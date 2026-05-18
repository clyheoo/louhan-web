<?php

namespace App\Exports\Sheets;

use App\Models\Ikan;
use App\Models\ScoringPointConfig;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class PointRankingSheet implements FromArray, WithTitle, WithEvents, WithStyles, WithColumnWidths
{
    private $scope;
    private $mergeRanges = [];

    private const CATS = [
        'overall'  => ['label' => 'OVERALL',      'fields' => [['id'=>'impression','label'=>'Impression','max'=>100]]],
        'head'     => ['label' => 'HEAD',          'fields' => [['id'=>'size','label'=>'Size','max'=>60],['id'=>'bentuk','label'=>'Bentuk Kepala','max'=>40]]],
        'face'     => ['label' => 'FACE',          'fields' => [['id'=>'pipi','label'=>'Pipi','max'=>25],['id'=>'mata','label'=>'Mata','max'=>25],['id'=>'bibir','label'=>'Bibir','max'=>25],['id'=>'kondisi','label'=>'Kondisi','max'=>25]]],
        'body'     => ['label' => 'BODY SHAPE',    'fields' => [['id'=>'bentuk','label'=>'Bentuk Badan','max'=>50],['id'=>'proporsi','label'=>'Proporsional','max'=>40],['id'=>'pangkal','label'=>'Pangkal','max'=>10]]],
        'marking'  => ['label' => 'MARKING',       'fields' => [['id'=>'fullness','label'=>'Fullness','max'=>40],['id'=>'contrast','label'=>'Contrast','max'=>40],['id'=>'bentuk','label'=>'Bentuk','max'=>20]]],
        'pearl'    => ['label' => 'PEARL',         'fields' => [['id'=>'shining','label'=>'Shining','max'=>45],['id'=>'fullness','label'=>'Fullness','max'=>35],['id'=>'bentuk','label'=>'Bentuk','max'=>20]]],
        'color'    => ['label' => 'COLOUR',        'fields' => [['id'=>'komposisi','label'=>'Komposisi','max'=>45],['id'=>'kecerahan','label'=>'Kecerahan','max'=>35],['id'=>'fullness','label'=>'Fullness','max'=>20]]],
        'finnage'  => ['label' => 'FINNAGE',       'fields' => [['id'=>'bentuk','label'=>'Bentuk Sirip & Ekor','max'=>75],['id'=>'kecerahan','label'=>'Kecerahan','max'=>25]]],
    ];

    private const BOBOT_KEYS = [
        'overall'=>'overall_bobot','head'=>'head_bobot','face'=>'face_bobot',
        'body'=>'body_bobot','marking'=>'marking_bobot','pearl'=>'pearl_bobot',
        'color'=>'color_bobot','finnage'=>'finnage_bobot',
    ];

    public function __construct($scope = 'per_kategori_kelas')
    {
        $this->scope = $scope;
    }

    public function title(): string
    {
        return match($this->scope) {
            'per_kategori_kelas' => 'RANKING PER KAT+KELAS',
            'per_kategori'       => 'RANKING PER KATEGORI',
            'global'             => 'RANK GLOBAL',
            default              => 'POINT RANKING',
        };
    }

    public function columnWidths(): array
    {
        $w = [
            'A' => 6, 'B' => 22, 'C' => 14, 'D' => 8, 'E' => 9, 'F' => 18, 'G' => 8,
        ];
        $col = 8;
        foreach (self::CATS as $info) {
            foreach ($info['fields'] as $f) {
                $letter = Coordinate::stringFromColumnIndex($col++);
                $w[$letter] = 11;
                $letter = Coordinate::stringFromColumnIndex($col++);
                $w[$letter] = 9;
            }
            $letter = Coordinate::stringFromColumnIndex($col++);
            $w[$letter] = 11;
        }
        $endLetters = ['TOTAL POINT','BONUS','FINAL POINT','RANK POINT'];
        foreach ($endLetters as $i => $label) {
            $letter = Coordinate::stringFromColumnIndex($col++);
            $w[$letter] = $label === 'TOTAL POINT' ? 13 : 10;
        }
        return $w;
    }

    /* ── Hitung data per ikan ── */
    private function calcIkan($ikan, $configs)
    {
        $scorings = $ikan->scorings->filter(fn($s) => $s->submitted_to_grand);
        if ($scorings->isEmpty()) return null;

        $cfg = $configs->get($ikan->kategori);
        if (!$cfg) return null;

        $bobots = [];
        foreach (self::BOBOT_KEYS as $kat => $key) {
            $bobots[$kat] = (float)($cfg->$key ?? 0);
        }

        // Rata-rata per komponen
        $avg = [];
        foreach ($scorings as $s) {
            $nd = $s->nilai_detail;
            if (!$nd || !is_array($nd)) continue;
            foreach ($nd as $kat => $fields) {
                foreach ($fields as $fid => $val) {
                    if (!isset($avg[$kat][$fid])) $avg[$kat][$fid] = ['sum'=>0,'count'=>0];
                    $avg[$kat][$fid]['sum'] += (float)($val ?? 0);
                    $avg[$kat][$fid]['count']++;
                }
            }
        }
        foreach ($avg as $kat => &$fields) {
            foreach ($fields as $fid => &$d) {
                $d['avg'] = $d['count'] > 0 ? $d['sum'] / $d['count'] : 0;
            }
        }
        unset($fields, $d);

        // Point per komponen
        $compPoints = [];
        $catSubs = [];
        $totalPoint = 0;
        foreach (self::CATS as $kat => $info) {
            $sub = 0;
            foreach ($info['fields'] as $f) {
                $a = $avg[$kat][$f['id']]['avg'] ?? 0;
                $p = $f['max'] > 0 ? round(($a / $f['max']) * $bobots[$kat], 2) : 0;
                $compPoints[$kat][$f['id']] = ['avg' => $a, 'point' => $p];
                $sub += $p;
            }
            $catSubs[$kat] = round($sub, 2);
            $totalPoint += $sub;
        }

        $totalPoint = round($totalPoint);
        $totalBonus = (int) $ikan->bonusPoints->sum('points');

        return [
            'nama_peserta' => $ikan->peserta->nama_peserta ?? '—',
            'kategori'      => $ikan->kategori,
            'kelas'         => $ikan->kelas ?? '—',
            'nomor_tank'    => $ikan->nomor_tank,
            'asal'          => $ikan->peserta->detail_anggota ?? '—',
            'jml_juri'      => $scorings->count(),
            'comp_points'   => $compPoints,
            'cat_subs'      => $catSubs,
            'total_point'   => $totalPoint,
            'total_bonus'   => $totalBonus,
            'final_point'   => $totalPoint + $totalBonus,
        ];
    }

    /* ── Build header rows ── */
    private function buildRows(): array
    {
        $configs = ScoringPointConfig::all()->keyBy('kategori');

        $ikans = Ikan::where('is_locked', true)
            ->whereNotNull('nomor_tank')
            ->whereHas('scorings', fn($q) => $q->where('submitted_to_grand', true))
            ->with(['peserta', 'scorings' => fn($q) => $q->where('submitted_to_grand', true), 'bonusPoints'])
            ->orderBy('kategori')->orderBy('kelas')->orderBy('nomor_tank')
            ->get();

        // Group
        $groups = [];
        foreach ($ikans as $ikan) {
            $d = $this->calcIkan($ikan, $configs);
            if (!$d) continue;
            $key = match($this->scope) {
                'per_kategori_kelas' => $ikan->kategori . ' - Kelas ' . ($ikan->kelas ?? '—'),
                'per_kategori'       => $ikan->kategori,
                'global'             => 'GLOBAL',
            };
            $groups[$key][] = $d;
        }
        if ($this->scope !== 'global') ksort($groups);

        // ─ Row 1: Category merged headers ─
        $fixedH = ['RANK','PESERTA','KATEGORI','KELAS','NO TANK','ASAL / TEAM','JML JURI'];
        $catRow = $fixedH;
        $this->mergeRanges = [];
        $col = 8;
        foreach (self::CATS as $info) {
            $n = count($info['fields']) * 2 + 1;
            $startC = $col;
            $catRow[] = strtoupper($info['label']);
            for ($i = 1; $i < $n; $i++) $catRow[] = '';
            $this->mergeRanges[] = [$startC, $col + $n - 1];
            $col += $n;
        }
        foreach (['TOTAL POINT','BONUS','FINAL POINT','RANK POINT'] as $h) $catRow[] = $h;

        // ─ Row 2: Component headers ─
        $compRow = $fixedH;
        foreach (self::CATS as $info) {
            foreach ($info['fields'] as $f) {
                $compRow[] = $f['label'] . "\n[Rata-rata]";
                $compRow[] = $f['label'] . "\n[Point]";
            }
            $compRow[] = 'SUBTOTAL';
        }

        $rows = [$catRow, $compRow];

        // ─ Data rows ─
        foreach ($groups as $groupName => $items) {
            usort($items, fn($a, $b) => $b['final_point'] <=> $a['final_point']);
            $ranked = [];
            foreach ($items as $i => $it) {
                $it['rank_point'] = max(1, 100 - $i);
                $ranked[] = $it;
            }

            // Group separator
            $sep = array_fill(0, count($catRow), '');
            $sep[0] = '▶ ' . strtoupper($groupName) . ' (' . count($ranked) . ' peserta)';
            $rows[] = $sep;

            foreach ($ranked as $i => $d) {
                $row = [$i + 1, $d['nama_peserta'], strtoupper($d['kategori']), $d['kelas'], $d['nomor_tank'], $d['asal'], $d['jml_juri']];
                foreach (self::CATS as $kat => $info) {
                    foreach ($info['fields'] as $f) {
                        $cp = $d['comp_points'][$kat][$f['id']] ?? ['avg'=>0,'point'=>0];
                        $row[] = round($cp['avg'], 2);
                        $row[] = $cp['point'];
                    }
                    $row[] = $d['cat_subs'][$kat] ?? 0;
                }
                $row[] = $d['total_point'];
                $row[] = $d['total_bonus'];
                $row[] = $d['final_point'];
                $row[] = $d['rank_point'];
                $rows[] = $row;
            }
            $rows[] = array_fill(0, count($catRow), '');
        }

        return $rows;
    }

    public function array(): array
    {
        return $this->buildRows();
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $lastCol = $sheet->getHighestColumn();
                $lastRow = $sheet->getHighestRow();

                // Merge category headers in row 1
                foreach ($this->mergeRanges as $range) {
                    $s = Coordinate::stringFromColumnIndex($range[0]);
                    $e = Coordinate::stringFromColumnIndex($range[1]);
                    $sheet->mergeCells("{$s}1:{$e}1");
                }

                // Style row 1 (category headers)
                $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '6D28D9']],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
                ]);

                // Style row 2 (component headers)
                $sheet->getStyle("A2:{$lastCol}2")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '7C3AED']],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
                ]);

                // Style group separator rows
                for ($r = 3; $r <= $lastRow; $r++) {
                    $val = $sheet->getCell("A{$r}")->getValue();
                    if ($val && str_starts_with($val, '▶')) {
                        $sheet->mergeCells("A{$r}:{$lastCol}{$r}");
                        $sheet->getStyle("A{$r}:{$lastCol}{$r}")->applyFromArray([
                            'font'      => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '4C1D95']],
                            'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F5F3FF']],
                            'alignment' => ['horizontal' => 'left', 'vertical' => 'center'],
                        ]);
                    }
                }

                // Alternating row colors for data
                $dataStart = 3;
                for ($r = $dataStart; $r <= $lastRow; $r++) {
                    $val = $sheet->getCell("A{$r}")->getValue();
                    if ($val === '' || ($val && str_starts_with($val, '▶'))) continue;
                    if (($r - $dataStart) % 2 === 0) {
                        $sheet->getStyle("A{$r}:{$lastCol}{$r}")->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FAF5FF']],
                        ]);
                    }
                }

                // Borders for all used cells
                $sheet->getStyle("A2:{$lastCol}{$lastRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']],
                    ],
                ]);

                // Number format for point columns (2 decimal)
                for ($c = 8; $c <= Coordinate::columnIndexFromString($lastCol) - 4; $c++) {
                    $colLetter = Coordinate::stringFromColumnIndex($c);
                    $sheet->getStyle("{$colLetter}3:{$colLetter}{$lastRow}")->getNumberFormat()
                        ->setFormatCode('0.00');
                }

                // Freeze top 2 rows
                $sheet->freezePane('H3');

                // Row height for header rows
                $sheet->getRowDimension(1)->setRowHeight(22);
                $sheet->getRowDimension(2)->setRowHeight(40);
            },
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [];
    }
}