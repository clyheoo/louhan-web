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
    
    private const DEFECT_CATS = ['head', 'face', 'body', 'finnage'];

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
        
        // 1. Hitung Rata-rata Nilai Detail
        $avgDetail = [];
        $jumlahJuriYangNilai = 0;

        foreach ($scorings as $s) {
            if ($s->total_nilai) $jumlahJuriYangNilai++;
            if ($s->nilai_detail && is_array($s->nilai_detail)) {
                foreach ($s->nilai_detail as $kat => $fields) {
                    if (!is_array($fields)) continue;
                    foreach ($fields as $fid => $val) {
                        if ($fid === 'defect') continue;
                        if ($kat === 'pearl' && $fid === 'shining') $fid = 'shinning';
                        
                        if (!isset($avgDetail[$kat][$fid])) $avgDetail[$kat][$fid] = ['sum' => 0, 'count' => 0];
                        $avgDetail[$kat][$fid]['sum'] += (float)($val ?? 0);
                        $avgDetail[$kat][$fid]['count']++;
                    }
                }
            }
        }

        // Inisialisasi struktur data final untuk menghindari error "Undefined array key"
        $finalAvgDetail = [];
        foreach (self::CATS as $kat => $info) {
            $finalAvgDetail[$kat] = [];
            foreach ($info['fields'] as $f) {
                $finalAvgDetail[$kat][$f['id']] = 0;
            }
        }

        if ($jumlahJuriYangNilai > 0) {
            foreach ($avgDetail as $kat => $fields) {
                foreach ($fields as $fid => $d) {
                    if (isset($finalAvgDetail[$kat][$fid])) {
                        $finalAvgDetail[$kat][$fid] = $d['count'] > 0 ? $d['sum'] / $d['count'] : 0;
                    }
                }
            }
        }

        // 2. Gabungkan Defect
        $defectKeysMapping = ['head' => 'raw_head_penalty', 'face' => 'raw_face_penalty', 'body' => 'raw_body_penalty', 'finnage' => 'raw_finnage_penalty'];
        $combinedDefects = [];
        $defectDataForCalc = [];

        foreach ($defectKeysMapping as $k => $dk) { 
            $combinedDefects[$k] = []; 
            $defectDataForCalc[$dk] = []; 
        }

        foreach ($scorings as $s) {
            foreach ($defectKeysMapping as $cat => $rawKey) {
                $defs = $s->$rawKey; // Sudah array karena cast di Model
                if (!$defs) continue;
                
                // Pastikan array (meskipun cast sudah ada, safety check)
                if (!is_array($defs)) $defs = [$defs];

                foreach ($defs as $d) {
                    if ($d && $d !== '0') {
                        if (!in_array($d, $combinedDefects[$cat])) {
                            $combinedDefects[$cat][] = $d;
                        }
                        $defectDataForCalc[$rawKey][] = $d;
                    }
                }
            }
        }
        
        // Pastikan key ada untuk kalkulasi
        foreach ($defectKeysMapping as $rawKey) {
            if (empty($defectDataForCalc[$rawKey])) $defectDataForCalc[$rawKey] = ['0'];
        }

        // 3. Hitung Point via Helper
        $breakdown = PointCalculator::hitungBreakdown($ikan->kategori, $finalAvgDetail);
        $evaluatedDefects = PointCalculator::evaluateDefects($defectDataForCalc);

        $compPoints = [];
        $catSubs = [];
        $totalDeduction = 0;
        $defectDisplay = [];

        $cfg = $configs->get($ikan->kategori);

        if ($breakdown) {
            foreach (self::CATS as $kat => $info) {
                // A. Hitung Komponen Field
                $bobot = (float)($cfg->{$kat . '_bobot'} ?? 0);
                
                $pctMap = [
                    'head'     => ['size' => 'head_size_pct', 'bentuk' => 'head_bentuk_k_pct'],
                    'face'     => ['face' => 'face_face_pct'],
                    'body'     => ['bentuk' => 'body_bentuk_pct', 'proporsi' => 'body_proposional_pct', 'pangkal' => 'body_pangkal_pct'],
                    'marking'  => ['fullness' => 'marking_fullness_pct', 'contrast' => 'marking_contrast_pct', 'bentuk' => 'marking_bentuk_pct'],
                    'pearl'    => ['shinning' => 'pearl_shinning_pct', 'fullness' => 'pearl_fullnes_pct', 'bentuk' => 'pearl_bentuk_pearl_pct'],
                    'color'    => ['komposisi' => 'color_komposisi_pct', 'kecerahan' => 'color_kecerahan_pct', 'fullness' => 'color_fullness_colour_pct'],
                    'finnage'  => ['bentuk' => 'finnage_bentuk_sirip_ekor_pct', 'kecerahan' => 'finnage_kecerahan_pct'],
                    'overall'  => ['impression' => 'overall_point']
                ];

                foreach ($info['fields'] as $f) {
                    $val = $finalAvgDetail[$kat][$f['id']] ?? 0;
                    $pctColName = $pctMap[$kat][$f['id']] ?? null;
                    
                    if ($kat === 'overall') {
                        $overallPointVal = (float)($cfg->overall_point ?? 0);
                        $compPoints[$kat][$f['id']] = round(($val * $bobot * $overallPointVal) / 100, 2);
                    } else {
                        $pct = $pctColName ? (float)($cfg->$pctColName ?? 0) : 0;
                        $compPoints[$kat][$f['id']] = round(($val * $bobot * $pct) / 100, 2);
                    }
                }

                // B. Hitung Subtotal & Defect
                $rawPoint = $breakdown[$kat]['point'] ?? 0;
                $penaltyKey = $kat . '_penalty';
                
                if (in_array($kat, self::DEFECT_CATS) && !empty($evaluatedDefects[$penaltyKey])) {
                    $penaltyStr = $evaluatedDefects[$penaltyKey]; // "10%" atau "30%"
                    $penaltyPercent = (float)str_replace('%', '', $penaltyStr);
                    
                    $deductionAmount = $rawPoint * ($penaltyPercent / 100);
                    $totalDeduction += $deductionAmount;
                    $catSubs[$kat] = round($rawPoint - $deductionAmount, 2);

                    // Format: "Kutil, Bengkok (10%)"
                    $items = implode(", ", $combinedDefects[$cat]);
                    $defectDisplay[$kat] = $items . " (" . $penaltyStr . ")";
                } else {
                    $catSubs[$kat] = $rawPoint;
                    $defectDisplay[$kat] = implode(", ", $combinedDefects[$cat]);
                }
            }
        }

        $totalPoint = round(array_sum($catSubs));
        $totalBonus = (int) $ikan->bonusPoints->sum('points');

        return [
            'nama_peserta' => $ikan->peserta->nama_peserta ?? '—',
            'kategori' => $ikan->kategori, 'kelas' => $ikan->kelas ?? '—',
            'nomor_tank' => $ikan->nomor_tank, 'asal' => $ikan->peserta->detail_anggota ?? '—',
            'jml_juri' => $scorings->count(), 
            'comp_points' => $compPoints,
            'cat_subs' => $catSubs, 
            'total_point' => $totalPoint,
            'total_deduction' => round($totalDeduction, 2),
            'total_bonus' => $totalBonus, 
            'final_point' => $totalPoint + $totalBonus,
            'defects' => $defectDisplay,
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

        $this->catStartCols = [];
        $col = 8;
        foreach (self::CATS as $kat => $info) {
            $this->catStartCols[] = $col;
            $hasDefect = in_array($kat, self::DEFECT_CATS);
            $fieldCount = count($info['fields']);
            $defectCol = $hasDefect ? 1 : 0;
            $col += $fieldCount + $defectCol + 1; 
        }

        $fixedH = ['RANK', 'PESERTA', 'KATEGORI', 'KELAS', 'NO TANK', 'ASAL / TEAM', 'JML JURI'];
        $catRow = $fixedH;
        $this->mergeRanges = [];
        $col = 8;
        
        foreach (self::CATS as $kat => $info) {
            $hasDefect = in_array($kat, self::DEFECT_CATS);
            $fieldCount = count($info['fields']);
            $defectCol = $hasDefect ? 1 : 0;
            $n = $fieldCount + $defectCol + 1; 
            
            $startC = $col;
            $catRow[] = strtoupper($info['label']);
            for ($i = 1; $i < $n; $i++) $catRow[] = '';
            $this->mergeRanges[] = [$startC, $col + $n - 1];
            $col += $n;
        }
        
        foreach (['TOTAL POINT', 'DEFECT DEDUCTION', 'BONUS', 'FINAL POINT', 'RANK POINT'] as $h) $catRow[] = $h;
        $colCount = count($catRow);

        $compRow = $fixedH;
        foreach (self::CATS as $kat => $info) {
            foreach ($info['fields'] as $f) {
                $compRow[] = $f['label'];
            }
            if (in_array($kat, self::DEFECT_CATS)) {
                $compRow[] = 'DEFECT';
            }
            $compRow[] = 'SUBTOTAL';
        }
        for ($i=0; $i < 5; $i++) $compRow[] = ''; 

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
                    foreach ($info['fields'] as $f) {
                        $row[] = $d['comp_points'][$kat][$f['id']] ?? 0;
                    }
                    if (in_array($kat, self::DEFECT_CATS)) {
                        $row[] = $d['defects'][$kat] ?? '';
                    }
                    $row[] = $d['cat_subs'][$kat] ?? 0;
                }
                
                $row[] = $d['total_point'];
                $row[] = -$d['total_deduction'];
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

            $separatorCols = array_slice($this->catStartCols, 1);
            $totalPointCol = $lastColIdx - 4;
            $separatorCols[] = $totalPointCol;

            $thickBorder = [
                'left' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '4C1D95']],
            ];

            foreach ($separatorCols as $colIdx) {
                $colLetter = Coordinate::stringFromColumnIndex($colIdx);
                $sheet->getStyle("{$colLetter}1")->applyFromArray(['borders' => $thickBorder]);
                $sheet->getStyle("{$colLetter}2")->applyFromArray(['borders' => $thickBorder]);
            }

            $subtotalCols = [];
            $col = 8;
            foreach (self::CATS as $kat => $info) {
                $hasDefect = in_array($kat, self::DEFECT_CATS);
                $col += count($info['fields']);
                if ($hasDefect) { $col++; }
                $subtotalCols[] = $col;
                $col++;
            }

            for ($r = 3; $r <= $lastRow; $r++) {
                $val = $sheet->getCell("A{$r}")->getValue();
                if ($val && str_starts_with($val, '▶')) {
                    $sheet->mergeCells("A{$r}:{$lastCol}{$r}");
                    $sheet->getStyle("A{$r}:{$lastCol}{$r}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '4C1D95']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F5F3FF']],
                    ]);
                } elseif ($val !== '') {
                    $sheet->getStyle("A{$r}:{$lastCol}{$r}")->applyFromArray([
                        'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                    ]);

                    $deductionColLetter = Coordinate::stringFromColumnIndex($totalPointCol + 1);
                    $sheet->getStyle("{$deductionColLetter}{$r}")->getNumberFormat()->setFormatCode('[Red]-0.00');

                    if (isset($toggle) && $toggle) {
                        $sheet->getStyle("A{$r}:{$lastCol}{$r}")->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FAF5FF']],
                        ]);
                    }
                    $toggle = !($toggle ?? false);

                    foreach ($separatorCols as $colIdx) {
                        $colLetter = Coordinate::stringFromColumnIndex($colIdx);
                        $sheet->getStyle("{$colLetter}{$r}")->applyFromArray(['borders' => $thickBorder]);
                    }
                }
            }

            $sheet->getStyle("A2:{$lastCol}{$lastRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDD6FE']],
                ],
            ]);

            foreach ($subtotalCols as $colIdx) {
                $colLetter = Coordinate::stringFromColumnIndex($colIdx);
                $sheet->getStyle("{$colLetter}3:{$colLetter}{$lastRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F5F3FF']],
                ]);
            }

            for ($c = 8; $c <= $lastColIdx - 5; $c++) {
                $colLetter = Coordinate::stringFromColumnIndex($c);
                $sheet->getStyle("{$colLetter}3:{$colLetter}{$lastRow}")->getNumberFormat()->setFormatCode('0.00');
            }

            $sheet->freezePane('H3');
            $sheet->getRowDimension(1)->setRowHeight(20);
            $sheet->getRowDimension(2)->setRowHeight(30);
        }];
    }
}