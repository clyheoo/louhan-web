<?php

namespace App\Exports\Sheets;

use App\Models\Ikan;
use App\Models\ScoringPointConfig;
use App\Helpers\PointCalculator;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class PointRankingSheet implements FromArray, WithTitle, WithEvents, ShouldAutoSize
{
    private $scope;
    private $mergeRanges = [];
    private $catStartCols = [];

    private const CATS = [
        'overall'  => ['label' => 'OVERALL',   'fields' => [['id'=>'impression','label'=>'Impression']]],
        'head'     => ['label' => 'HEAD',       'fields' => [['id'=>'size','label'=>'Size'],['id'=>'bentuk','label'=>'Bentuk Kepala']]],
        'face'     => ['label' => 'FACE',       'fields' => [['id'=>'face','label'=>'Face']]],
        'body'     => ['label' => 'BODY SHAPE', 'fields' => [['id'=>'bentuk','label'=>'Bentuk Badan'],['id'=>'proporsi','label'=>'Proporsional'],['id'=>'pangkal','label'=>'Pangkal']]],
        'marking'  => ['label' => 'MARKING',    'fields' => [['id'=>'fullness','label'=>'Fullness'],['id'=>'contrast','label'=>'Contrast'],['id'=>'bentuk','label'=>'Bentuk']]],
        'pearl'    => ['label' => 'PEARL',      'fields' => [['id'=>'shinning','label'=>'Shinning'],['id'=>'fullness','label'=>'Fullness'],['id'=>'bentuk','label'=>'Bentuk']]],
        'color'    => ['label' => 'COLOUR',     'fields' => [['id'=>'komposisi','label'=>'Komposisi'],['id'=>'kecerahan','label'=>'Kecerahan'],['id'=>'fullness','label'=>'Fullness']]],
        'finnage'  => ['label' => 'FINNAGE',    'fields' => [['id'=>'bentuk','label'=>'Bentuk Sirip & Ekor'],['id'=>'kecerahan','label'=>'Kecerahan']]],
    ];

    private const FIELD_PCT_MAP = [
        'overall' => ['impression' => 'overall_point'],
        'head'    => ['size' => 'head_size_pct', 'bentuk' => 'head_bentuk_k_pct'],
        'face'    => ['face' => 'face_face_pct'],
        'body'    => ['bentuk' => 'body_bentuk_pct', 'proporsi' => 'body_proposional_pct', 'pangkal' => 'body_pangkal_pct'],
        'marking' => ['fullness' => 'marking_fullness_pct', 'contrast' => 'marking_contrast_pct', 'bentuk' => 'marking_bentuk_pct'],
        'pearl'   => ['shinning' => 'pearl_shinning_pct', 'fullness' => 'pearl_fullnes_pct', 'bentuk' => 'pearl_bentuk_pearl_pct'],
        'color'   => ['komposisi' => 'color_komposisi_pct', 'kecerahan' => 'color_kecerahan_pct', 'fullness' => 'color_fullness_colour_pct'],
        'finnage' => ['bentuk' => 'finnage_bentuk_sirip_ekor_pct', 'kecerahan' => 'finnage_kecerahan_pct'],
    ];

    private const BOBOT_KEYS = [
        'overall'=>'overall_bobot','head'=>'head_bobot','face'=>'face_bobot',
        'body'=>'body_bobot','marking'=>'marking_bobot','pearl'=>'pearl_bobot',
        'color'=>'color_bobot','finnage'=>'finnage_bobot',
    ];

    public function __construct($scope = 'per_kategori_kelas') { $this->scope = $scope; }

    public function title(): string
    {
        return match($this->scope) {
            'per_kategori_kelas' => 'RANKING PER KAT+KELAS',
            'per_kategori'       => 'RANKING PER KATEGORI',
            'global'             => 'RANK GLOBAL',
            default              => 'POINT RANKING',
        };
    }

    private function calcIkan($ikan, $configs)
    {
        $scorings = $ikan->scorings->filter(fn($s) => $s->submitted_to_grand);
        if ($scorings->isEmpty()) return null;
        $cfg = $configs->get($ikan->kategori);
        if (!$cfg) return null;

        // Rata-rata nilai_detail semua juri (masih dibutuhkan untuk kalkulasi internal)
        $avg = [];
        foreach ($scorings as $s) {
            $nd = $s->nilai_detail;
            if (!$nd || !is_array($nd)) continue;
            foreach ($nd as $kat => $fields) {
                if (!is_array($fields)) continue;
                foreach ($fields as $fid => $val) {
                    if ($fid === 'defect') continue;
                    if ($kat === 'pearl' && $fid === 'shining') $fid = 'shinning';
                    if (!isset($avg[$kat][$fid])) $avg[$kat][$fid] = ['sum' => 0, 'count' => 0];
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

        // Gabungkan defect dari semua juri (union)
        $defectKeys = ['raw_head_penalty', 'raw_face_penalty', 'raw_body_penalty', 'raw_finnage_penalty'];
        $combinedDefects = [];
        foreach ($defectKeys as $dk) { $combinedDefects[$dk] = []; }

        foreach ($scorings as $s) {
            foreach ($defectKeys as $dk) {
                $defs = $s->$dk;
                if (!$defs) continue;
                if (is_string($defs)) $defs = [$defs];
                if (!is_array($defs)) continue;
                foreach ($defs as $d) {
                    if ($d && $d !== '0' && !in_array($d, $combinedDefects[$dk])) {
                        $combinedDefects[$dk][] = $d;
                    }
                }
            }
        }
        foreach ($combinedDefects as $dk => &$defs) {
            $combinedDefects[$dk] = count($defs) > 0 ? $defs : ['0'];
        }
        unset($defs);

        $evaluated = PointCalculator::evaluateDefects($combinedDefects);

        // Hitung point per komponen — hanya point, tidak perlu avg untuk output
        $compPoints = [];
        $catSubs = [];

        foreach (self::CATS as $kat => $info) {
            $bobot = (float)($cfg->{self::BOBOT_KEYS[$kat]} ?? 0);
            $sub = 0;

            foreach ($info['fields'] as $f) {
                $avgVal = $avg[$kat][$f['id']]['avg'] ?? 0;
                $pctKey = self::FIELD_PCT_MAP[$kat][$f['id']] ?? null;
                $pct = $pctKey ? (float)($cfg->$pctKey ?? 0) : 0;

                $point = ($avgVal * $bobot * $pct) / 100;

                // Hanya simpan point, tidak ada avg
                $compPoints[$kat][$f['id']] = round($point, 2);
                $sub += $point;
            }

            // Terapkan defect penalty ke subtotal kategori
            $penaltyKey = $kat . '_penalty';
            if (!empty($evaluated[$penaltyKey])) {
                $penaltyPercent = (float)str_replace('%', '', $evaluated[$penaltyKey]);
                $sub = $sub * (1 - ($penaltyPercent / 100));
            }

            $catSubs[$kat] = round($sub, 2);
        }

        $totalPoint = round(array_sum($catSubs));
        $totalBonus = (int) $ikan->bonusPoints->sum('points');

        return [
            'nama_peserta' => $ikan->peserta->nama_peserta ?? '—',
            'kategori' => $ikan->kategori, 'kelas' => $ikan->kelas ?? '—',
            'nomor_tank' => $ikan->nomor_tank, 'asal' => $ikan->peserta->detail_anggota ?? '—',
            'jml_juri' => $scorings->count(), 'comp_points' => $compPoints,
            'cat_subs' => $catSubs, 'total_point' => $totalPoint,
            'total_bonus' => $totalBonus, 'final_point' => $totalPoint + $totalBonus,
        ];
    }

    public function array(): array
    {
        $configs = ScoringPointConfig::all()->keyBy('kategori');
        $ikans = Ikan::where('is_locked', true)->whereNotNull('nomor_tank')
            ->whereHas('scorings', fn($q) => $q->where('submitted_to_grand', true))
            ->with(['peserta', 'scorings' => fn($q) => $q->where('submitted_to_grand', true), 'bonusPoints'])
            ->orderBy('kategori')->orderBy('kelas')->orderBy('nomor_tank')->get();

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

        // ★ Hitung posisi kolom — setiap field sekarang hanya 1 kolom (Point saja)
        $this->catStartCols = [];
        $col = 8;
        foreach (self::CATS as $info) {
            $this->catStartCols[] = $col;
            $col += count($info['fields']) + 1; // fields + 1 subtotal
        }

        $fixedH = ['RANK', 'PESERTA', 'KATEGORI', 'KELAS', 'NO TANK', 'ASAL / TEAM', 'JML JURI'];
        $catRow = $fixedH;
        $this->mergeRanges = [];
        $col = 8;
        foreach (self::CATS as $info) {
            $n = count($info['fields']) + 1; // field point cols + 1 subtotal
            $startC = $col;
            $catRow[] = strtoupper($info['label']);
            for ($i = 1; $i < $n; $i++) $catRow[] = '';
            $this->mergeRanges[] = [$startC, $col + $n - 1];
            $col += $n;
        }
        foreach (['TOTAL POINT', 'BONUS', 'FINAL POINT', 'RANK POINT'] as $h) $catRow[] = $h;
        $colCount = count($catRow);

        // ★ Row 2: Hanya label komponen, tanpa [Rata-rata]
        $compRow = $fixedH;
        foreach (self::CATS as $info) {
            foreach ($info['fields'] as $f) {
                $compRow[] = $f['label'];
            }
            $compRow[] = 'SUBTOTAL';
        }

        $rows = [$catRow, $compRow];
        foreach ($groups as $groupName => $items) {
            usort($items, fn($a, $b) => $b['final_point'] <=> $a['final_point']);
            $ranked = [];
            foreach ($items as $i => $it) { $it['rank_point'] = max(1, 100 - $i); $ranked[] = $it; }

            $sep = array_fill(0, $colCount, '');
            $sep[0] = '▶ ' . strtoupper($groupName) . ' (' . count($ranked) . ' peserta)';
            $rows[] = $sep;

            foreach ($ranked as $i => $d) {
                $row = [$i + 1, $d['nama_peserta'], strtoupper($d['kategori']), $d['kelas'], $d['nomor_tank'], $d['asal'], $d['jml_juri']];
                foreach (self::CATS as $kat => $info) {
                    // ★ Hanya 1 kolom point per komponen
                    foreach ($info['fields'] as $f) {
                        $row[] = $d['comp_points'][$kat][$f['id']] ?? 0;
                    }
                    $row[] = $d['cat_subs'][$kat] ?? 0;
                }
                $row[] = $d['total_point'];
                $row[] = $d['total_bonus'];
                $row[] = $d['final_point'];
                $row[] = $d['rank_point'];
                $rows[] = $row;
            }
            $rows[] = array_fill(0, $colCount, '');
        }
        return $rows;
    }

    public function registerEvents(): array
    {
        return [AfterSheet::class => function (AfterSheet $event) {
            $sheet = $event->sheet;
            $lastCol = $sheet->getHighestColumn();
            $lastColIdx = Coordinate::columnIndexFromString($lastCol);
            $lastRow = $sheet->getHighestRow();

            // Column widths — sedikit lebih lebar karena hanya 1 kolom per komponen
            for ($c = 1; $c <= $lastColIdx; $c++) {
                $letter = Coordinate::stringFromColumnIndex($c);
                $w = $c <= 2 ? 14 : ($c <= 7 ? 12 : 13);
                $sheet->getColumnDimension($letter)->setAutoSize(false)->setWidth($w);
            }

            foreach ($this->mergeRanges as $range) {
                $s = Coordinate::stringFromColumnIndex($range[0]);
                $e = Coordinate::stringFromColumnIndex($range[1]);
                $sheet->mergeCells("{$s}1:{$e}1");
            }

            $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
                'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '6D28D9']],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
            ]);

            $sheet->getStyle("A2:{$lastCol}2")->applyFromArray([
                'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '7C3AED']],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
            ]);

            // Separator kolom tebal antar kategori
            $separatorCols = array_slice($this->catStartCols, 1);
            $totalPointCol = $lastColIdx - 3;
            $separatorCols[] = $totalPointCol;

            $thickBorder = [
                'left' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '4C1D95']],
            ];

            foreach ($separatorCols as $colIdx) {
                $colLetter = Coordinate::stringFromColumnIndex($colIdx);
                $sheet->getStyle("{$colLetter}1")->applyFromArray(['borders' => $thickBorder]);
                $sheet->getStyle("{$colLetter}2")->applyFromArray(['borders' => $thickBorder]);
            }

            // Group separator rows
            for ($r = 3; $r <= $lastRow; $r++) {
                $val = $sheet->getCell("A{$r}")->getValue();
                if ($val && str_starts_with($val, '▶')) {
                    $sheet->mergeCells("A{$r}:{$lastCol}{$r}");
                    $sheet->getStyle("A{$r}:{$lastCol}{$r}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '4C1D95']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F5F3FF']],
                    ]);
                }
            }

            // Data rows
            $toggle = false;
            for ($r = 3; $r <= $lastRow; $r++) {
                $val = $sheet->getCell("A{$r}")->getValue();
                if ($val === '' || ($val && str_starts_with($val, '▶'))) { $toggle = false; continue; }

                $sheet->getStyle("A{$r}:{$lastCol}{$r}")->applyFromArray([
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                ]);

                if ($toggle) {
                    $sheet->getStyle("A{$r}:{$lastCol}{$r}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FAF5FF']],
                    ]);
                }
                $toggle = !$toggle;

                foreach ($separatorCols as $colIdx) {
                    $colLetter = Coordinate::stringFromColumnIndex($colIdx);
                    $sheet->getStyle("{$colLetter}{$r}")->applyFromArray(['borders' => $thickBorder]);
                }
            }

            // Thin borders semua data
            $sheet->getStyle("A2:{$lastCol}{$lastRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDD6FE']],
                ],
            ]);

            // Override thick border di data rows
            for ($r = 3; $r <= $lastRow; $r++) {
                $val = $sheet->getCell("A{$r}")->getValue();
                if ($val === '' || ($val && str_starts_with($val, '▶'))) continue;
                foreach ($separatorCols as $colIdx) {
                    $colLetter = Coordinate::stringFromColumnIndex($colIdx);
                    $sheet->getStyle("{$colLetter}{$r}")->applyFromArray(['borders' => $thickBorder]);
                }
            }

            // Number format untuk kolom point (mulai kolom 8 sampai sebelum 4 kolom terakhir)
            for ($c = 8; $c <= $lastColIdx - 4; $c++) {
                $colLetter = Coordinate::stringFromColumnIndex($c);
                $sheet->getStyle("{$colLetter}3:{$colLetter}{$lastRow}")->getNumberFormat()->setFormatCode('0.00');
            }

            // Subtotal columns: bold — ★ dihitung ulang sesuai layout baru
            $subtotalCols = [];
            $col = 8;
            foreach (self::CATS as $info) {
                $col += count($info['fields']); // field point cols (tanpa ×2)
                $subtotalCols[] = $col;
                $col++;
            }
            foreach ($subtotalCols as $colIdx) {
                $colLetter = Coordinate::stringFromColumnIndex($colIdx);
                $sheet->getStyle("{$colLetter}3:{$colLetter}{$lastRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F5F3FF']],
                ]);
            }

            $sheet->freezePane('H3');
            $sheet->getRowDimension(1)->setRowHeight(20);
            $sheet->getRowDimension(2)->setRowHeight(30);
        }];
    }
}