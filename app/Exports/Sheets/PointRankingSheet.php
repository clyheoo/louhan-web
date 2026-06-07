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

    // ★ Mapping field ID ke nama kolom config database
    private const PCT_MAP = [
        'overall' => ['impression' => 'overall_point'],
        'head' => ['size' => 'head_size_pct', 'bentuk' => 'head_bentuk_k_pct'],
        'face' => ['face' => 'face_face_pct'],
        'body' => ['bentuk' => 'body_bentuk_pct', 'proporsi' => 'body_proposional_pct', 'pangkal' => 'body_pangkal_pct'],
        'marking' => ['fullness' => 'marking_fullness_pct', 'contrast' => 'marking_contrast_pct', 'bentuk' => 'marking_bentuk_pct'],
        'pearl' => ['shinning' => 'pearl_shinning_pct', 'fullness' => 'pearl_fullnes_pct', 'bentuk' => 'pearl_bentuk_pearl_pct'],
        'color' => ['komposisi' => 'color_komposisi_pct', 'kecerahan' => 'color_kecerahan_pct', 'fullness' => 'color_fullness_colour_pct'],
        'finnage' => ['bentuk' => 'finnage_bentuk_sirip_ekor_pct', 'kecerahan' => 'finnage_kecerahan_pct'],
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

    /**
     * ★ MENGHITUNG POINT IKAN - 100% KONSISTEN DENGAN POINTCALCULATOR
     */
    private function calcIkan($ikan, $configs)
    {
        $scorings = $ikan->scorings;
        if ($scorings->isEmpty()) return null;
        
        // ═══════════════════════════════════════════════════════════
        // STEP 1: Hitung Rata-rata Nilai Detail dari Semua Juri
        // ═══════════════════════════════════════════════════════════
        $avgDetail = [];
        $jumlahJuriYangNilai = 0;

        foreach ($scorings as $s) {
            if ($s->total_nilai) $jumlahJuriYangNilai++;
            if ($s->nilai_detail && is_array($s->nilai_detail)) {
                foreach ($s->nilai_detail as $kat => $fields) {
                    if (!is_array($fields)) continue;
                    foreach ($fields as $fid => $val) {
                        if ($fid === 'defect') continue;
                        // Fix typo shining -> shinning
                        if ($kat === 'pearl' && $fid === 'shining') $fid = 'shinning';
                        
                        if (!isset($avgDetail[$kat][$fid])) {
                            $avgDetail[$kat][$fid] = ['sum' => 0, 'count' => 0];
                        }
                        $avgDetail[$kat][$fid]['sum'] += (float)($val ?? 0);
                        $avgDetail[$kat][$fid]['count']++;
                    }
                }
            }
        }

        $finalAvgDetail = [];
        if ($jumlahJuriYangNilai > 0) {
            foreach ($avgDetail as $kat => $fields) {
                $finalAvgDetail[$kat] = [];
                foreach ($fields as $fid => $d) {
                    $finalAvgDetail[$kat][$fid] = $d['count'] > 0 ? $d['sum'] / $d['count'] : 0;
                }
            }
        }

        // ═══════════════════════════════════════════════════════════
        // STEP 2: Gabungkan Defect dari Semua Juri (Union - tanpa duplikat)
        // ═══════════════════════════════════════════════════════════
        $defectKeys = ['raw_head_penalty', 'raw_face_penalty', 'raw_body_penalty', 'raw_finnage_penalty'];
        $combinedDefects = [];
        foreach ($defectKeys as $dk) { 
            $combinedDefects[$dk] = []; 
        }

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
        
        // Format untuk dikirim ke helper (harus ada value minimal '0' jika kosong)
        $defectDataForCalc = [];
        foreach ($combinedDefects as $dk => &$defs) {
            $defectDataForCalc[$dk] = count($defs) > 0 ? $defs : ['0'];
        }
        unset($defs);

        // ═══════════════════════════════════════════════════════════
        // STEP 3: Hitung Breakdown Point menggunakan HELPER (KONSISTEN!)
        // ═══════════════════════════════════════════════════════════
        $breakdown = PointCalculator::hitungBreakdown($ikan->kategori, $finalAvgDetail);
        
        if (!$breakdown) return null;

        // ═══════════════════════════════════════════════════════════
        // STEP 4: Hitung Detail Defect menggunakan HELPER
        // ═══════════════════════════════════════════════════════════
        $defectDetails = PointCalculator::getDefectDetails($defectDataForCalc);
        
        // ═══════════════════════════════════════════════════════════
        // STEP 5: Hitung Point Per Field untuk Display Kolom Excel
        // Menggunakan RUMUS YANG SAMA PERSIS dengan PointCalculator
        // ═══════════════════════════════════════════════════════════
        $cfg = $configs->get($ikan->kategori);
        $compPoints = [];
        $catSubs = [];
        $totalDeduction = 0;
        
        if ($cfg) {
            foreach (self::CATS as $kat => $info) {
                $bobotKey = $kat . '_bobot';
                $bobot = (float)($cfg->$bobotKey ?? 0);
                
                // Hitung point per field menggunakan rumus: (nilai × bobot × pct) / 100
                foreach ($info['fields'] as $f) {
                    $val = $finalAvgDetail[$kat][$f['id']] ?? 0;
                    $pctColName = self::PCT_MAP[$kat][$f['id']] ?? null;
                    $pct = $pctColName ? (float)($cfg->$pctColName ?? 0) : 0;
                    
                    // ★ RUMUS SAMA PERSIS DENGAN HELPER
                    $compPoints[$kat][$f['id']] = round(($val * $bobot * $pct) / 100, 2);
                }
                
                // ═══════════════════════════════════════════════════
                // STEP 6: Ambil Subtotal dari Breakdown & Terapkan Penalty
                // ═══════════════════════════════════════════════════
                $rawPoint = $breakdown[$kat]['point'] ?? 0;
                
                // Terapkan defect penalty jika ada
                $defectInfo = $defectDetails[$kat] ?? [];
                if (!empty($defectInfo['percent'])) {
                    $penaltyPercent = $defectInfo['percent_value'];
                    $deductionAmount = $rawPoint * ($penaltyPercent / 100);
                    $totalDeduction += $deductionAmount;
                    $catSubs[$kat] = round($rawPoint - $deductionAmount, 2);
                } else {
                    $catSubs[$kat] = $rawPoint;
                }
            }
        }

        // ═══════════════════════════════════════════════════════════
        // STEP 7: Format Defect Strings untuk Kolom Excel
        // Format: "10% - Minor: Kutil" atau "30% - Mayor: Bagian Bibir Hilang"
        // ═══════════════════════════════════════════════════════════
        $defectStrings = [];
        foreach (self::DEFECT_CATS as $cat) {
            $info = $defectDetails[$cat] ?? [];
            if (!empty($info['percent'])) {
                $defectStrings[$cat] = $info['percent'] . ' - ' . $info['label'];
            } else {
                $defectStrings[$cat] = '';
            }
        }

        // ═══════════════════════════════════════════════════════════
        // STEP 8: Hitung Final Values
        // ═══════════════════════════════════════════════════════════
        $totalPoint = $breakdown['total'] ?? round(array_sum($catSubs));
        $totalBonus = (int) $ikan->bonusPoints->sum('points');
        $totalDeductionPercent = $defectDetails['total_deduction_percent'] ?? 0;

        return [
            'ikan_id' => $ikan->id,
            'nama_peserta' => $ikan->nama_peserta   ?? $ikan->peserta?->nama_peserta   ?? '—',
            'kategori' => $ikan->kategori, 
            'kelas' => $ikan->kelas ?? '—',
            'nomor_tank' => $ikan->nomor_tank, 
            'asal' => $ikan->detail_anggota         ?? $ikan->peserta?->detail_anggota ?? '—',
            'jml_juri' => $scorings->count(), 
            'comp_points' => $compPoints,
            'cat_subs' => $catSubs, 
            'total_point' => $totalPoint,
            'total_deduction' => round($totalDeduction, 2),
            'total_deduction_percent' => $totalDeductionPercent,
            'total_bonus' => $totalBonus, 
            'defects' => $defectStrings,
        ];
    }

    public function array(): array
    {
        $configs = ScoringPointConfig::all()->keyBy('kategori');
        $ikans = Ikan::where('is_locked', true)->whereNotNull('nomor_tank')
            ->whereHas('scorings')
            ->with(['peserta', 'scorings', 'bonusPoints'])
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

        // ═══════════════════════════════════════════════════════════
        // HITUNG POSISI KOLOM DINAMIS
        // ═══════════════════════════════════════════════════════════
        $this->catStartCols = [];
        $col = 8; // Dimulai dari kolom H (setelah 7 kolom fixed)
        
        foreach (self::CATS as $kat => $info) {
            $this->catStartCols[] = $col;
            $hasDefect = in_array($kat, self::DEFECT_CATS);
            $fieldCount = count($info['fields']);
            $defectCol = $hasDefect ? 1 : 0;
            // Kolom: fields + defect (jika ada) + subtotal
            $col += $fieldCount + $defectCol + 1; 
        }

        // ═══════════════════════════════════════════════════════════
        // ROW 1: HEADER KATEGORI (OVERALL, HEAD, FACE, dll)
        // ═══════════════════════════════════════════════════════════
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
        
        // Kolom Fixed di akhir
        foreach (['TOTAL POINT', 'DEFECT DEDUCTION', 'BONUS', 'RANK POINT', 'JUARA'] as $h) {
            $catRow[] = $h;
        }
        $colCount = count($catRow);

        // ═══════════════════════════════════════════════════════════
        // ROW 2: HEADER DETAIL (Size, Bentuk, DEFECT, SUBTOTAL, dll)
        // ═══════════════════════════════════════════════════════════
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
        // Kolom Fixed di akhir (kosong karena sudah di row 1)
        for ($i = 0; $i < 5; $i++) { 
            $compRow[] = ''; 
        }

        $rows = [$catRow, $compRow];

        // ═══════════════════════════════════════════════════════════
        // DATA ROWS: Grouped & Ranked
        // ═══════════════════════════════════════════════════════════
        foreach ($groups as $groupName => $items) {
            // ★ Gunakan PointCalculator::hitungRankPoints() — SAMA PERSIS dengan website
            $rankInput = [];
            foreach ($items as $it) {
                $rankInput[] = [
                    'ikan_id'     => $it['ikan_id'],
                    'total_point' => $it['total_point'],
                    'total_bonus' => $it['total_bonus'],
                ];
            }
            $rankedItems = PointCalculator::hitungRankPoints($rankInput, 'total_point');

            // Build lookup by ikan_id
            $rankLookup = [];
            foreach ($rankedItems as $ri) {
                $rankLookup[$ri['ikan_id']] = $ri;
            }

            // Merge rank data back into items
            $ranked = [];
            foreach ($items as $it) {
                $ri = $rankLookup[$it['ikan_id']] ?? null;
                $it['rank_point'] = $ri['rank_point'] ?? 0;
                $it['final_rank_point'] = $ri['final_rank_point'] ?? 0;
                $it['position'] = $ri['position'] ?? 0;
                $ranked[] = $it;
            }

            // Sort by final_rank_point descending (sama seperti website: post-bonus re-sort)
            usort($ranked, fn($a, $b) => ($b['final_rank_point'] ?? 0) <=> ($a['final_rank_point'] ?? 0));

            // Separator Row
            $sep = array_fill(0, $colCount, '');
            $sep[0] = '▶ ' . strtoupper($groupName) . ' (' . count($ranked) . ' peserta)';
            $rows[] = $sep;

            // Data Rows
            foreach ($ranked as $i => $d) {
                $row = [
                    $i + 1, 
                    $d['nama_peserta'], 
                    strtoupper($d['kategori']), 
                    $d['kelas'], 
                    $d['nomor_tank'], 
                    $d['asal'], 
                    $d['jml_juri']
                ];
                
                // Point per kategori
                foreach (self::CATS as $kat => $info) {
                    // Field points
                    foreach ($info['fields'] as $f) {
                        $row[] = $d['comp_points'][$kat][$f['id']] ?? 0;
                    }
                    // Defect info (hanya kategori yang punya defect)
                    if (in_array($kat, self::DEFECT_CATS)) {
                        // ★ FORMAT: "10% - Minor: Kutil" atau "30% - Mayor: Bagian Bibir Hilang"
                        $row[] = $d['defects'][$kat] ?? '';
                    }
                    // Subtotal (sudah dikurangi penalty)
                    $row[] = $d['cat_subs'][$kat] ?? 0;
                }
                
                // Kolom Fixed
                $row[] = $d['total_point'];
                // DEFECT DEDUCTION: persen total pengurangan
                $row[] = $d['total_deduction_percent'] > 0 ? $d['total_deduction_percent'] . '%' : '-';
                $row[] = $d['total_bonus'];
                // ★ RANK POINT: final_rank_point (rank base + bonus) — sama seperti website
                $row[] = $d['final_rank_point'] ?? 0;
                // ★ JUARA column
                $pos = $d['position'] ?? 0;
                if ($pos === 1) {
                    $row[] = '🥇 JUARA 1';
                } elseif ($pos === 2) {
                    $row[] = '🥈 JUARA 2';
                } elseif ($pos === 3) {
                    $row[] = '🥉 JUARA 3';
                } elseif ($pos >= 4 && $pos <= 10) {
                    $row[] = 'Top 10 (#' . $pos . ')';
                } else {
                    $row[] = '-';
                }
                
                $rows[] = $row;
            }

            // ★ CHAMPION SUMMARY ROW — tampilkan Juara 1 per group
            $champion = null;
            foreach ($ranked as $r) {
                if (($r['position'] ?? 0) === 1) {
                    $champion = $r;
                    break;
                }
            }
            if ($champion) {
                $champRow = array_fill(0, $colCount, '');
                $bonusText = ($champion['total_bonus'] ?? 0) > 0 
                    ? ' (Base ' . ($champion['rank_point'] ?? 0) . ' + Bonus ' . $champion['total_bonus'] . ')' 
                    : '';
                $champRow[0] = '🏆 JUARA 1: ' . $champion['nama_peserta'] 
                    . ' | Tank ' . $champion['nomor_tank'] 
                    . ' | ' . strtoupper($champion['kategori']) . ' ' . $champion['kelas']
                    . ' | Rank Point: ' . ($champion['final_rank_point'] ?? 0) . $bonusText;
                $rows[] = $champRow;
            }
            
            // Empty row after group
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

            // ═══════════════════════════════════════════════════════════
            // SET COLUMN WIDTHS
            // ═══════════════════════════════════════════════════════════
            for ($c = 1; $c <= $lastColIdx; $c++) {
                $letter = Coordinate::stringFromColumnIndex($c);
                if ($c <= 2) {
                    $w = 14;
                } elseif ($c <= 7) {
                    $w = 12;
                } else {
                    // Cek apakah ini kolom DEFECT (lebih lebar untuk teks panjang)
                    $isDefectCol = false;
                    $colOffset = $c - 8;
                    $checkCol = 8;
                    foreach (self::CATS as $kat => $info) {
                        $hasDefect = in_array($kat, self::DEFECT_CATS);
                        $fieldCount = count($info['fields']);
                        
                        if ($hasDefect && $c == $checkCol + $fieldCount) {
                            $isDefectCol = true;
                            break;
                        }
                        $checkCol += $fieldCount + ($hasDefect ? 1 : 0) + 1;
                    }
                    
                    // Cek apakah ini kolom DEFECT DEDUCTION
                    $deductionColIdx = $lastColIdx - 4;
                    $isDeductionCol = ($c == $deductionColIdx);
                    
                    if ($isDefectCol) {
                        $w = 40; // Lebar untuk teks defect panjang
                    } elseif ($isDeductionCol) {
                        $w = 18;
                    } else {
                        $w = 13;
                    }
                }
                $sheet->getColumnDimension($letter)->setAutoSize(false)->setWidth($w);
            }

            // ═══════════════════════════════════════════════════════════
            // MERGE CELLS untuk Header Kategori
            // ═══════════════════════════════════════════════════════════
            foreach ($this->mergeRanges as $range) {
                $s = Coordinate::stringFromColumnIndex($range[0]);
                $e = Coordinate::stringFromColumnIndex($range[1]);
                $sheet->mergeCells("{$s}1:{$e}1");
            }

            // ═══════════════════════════════════════════════════════════
            // STYLE ROW 1: Header Kategori (Ungu Tua)
            // ═══════════════════════════════════════════════════════════
            $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
                'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '6D28D9']],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
            ]);

            // ═══════════════════════════════════════════════════════════
            // STYLE ROW 2: Header Detail (Ungu Muda)
            // ═══════════════════════════════════════════════════════════
            $sheet->getStyle("A2:{$lastCol}2")->applyFromArray([
                'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '7C3AED']],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
            ]);

            // ═══════════════════════════════════════════════════════════
            // SEPARATOR LINES (Garis Tebal Pembatas Kategori)
            // ═══════════════════════════════════════════════════════════
            $separatorCols = array_slice($this->catStartCols, 1);
            $totalPointCol = $lastColIdx - 4;
            $separatorCols[] = $totalPointCol;
            $separatorCols[] = $lastColIdx - 1; // ★ RANK POINT column separator

            $thickBorder = [
                'left' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '4C1D95']],
            ];

            foreach ($separatorCols as $colIdx) {
                $colLetter = Coordinate::stringFromColumnIndex($colIdx);
                $sheet->getStyle("{$colLetter}1")->applyFromArray(['borders' => $thickBorder]);
                $sheet->getStyle("{$colLetter}2")->applyFromArray(['borders' => $thickBorder]);
            }

            // ═══════════════════════════════════════════════════════════
            // IDENTIFIKASI KOLOM SUBTOTAL
            // ═══════════════════════════════════════════════════════════
            $subtotalCols = [];
            $col = 8;
            foreach (self::CATS as $kat => $info) {
                $hasDefect = in_array($kat, self::DEFECT_CATS);
                $col += count($info['fields']);
                if ($hasDefect) { $col++; }
                $subtotalCols[] = $col;
                $col++;
            }

            // ═══════════════════════════════════════════════════════════
            // IDENTIFIKASI KOLOM DEFECT (untuk styling khusus)
            // ═══════════════════════════════════════════════════════════
            $defectCols = [];
            $col = 8;
            foreach (self::CATS as $kat => $info) {
                $hasDefect = in_array($kat, self::DEFECT_CATS);
                $fieldCount = count($info['fields']);
                if ($hasDefect) {
                    $defectCols[] = $col + $fieldCount;
                }
                $col += $fieldCount + ($hasDefect ? 1 : 0) + 1;
            }

            // ═══════════════════════════════════════════════════════════
            // STYLE DATA ROWS
            // ═══════════════════════════════════════════════════════════
            $toggle = false;
            for ($r = 3; $r <= $lastRow; $r++) {
                $val = $sheet->getCell("A{$r}")->getValue();
                
                // Separator Row (Group Header)
                if ($val && str_starts_with($val, '▶')) {
                    $sheet->mergeCells("A{$r}:{$lastCol}{$r}");
                    $sheet->getStyle("A{$r}:{$lastCol}{$r}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '4C1D95']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F5F3FF']],
                    ]);
                } 
                // ★ Champion Row (Juara 1 Summary)
                elseif ($val && str_starts_with($val, '🏆')) {
                    $sheet->mergeCells("A{$r}:{$lastCol}{$r}");
                    $sheet->getStyle("A{$r}:{$lastCol}{$r}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '92400E']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FEF3C7']],
                        'alignment' => ['horizontal' => 'left', 'vertical' => 'center'],
                    ]);
                }
                // Data Row
                elseif ($val !== '') {
                    $sheet->getStyle("A{$r}:{$lastCol}{$r}")->applyFromArray([
                        'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                    ]);

                    // Alternating row colors
                    if ($toggle) {
                        $sheet->getStyle("A{$r}:{$lastCol}{$r}")->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FAF5FF']],
                        ]);
                    }
                    $toggle = !$toggle;

                    // Separator lines di data rows
                    foreach ($separatorCols as $colIdx) {
                        $colLetter = Coordinate::stringFromColumnIndex($colIdx);
                        $sheet->getStyle("{$colLetter}{$r}")->applyFromArray(['borders' => $thickBorder]);
                    }

                    // ★ Style khusus kolom DEFECT (align left, wrap text, warna khusus)
                    foreach ($defectCols as $colIdx) {
                        $colLetter = Coordinate::stringFromColumnIndex($colIdx);
                        $cellValue = $sheet->getCell("{$colLetter}{$r}")->getValue();
                        
                        if ($cellValue && $cellValue !== '') {
                            // Deteksi apakah Mayor atau Minor untuk warna berbeda
                            $isMayor = str_contains($cellValue, 'Mayor');
                            $bgColor = $isMayor ? 'FEE2E2' : 'FEF3C7'; // Merah muda untuk Mayor, Kuning untuk Minor
                            $textColor = $isMayor ? '991B1B' : '92400E';
                            
                            $sheet->getStyle("{$colLetter}{$r}")->applyFromArray([
                                'alignment' => ['horizontal' => 'left', 'vertical' => 'center', 'wrapText' => true],
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => $bgColor]],
                                'font' => ['size' => 8, 'color' => ['rgb' => $textColor]],
                            ]);
                        }
                    }

                    // ★ Style khusus kolom DEFECT DEDUCTION
                    $deductionColLetter = Coordinate::stringFromColumnIndex($totalPointCol + 1);
                    $deductionValue = $sheet->getCell("{$deductionColLetter}{$r}")->getValue();
                    if ($deductionValue && $deductionValue !== '-') {
                        $sheet->getStyle("{$deductionColLetter}{$r}")->applyFromArray([
                            'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'DC2626']],
                        ]);
                    }

                    // ★ Style khusus kolom JUARA (kolom terakhir)
                    $juaraColLetter = Coordinate::stringFromColumnIndex($lastColIdx);
                    $juaraValue = $sheet->getCell("{$juaraColLetter}{$r}")->getValue();
                    if ($juaraValue && $juaraValue !== '-') {
                        if (str_contains($juaraValue, 'JUARA 1')) {
                            $sheet->getStyle("{$juaraColLetter}{$r}")->applyFromArray([
                                'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '92400E']],
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FEF3C7']],
                                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                            ]);
                        } elseif (str_contains($juaraValue, 'JUARA 2')) {
                            $sheet->getStyle("{$juaraColLetter}{$r}")->applyFromArray([
                                'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '57534E']],
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'E7E5E4']],
                                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                            ]);
                        } elseif (str_contains($juaraValue, 'JUARA 3')) {
                            $sheet->getStyle("{$juaraColLetter}{$r}")->applyFromArray([
                                'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '9A3412']],
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FED7AA']],
                                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                            ]);
                        } else {
                            $sheet->getStyle("{$juaraColLetter}{$r}")->applyFromArray([
                                'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '6D28D9']],
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F5F3FF']],
                                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                            ]);
                        }
                    }

                    // ★ Style khusus kolom RANK POINT (bold + background emas)
                    $rankPointColLetter = Coordinate::stringFromColumnIndex($lastColIdx - 1);
                    $sheet->getStyle("{$rankPointColLetter}{$r}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 11],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FEF9C3']],
                        'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                    ]);
                }
            }

            // ═══════════════════════════════════════════════════════════
            // BORDER ALL CELLS
            // ═══════════════════════════════════════════════════════════
            $sheet->getStyle("A2:{$lastCol}{$lastRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDD6FE']],
                ],
            ]);

            // ═══════════════════════════════════════════════════════════
            // STYLE KOLOM SUBTOTAL (Bold + Background)
            // ═══════════════════════════════════════════════════════════
            foreach ($subtotalCols as $colIdx) {
                $colLetter = Coordinate::stringFromColumnIndex($colIdx);
                $sheet->getStyle("{$colLetter}3:{$colLetter}{$lastRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F5F3FF']],
                ]);
            }

            // ═══════════════════════════════════════════════════════════
            // NUMBER FORMAT untuk kolom point
            // ═══════════════════════════════════════════════════════════
            for ($c = 8; $c <= $lastColIdx - 5; $c++) {
                $colLetter = Coordinate::stringFromColumnIndex($c);
                // Skip kolom defect (bukan angka)
                if (!in_array($c, $defectCols)) {
                    $sheet->getStyle("{$colLetter}3:{$colLetter}{$lastRow}")
                        ->getNumberFormat()->setFormatCode('0.00');
                }
            }

            // ★ Number format untuk kolom TOTAL POINT (2 desimal)
            $totalPointColLetter = Coordinate::stringFromColumnIndex($lastColIdx - 4);
            $sheet->getStyle("{$totalPointColLetter}3:{$totalPointColLetter}{$lastRow}")
                ->getNumberFormat()->setFormatCode('0.00');

            // ★ Number format untuk kolom RANK POINT (integer)
            $rankPointColLetter = Coordinate::stringFromColumnIndex($lastColIdx - 1);
            $sheet->getStyle("{$rankPointColLetter}3:{$rankPointColLetter}{$lastRow}")
                ->getNumberFormat()->setFormatCode('0');

            // ═══════════════════════════════════════════════════════════
            // FREEZE PANES & ROW HEIGHT
            // ═══════════════════════════════════════════════════════════
            $sheet->freezePane('H3');
            $sheet->getRowDimension(1)->setRowHeight(20);
            $sheet->getRowDimension(2)->setRowHeight(30);
        }];
    }
}