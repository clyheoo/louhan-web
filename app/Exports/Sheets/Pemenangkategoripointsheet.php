<?php

namespace App\Exports\Sheets;

use App\Models\Ikan;
use App\Helpers\PointCalculator;
use App\Support\PesertaRankingBuilder;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

/**
 * Daftar PEMENANG per kategori — DENGAN point (kartu, seperti Gambar 1 / "temel").
 * Tiap pemenang = kartu transpos (label | nilai): NO TANK, NAMA PESERTA, TEAM,
 * OVERALL..FINNAGE (subtotal komponen), TOTAL POINT, KETERANGAN.
 * Tata letak: kartu-kartu satu kategori mengalir KE KANAN; kategori berikut TURUN.
 * Sumber pemenang & nomor JUARA: PesertaRankingBuilder (identik dgn sheet HASIL PESERTA).
 * Subtotal komponen: PointCalculator::hitungBreakdown (identik dgn sheet SUBTOTAL).
 */
class PemenangKategoriPointSheet implements FromArray, WithTitle, WithEvents
{
    private int $from;
    private int $to;

    private array $cards = []; // placement kartu untuk styling
    private int $totalCols = 1;

    private const CATS = [
        'overall' => 'OVERALL',
        'head'    => 'HEAD',
        'face'    => 'FACE',
        'body'    => 'BODY SHAPE',
        'marking' => 'MARKING',
        'pearl'   => 'PEARL',
        'color'   => 'COLOUR',
        'finnage' => 'FINNAGE',
    ];

    // Urutan baris kartu (sesuai Gambar 1)
    private const LABELS = [
        'NO TANK', 'NAMA PESERTA', 'TEAM',
        'OVERALL', 'HEAD', 'FACE', 'BODY SHAPE', 'MARKING', 'PEARL', 'COLOUR', 'FINNAGE',
        'TOTAL POINT', 'KETERANGAN',
    ];

    private const CARD_COLS = 2; // label | nilai
    private const GAP_COLS  = 1;
    private const GAP_ROWS  = 1;

    public function __construct(int $from = 1, int $to = 5)
    {
        $this->from = $from;
        $this->to   = $to;
    }

    public function title(): string
    {
        return 'PEMENANG POINT ' . $this->from . '-' . $this->to;
    }

    /** Peta ikan_id => ['cat_subs'=>[], 'total'=>float, 'deduction'=>float] untuk para pemenang. */
    private function buildBreakdownMap(array $ikanIds): array
    {
        $map = [];

        $ikans = Ikan::whereIn('id', $ikanIds)
            ->with(['scorings', 'bonusPoints'])
            ->get();

        foreach ($ikans as $ikan) {
            $scorings = $ikan->scorings;
            if ($scorings->isEmpty()) {
                continue;
            }

            $avgDetail = [];
            $jumlahJuri = 0;
            foreach ($scorings as $s) {
                if ($s->total_nilai) {
                    $jumlahJuri++;
                }
                if ($s->nilai_detail && is_array($s->nilai_detail)) {
                    foreach ($s->nilai_detail as $kat => $fields) {
                        if (!is_array($fields)) continue;
                        foreach ($fields as $fid => $val) {
                            if ($fid === 'defect') continue;
                            if ($kat === 'pearl' && $fid === 'shining') $fid = 'shinning';
                            if (!isset($avgDetail[$kat][$fid])) $avgDetail[$kat][$fid] = ['sum' => 0, 'count' => 0];
                            $avgDetail[$kat][$fid]['sum'] += (float) ($val ?? 0);
                            $avgDetail[$kat][$fid]['count']++;
                        }
                    }
                }
            }
            $finalAvgDetail = [];
            if ($jumlahJuri > 0) {
                foreach ($avgDetail as $kat => $fields) {
                    foreach ($fields as $fid => $d) {
                        $finalAvgDetail[$kat][$fid] = $d['count'] > 0 ? $d['sum'] / $d['count'] : 0;
                    }
                }
            }

            $defectKeys = ['raw_head_penalty', 'raw_face_penalty', 'raw_body_penalty', 'raw_finnage_penalty'];
            $combined = [];
            foreach ($defectKeys as $dk) $combined[$dk] = [];
            foreach ($scorings as $s) {
                foreach ($defectKeys as $dk) {
                    $defs = $s->$dk;
                    if (!$defs) continue;
                    if (is_string($defs)) $defs = [$defs];
                    if (!is_array($defs)) continue;
                    foreach ($defs as $d) {
                        if ($d && $d !== '0' && !in_array($d, $combined[$dk])) $combined[$dk][] = $d;
                    }
                }
            }
            $defectDataForCalc = [];
            foreach ($combined as $dk => $defs) $defectDataForCalc[$dk] = count($defs) ? $defs : ['0'];

            $breakdown = PointCalculator::hitungBreakdown($ikan->kategori, $finalAvgDetail, $defectDataForCalc);
            if (!$breakdown) {
                continue;
            }
            $defectDetails = PointCalculator::getDefectDetails($defectDataForCalc);

            $catSubs = [];
            foreach (self::CATS as $catKey => $catLabel) {
                $catSubs[$catKey] = $breakdown[$catKey]['point'] ?? 0;
            }

            $map[$ikan->id] = [
                'cat_subs'  => $catSubs,
                'total'     => $breakdown['total'] ?? 0,
                'deduction' => $defectDetails['total_deduction_percent'] ?? 0,
            ];
        }

        return $map;
    }

    public function array(): array
    {
        $ranking = PesertaRankingBuilder::build();

        // Ambil pemenang dalam rentang band, kelompokkan per kategori + kelas.
        $groups = [];
        $winnerIds = [];
        foreach ($ranking as $p) {
            $pos = (int) ($p['juara'] ?? 0);
            if ($pos < $this->from || $pos > $this->to) {
                continue;
            }
            $groups[$p['kategori'] . ' - Kelas ' . $p['kelas']][] = $p;
            $winnerIds[] = $p['ikan_id'];
        }
        ksort($groups);

        if (empty($winnerIds)) {
            return [['Belum ada pemenang pada rentang juara ' . $this->from . '-' . $this->to . '.']];
        }

        $subs = $this->buildBreakdownMap($winnerIds);

        // ===== LAYOUT: 1 kategori = 1 band; kartu mengalir ke kanan; kategori berikut turun =====
        $stride = self::CARD_COLS + self::GAP_COLS; // 3
        $rowH   = 1 + count(self::LABELS);          // 1 judul + 13 baris = 14
        $grid   = [];
        $this->cards = [];
        $maxCol = 1;
        $rowCursor = 1;

        foreach ($groups as $name => $items) {
            usort($items, fn ($a, $b) => ((int) $a['juara'] <=> (int) $b['juara']));

            $colIndex = 0;
            foreach ($items as $w) {
                $startCol = 1 + $colIndex * $stride; // kolom label
                $valCol   = $startCol + 1;
                $titleRow = $rowCursor;

                $cs = $subs[$w['ikan_id']] ?? ['cat_subs' => [], 'total' => $w['total_point'] ?? 0, 'deduction' => 0];

                $grid[$titleRow][$startCol] = $name;

                $vals = [
                    (string) ($w['nomor_tank'] ?? '—'),
                    $w['nama'] ?? '—',
                    $w['team'] ?? '—',
                    $cs['cat_subs']['overall'] ?? 0,
                    $cs['cat_subs']['head'] ?? 0,
                    $cs['cat_subs']['face'] ?? 0,
                    $cs['cat_subs']['body'] ?? 0,
                    $cs['cat_subs']['marking'] ?? 0,
                    $cs['cat_subs']['pearl'] ?? 0,
                    $cs['cat_subs']['color'] ?? 0,
                    $cs['cat_subs']['finnage'] ?? 0,
                    $cs['total'] ?? 0,
                    (($cs['deduction'] ?? 0) > 0 ? $cs['deduction'] . '%' : '-'),
                ];

                $rr = $titleRow + 1;
                foreach (self::LABELS as $i => $label) {
                    $grid[$rr][$startCol] = $label;
                    $grid[$rr][$valCol]   = $vals[$i];
                    $rr++;
                }

                $this->cards[] = [
                    'titleRow'  => $titleRow,
                    'labelCol'  => $startCol,
                    'valCol'    => $valCol,
                    'dataStart' => $titleRow + 1,
                    'dataEnd'   => $titleRow + count(self::LABELS),
                ];

                $maxCol = max($maxCol, $valCol);
                $colIndex++;
            }

            $rowCursor += $rowH + self::GAP_ROWS;
        }

        $this->totalCols = $maxCol;
        $maxRow = $rowCursor;

        $dense = [];
        for ($r = 1; $r < $maxRow; $r++) {
            $line = [];
            for ($c = 1; $c <= $this->totalCols; $c++) {
                $line[] = $grid[$r][$c] ?? '';
            }
            $dense[] = $line;
        }

        return $dense;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                if (empty($this->cards)) {
                    return;
                }

                $stride = self::CARD_COLS + self::GAP_COLS;

                // Lebar kolom (pola berulang: label, nilai, gap)
                for ($c = 1; $c <= $this->totalCols; $c++) {
                    $offset = ($c - 1) % $stride;
                    $letter = Coordinate::stringFromColumnIndex($c);
                    if ($offset === 0) {
                        $sheet->getColumnDimension($letter)->setAutoSize(false)->setWidth(15);
                    } elseif ($offset === 1) {
                        $sheet->getColumnDimension($letter)->setAutoSize(false)->setWidth(16);
                    } else {
                        $sheet->getColumnDimension($letter)->setAutoSize(false)->setWidth(2.5);
                    }
                }

                foreach ($this->cards as $cd) {
                    $L0 = Coordinate::stringFromColumnIndex($cd['labelCol']);
                    $LV = Coordinate::stringFromColumnIndex($cd['valCol']);
                    $tr = $cd['titleRow'];
                    $ds = $cd['dataStart'];
                    $de = $cd['dataEnd'];

                    // Judul kartu (merge label|nilai)
                    $sheet->mergeCells("{$L0}{$tr}:{$LV}{$tr}");
                    $sheet->getStyle("{$L0}{$tr}:{$LV}{$tr}")->applyFromArray([
                        'font'      => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '1E3A8A']],
                        'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                    ]);
                    $sheet->getRowDimension($tr)->setRowHeight(18);

                    // Border seluruh kartu
                    $sheet->getStyle("{$L0}{$ds}:{$LV}{$de}")->applyFromArray([
                        'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'BFDBFE']]],
                        'alignment' => ['vertical' => 'center'],
                    ]);

                    // Kolom label: bold + fill muda + rata kiri
                    $sheet->getStyle("{$L0}{$ds}:{$L0}{$de}")->applyFromArray([
                        'font'      => ['bold' => true, 'size' => 9, 'color' => ['rgb' => '1E3A8A']],
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'EFF6FF']],
                        'alignment' => ['horizontal' => 'left', 'vertical' => 'center'],
                    ]);

                    // Kolom nilai: default rata kanan
                    $sheet->getStyle("{$LV}{$ds}:{$LV}{$de}")->applyFromArray([
                        'alignment' => ['horizontal' => 'right', 'vertical' => 'center'],
                    ]);

                    // NAMA PESERTA & TEAM (2 baris pertama nilai) rata kiri
                    $nameTeamEnd = $ds + 2;
                    $sheet->getStyle("{$LV}{$ds}:{$LV}{$nameTeamEnd}")->getAlignment()->setHorizontal('left');

                    // Baris komponen s/d TOTAL POINT (index 3..11) format 0.00
                    $numStart = $ds + 3;
                    $numEnd   = $ds + 11;
                    $sheet->getStyle("{$LV}{$numStart}:{$LV}{$numEnd}")->getNumberFormat()->setFormatCode('0.00');

                    // Baris TOTAL POINT (index 11) → sorot emas
                    $tpRow = $ds + 11;
                    $sheet->getStyle("{$L0}{$tpRow}:{$LV}{$tpRow}")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['rgb' => '92400E']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FEF3C7']],
                    ]);
                }
            },
        ];
    }
}