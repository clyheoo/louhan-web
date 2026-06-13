<?php

namespace App\Helpers;

use App\Models\ScoringPointConfig;

class PointCalculator
{
    const MINOR_DEFECTS = [
        'Kutil',
        'Bibir Miring',                                      // legacy
        'Bibir Miring (kasat mata)',                         // baru
        'Katarak',
        'Bibir Tidak Menutup & Selaput Bergerak',   // dipindah dari mayor ke minor
        'Abses / Luka',
        'Fintail Bleaching',                                 // legacy
        'Fintail Bleaching / Transparan',                    // baru
        'Pangkal Ekor Naik/Trn',                             // legacy
        'Pangkal Ekor Naik atau Turun',                      // baru
        'Dayung Tdk Seimbang',                               // legacy
        'Sirip Dayung Tidak Seimbang',                       // baru
    ];

    const MAYOR_DEFECTS = [
        'Bagian Bibir Hilang',
        'Mulut Terbuka Terus',                               // legacy
        'Muka Miring',
        'Pangkal Bengkok/Patah',                             // legacy
        'Pangkal Bengkok / Melintir',                        // baru
        'Fin/Tulang Hilang 1 Ruas',
    ];

    private static function normalizeDefectName($value): string
    {
        $value = trim((string) $value);
        return preg_replace('/\s+Sempurna/u', '', $value) ?? $value;
    }

    public static function hitungPoint(string $kategori, array $nd, array $defectData = []): float
    {
        $cfg = ScoringPointConfig::where('kategori', $kategori)->first();
        if (!$cfg || empty($nd)) return 0;

        $categoryPoints = [];

        // ★ OVERALL: nilai juri x overall x point overal / 100
        $v = (float)($nd['overall']['impression'] ?? 0);
        $categoryPoints['overall'] = ($v * (float)$cfg->overall_bobot * (float)$cfg->overall_point) / 100;

        // ★ HEAD: (size * head * %size / 100) + (bentuk * head * %bentuk_k / 100)
        $headPt = 0;
        $v = (float)($nd['head']['size'] ?? 0);
        $headPt += ($v * (float)$cfg->head_bobot * (float)$cfg->head_size_pct) / 100;
        $v = (float)($nd['head']['bentuk'] ?? 0);
        $headPt += ($v * (float)$cfg->head_bobot * (float)$cfg->head_bentuk_k_pct) / 100;
        $categoryPoints['head'] = $headPt;

        // ★ FACE: nilai juri x face x %face / 100
        if (isset($nd['face']['face'])) {
            $v = (float)$nd['face']['face'];
            $categoryPoints['face'] = ($v * (float)$cfg->face_bobot * (float)$cfg->face_face_pct) / 100;
        } else {
            // Fallback format lama (jika ada data lama yang belum migrasi)
            $faceSum = 0;
            foreach (['pipi','mata','bibir','kondisi'] as $k) {
                $faceSum += (float)($nd['face'][$k] ?? 0);
            }
            $categoryPoints['face'] = ($faceSum * (float)$cfg->face_bobot * (float)$cfg->face_face_pct) / 100;
        }

        // ★ BODY: (bentuk * body * %bentuk / 100) + (proporsi * body * %proporsional / 100) + (pangkal * body * %pangkal / 100)
        $bodyPt = 0;
        $v = (float)($nd['body']['bentuk'] ?? 0);
        $bodyPt += ($v * (float)$cfg->body_bobot * (float)$cfg->body_bentuk_pct) / 100;
        $v = (float)($nd['body']['proporsi'] ?? 0);
        $bodyPt += ($v * (float)$cfg->body_bobot * (float)$cfg->body_proposional_pct) / 100;
        $v = (float)($nd['body']['pangkal'] ?? 0);
        $bodyPt += ($v * (float)$cfg->body_bobot * (float)$cfg->body_pangkal_pct) / 100;
        $categoryPoints['body'] = $bodyPt;

        // ★ MARKING: (fullness * marking * %fullness / 100) + (contrast * marking * %contrast / 100) + (bentuk * marking * %bentuk / 100)
        $markingPt = 0;
        $v = (float)($nd['marking']['fullness'] ?? 0);
        $markingPt += ($v * (float)$cfg->marking_bobot * (float)$cfg->marking_fullness_pct) / 100;
        $v = (float)($nd['marking']['contrast'] ?? 0);
        $markingPt += ($v * (float)$cfg->marking_bobot * (float)$cfg->marking_contrast_pct) / 100;
        $v = (float)($nd['marking']['bentuk'] ?? 0);
        $markingPt += ($v * (float)$cfg->marking_bobot * (float)$cfg->marking_bentuk_pct) / 100;
        $categoryPoints['marking'] = $markingPt;

        // ★ PEARL: (shinning * pearl * %shinning / 100) + (fullness * pearl * %fullnes / 100) + (bentuk * pearl * %bentuk_pearl / 100)
        $pearlPt = 0;
        // Menggunakan shinning ?? shining untuk menghindari error typo dari inputan lama
        $v = (float)($nd['pearl']['shinning'] ?? $nd['pearl']['shining'] ?? 0);
        $pearlPt += ($v * (float)$cfg->pearl_bobot * (float)$cfg->pearl_shinning_pct) / 100;
        $v = (float)($nd['pearl']['fullness'] ?? 0);
        $pearlPt += ($v * (float)$cfg->pearl_bobot * (float)$cfg->pearl_fullnes_pct) / 100;
        $v = (float)($nd['pearl']['bentuk'] ?? 0);
        $pearlPt += ($v * (float)$cfg->pearl_bobot * (float)$cfg->pearl_bentuk_pearl_pct) / 100;
        $categoryPoints['pearl'] = $pearlPt;

        // ★ COLOUR: (komposisi * colour * %komposisi / 100) + (kecerahan * colour * %kecerahan / 100) + (fullness * colour * %fullness_colour / 100)
        $colorPt = 0;
        $v = (float)($nd['color']['komposisi'] ?? 0);
        $colorPt += ($v * (float)$cfg->color_bobot * (float)$cfg->color_komposisi_pct) / 100;
        $v = (float)($nd['color']['kecerahan'] ?? 0);
        $colorPt += ($v * (float)$cfg->color_bobot * (float)$cfg->color_kecerahan_pct) / 100;
        $v = (float)($nd['color']['fullness'] ?? 0);
        $colorPt += ($v * (float)$cfg->color_bobot * (float)$cfg->color_fullness_colour_pct) / 100;
        $categoryPoints['color'] = $colorPt;

        // ★ FINNAGE: (bentuk * finnage * %bentuk_sirip_ekor / 100) + (kecerahan * finnage * %kecerahan / 100)
        $finnagePt = 0;
        $v = (float)($nd['finnage']['bentuk'] ?? 0);
        $finnagePt += ($v * (float)$cfg->finnage_bobot * (float)$cfg->finnage_bentuk_sirip_ekor_pct) / 100;
        $v = (float)($nd['finnage']['kecerahan'] ?? 0);
        $finnagePt += ($v * (float)$cfg->finnage_bobot * (float)$cfg->finnage_kecerahan_pct) / 100;
        $categoryPoints['finnage'] = $finnagePt;

        // ★ TERAPKAN DEFECT PENALTY (PENGURANGAN POINT)
        if (!empty($defectData)) {
            $evaluated = self::evaluateDefects($defectData);
            $defectParts = ['head', 'face', 'body', 'finnage'];
            
            foreach ($defectParts as $p) {
                $penaltyKey = "{$p}_penalty";
                if (!empty($evaluated[$penaltyKey])) {
                    $penaltyPercent = (float)str_replace('%', '', $evaluated[$penaltyKey]);
                    $categoryPoints[$p] = $categoryPoints[$p] * (1 - ($penaltyPercent / 100));
                }
            }
        }

        // ★ TOTAL POINT AKHIR (PENJUMLAHAN SELURUH KOMPONEN)
        return round(array_sum($categoryPoints), 2);
    }

    // ★ FUNGSI EVALUASI DEFECT (TIDAK BERUBAH)
    public static function evaluateDefects(array $defectData): array
    {
        $parts = ['head', 'face', 'body', 'finnage'];
        $partStatus = [
            'head'    => ['minor' => false, 'mayor' => false, 'items' => []],
            'face'    => ['minor' => false, 'mayor' => false, 'items' => []],
            'body'    => ['minor' => false, 'mayor' => false, 'items' => []],
            'finnage' => ['minor' => false, 'mayor' => false, 'items' => []],
        ];
        foreach ($parts as $p) {
            $key = "raw_{$p}_penalty";
            $defs = $defectData[$key] ?? ['0'];
            if (is_string($defs)) $defs = [$defs];
            
            foreach ($defs as $d) {
                $d = self::normalizeDefectName($d);

                if ($d && $d !== '0') {
                    $partStatus[$p]['items'][] = $d;
                    if (in_array($d, self::MINOR_DEFECTS)) { $partStatus[$p]['minor'] = true; }
                    if (in_array($d, self::MAYOR_DEFECTS)) { $partStatus[$p]['mayor'] = true; }
                }
            }
        }

        // ★ FIX: hitung JUMLAH KOMPONEN yang punya minor, bukan total minor defect
        $componentsWithMinor = 0;
        foreach ($parts as $p) {
            if ($partStatus[$p]['minor']) $componentsWithMinor++;
        }
        $isGlobalMayor = $componentsWithMinor >= 3;
        $results = []; $globalNotes = [];
        
        foreach ($parts as $p) {
            if (count($partStatus[$p]['items']) > 0) {
                $isMayor = $partStatus[$p]['mayor'] || ($partStatus[$p]['minor'] && $isGlobalMayor);
                $results["{$p}_penalty"] = $isMayor ? '30%' : '10%';
                $globalNotes[] = strtoupper($p) . ': ' . implode(', ', $partStatus[$p]['items']);
            } else {
                $results["{$p}_penalty"] = '';
            }
        }
        $results['keterangan'] = implode(' | ', $globalNotes);
        return $results;
    }

    // ★ FUNGSI BREAKDOWN UNTUK TAMPILAN DETAIL (SUDAH DIADAPTASI RUMUS BARU)
    public static function hitungBreakdown(string $kategori, array $nd, array $defectData = []): array
    {
        $cfg = ScoringPointConfig::where('kategori', $kategori)->first();
        if (!$cfg || empty($nd)) return [];

        $b = [];
        $labels = [
            'overall' => 'Overall Impression', 'head' => 'Head', 'face' => 'Face',
            'body' => 'Body Shape', 'marking' => 'Marking', 'pearl' => 'Pearl',
            'color' => 'Color', 'finnage' => 'Finnage',
        ];
        $bobots = [
            'overall' => (float)$cfg->overall_bobot, 'head' => (float)$cfg->head_bobot,
            'face' => (float)$cfg->face_bobot, 'body' => (float)$cfg->body_bobot,
            'marking' => (float)$cfg->marking_bobot, 'pearl' => (float)$cfg->pearl_bobot,
            'color' => (float)$cfg->color_bobot, 'finnage' => (float)$cfg->finnage_bobot,
        ];

        $categoryPoints = [];
        $categoryParts = [];

        foreach ($labels as $kat => $label) {
            $pt = 0; $parts = [];

            if ($kat === 'overall') {
                $v = (float)($nd['overall']['impression'] ?? 0);
                $pt = ($v * $bobots['overall'] * (float)$cfg->overall_point) / 100;
                $parts[] = "({$v}×{$bobots['overall']}×{$cfg->overall_point}/100)=" . round($pt, 2);
            } 
            elseif ($kat === 'head') {
                $v1 = (float)($nd['head']['size'] ?? 0);
                $p1 = ($v1 * $bobots['head'] * (float)$cfg->head_size_pct) / 100;
                $v2 = (float)($nd['head']['bentuk'] ?? 0);
                $p2 = ($v2 * $bobots['head'] * (float)$cfg->head_bentuk_k_pct) / 100;
                $pt = $p1 + $p2;
                $parts[] = "({$v1}×{$bobots['head']}×{$cfg->head_size_pct}/100)=" . round($p1, 2);
                $parts[] = "({$v2}×{$bobots['head']}×{$cfg->head_bentuk_k_pct}/100)=" . round($p2, 2);
            } 
            elseif ($kat === 'face') {
                if (isset($nd['face']['face'])) {
                    $v = (float)$nd['face']['face'];
                    $pt = ($v * $bobots['face'] * (float)$cfg->face_face_pct) / 100;
                    $parts[] = "({$v}×{$bobots['face']}×{$cfg->face_face_pct}/100)=" . round($pt, 2);
                } else {
                    $faceSum = 0;
                    foreach (['pipi','mata','bibir','kondisi'] as $k) { $faceSum += (float)($nd['face'][$k] ?? 0); }
                    $pt = ($faceSum * $bobots['face'] * (float)$cfg->face_face_pct) / 100;
                    $parts[] = "(Sum:{$faceSum}×{$bobots['face']}×{$cfg->face_face_pct}/100)=" . round($pt, 2);
                }
            } 
            elseif ($kat === 'body') {
                $v1 = (float)($nd['body']['bentuk'] ?? 0); $p1 = ($v1 * $bobots['body'] * (float)$cfg->body_bentuk_pct) / 100;
                $v2 = (float)($nd['body']['proporsi'] ?? 0); $p2 = ($v2 * $bobots['body'] * (float)$cfg->body_proposional_pct) / 100;
                $v3 = (float)($nd['body']['pangkal'] ?? 0); $p3 = ($v3 * $bobots['body'] * (float)$cfg->body_pangkal_pct) / 100;
                $pt = $p1 + $p2 + $p3;
                $parts[] = "({$v1}×{$bobots['body']}×{$cfg->body_bentuk_pct}/100)=" . round($p1, 2);
                $parts[] = "({$v2}×{$bobots['body']}×{$cfg->body_proposional_pct}/100)=" . round($p2, 2);
                $parts[] = "({$v3}×{$bobots['body']}×{$cfg->body_pangkal_pct}/100)=" . round($p3, 2);
            } 
            elseif ($kat === 'marking') {
                $v1 = (float)($nd['marking']['fullness'] ?? 0); $p1 = ($v1 * $bobots['marking'] * (float)$cfg->marking_fullness_pct) / 100;
                $v2 = (float)($nd['marking']['contrast'] ?? 0); $p2 = ($v2 * $bobots['marking'] * (float)$cfg->marking_contrast_pct) / 100;
                $v3 = (float)($nd['marking']['bentuk'] ?? 0); $p3 = ($v3 * $bobots['marking'] * (float)$cfg->marking_bentuk_pct) / 100;
                $pt = $p1 + $p2 + $p3;
                $parts[] = "({$v1}×{$bobots['marking']}×{$cfg->marking_fullness_pct}/100)=" . round($p1, 2);
                $parts[] = "({$v2}×{$bobots['marking']}×{$cfg->marking_contrast_pct}/100)=" . round($p2, 2);
                $parts[] = "({$v3}×{$bobots['marking']}×{$cfg->marking_bentuk_pct}/100)=" . round($p3, 2);
            } 
            elseif ($kat === 'pearl') {
                $v1 = (float)($nd['pearl']['shinning'] ?? $nd['pearl']['shining'] ?? 0); $p1 = ($v1 * $bobots['pearl'] * (float)$cfg->pearl_shinning_pct) / 100;
                $v2 = (float)($nd['pearl']['fullness'] ?? 0); $p2 = ($v2 * $bobots['pearl'] * (float)$cfg->pearl_fullnes_pct) / 100;
                $v3 = (float)($nd['pearl']['bentuk'] ?? 0); $p3 = ($v3 * $bobots['pearl'] * (float)$cfg->pearl_bentuk_pearl_pct) / 100;
                $pt = $p1 + $p2 + $p3;
                $parts[] = "({$v1}×{$bobots['pearl']}×{$cfg->pearl_shinning_pct}/100)=" . round($p1, 2);
                $parts[] = "({$v2}×{$bobots['pearl']}×{$cfg->pearl_fullnes_pct}/100)=" . round($p2, 2);
                $parts[] = "({$v3}×{$bobots['pearl']}×{$cfg->pearl_bentuk_pearl_pct}/100)=" . round($p3, 2);
            } 
            elseif ($kat === 'color') {
                $v1 = (float)($nd['color']['komposisi'] ?? 0); $p1 = ($v1 * $bobots['color'] * (float)$cfg->color_komposisi_pct) / 100;
                $v2 = (float)($nd['color']['kecerahan'] ?? 0); $p2 = ($v2 * $bobots['color'] * (float)$cfg->color_kecerahan_pct) / 100;
                $v3 = (float)($nd['color']['fullness'] ?? 0); $p3 = ($v3 * $bobots['color'] * (float)$cfg->color_fullness_colour_pct) / 100;
                $pt = $p1 + $p2 + $p3;
                $parts[] = "({$v1}×{$bobots['color']}×{$cfg->color_komposisi_pct}/100)=" . round($p1, 2);
                $parts[] = "({$v2}×{$bobots['color']}×{$cfg->color_kecerahan_pct}/100)=" . round($p2, 2);
                $parts[] = "({$v3}×{$bobots['color']}×{$cfg->color_fullness_colour_pct}/100)=" . round($p3, 2);
            } 
            elseif ($kat === 'finnage') {
                $v1 = (float)($nd['finnage']['bentuk'] ?? 0); $p1 = ($v1 * $bobots['finnage'] * (float)$cfg->finnage_bentuk_sirip_ekor_pct) / 100;
                $v2 = (float)($nd['finnage']['kecerahan'] ?? 0); $p2 = ($v2 * $bobots['finnage'] * (float)$cfg->finnage_kecerahan_pct) / 100;
                $pt = $p1 + $p2;
                $parts[] = "({$v1}×{$bobots['finnage']}×{$cfg->finnage_bentuk_sirip_ekor_pct}/100)=" . round($p1, 2);
                $parts[] = "({$v2}×{$bobots['finnage']}×{$cfg->finnage_kecerahan_pct}/100)=" . round($p2, 2);
            }

            $categoryPoints[$kat] = $pt;
            $categoryParts[$kat] = $parts;
        }

        // ★ TERAPKAN DEFECT PENALTY (SAMA PERSIS DENGAN hitungPoint)
        $defectEvals = [];
        if (!empty($defectData)) {
            $evaluated = self::evaluateDefects($defectData);
            $defectParts = ['head', 'face', 'body', 'finnage'];
            
            foreach ($defectParts as $p) {
                $penaltyKey = "{$p}_penalty";
                if (!empty($evaluated[$penaltyKey])) {
                    $penaltyPercent = (float)str_replace('%', '', $evaluated[$penaltyKey]);
                    $categoryPoints[$p] = $categoryPoints[$p] * (1 - ($penaltyPercent / 100));
                    $defectEvals[$p] = $penaltyPercent;
                }
            }
        }

        // Build the result array
        foreach ($labels as $kat => $label) {
            $pt = $categoryPoints[$kat];
            $penaltySuffix = '';
            if (isset($defectEvals[$kat])) {
                $penaltySuffix = ' (Defect -' . $defectEvals[$kat] . '%)';
            }

            $b[$kat] = [
                'label'  => $label . $penaltySuffix,
                'bobot'  => $bobots[$kat],
                'point'  => round($pt, 2), // ★ FIX: Gunakan 2 desimal agar sumnya pas dengan total
                'parts'  => $categoryParts[$kat],
            ];
        }

        // ★ TOTAL DIHITUNG DARI DESIMAL EXACT (BUKAN DARI ROUND PER KATEGORI)
        $b['total'] = round(array_sum($categoryPoints), 2);
        return $b;
    }

    // ★ MAPPING RANK POINT TOP 10 (posisi → rank point)
    const RANK_POINT_MAPPING = [
        1 => 100, 2 => 80, 3 => 60, 4 => 45, 5 => 35,
        6 => 30,  7 => 25, 8 => 20, 9 => 15, 10 => 10,
    ];

    public static function hitungRankPoints(array $items, string $key = 'total_point'): array
    {
        // ★ STEP 1: Sort DESC by total_point (raw) → tentukan rank base dari posisi
        usort($items, function ($a, $b) use ($key) {
            $va = $a[$key] ?? 0; $vb = $b[$key] ?? 0;
            return $va === $vb ? 0 : ($va < $vb ? 1 : -1);
        });
        foreach ($items as $i => &$item) {
            $rankBase = self::RANK_POINT_MAPPING[$i + 1] ?? 0; // posisi 11+ → 0
            $bonus    = (int) ($item['total_bonus'] ?? 0);
            $item['rank_point']       = $rankBase;            // base dari posisi total_point
            $item['final_rank_point'] = $rankBase + $bonus;   // base + bonus
        }
        unset($item);

        // ★ STEP 2: RE-SORT by final_rank_point → bonus bisa ubah urutan juara
        //   Contoh: AER rank base 60 + bonus 100 = 160 → naik jadi Juara 1
        usort($items, function ($a, $b) {
            $va = $a['final_rank_point'] ?? 0; $vb = $b['final_rank_point'] ?? 0;
            return $va === $vb ? 0 : ($va < $vb ? 1 : -1);
        });

        // ★ STEP 3: Assign posisi final (post-bonus) ke setiap item
        foreach ($items as $i => &$item) {
            $item['position'] = $i + 1;
        }
        unset($item);

        return $items;
    }

        public static function getDefectDetails(array $defectData): array
    {
        $parts = ['head', 'face', 'body', 'finnage'];
        $partStatus = [
            'head'    => ['minor' => false, 'mayor' => false, 'items' => [], 'minorItems' => [], 'mayorItems' => []],
            'face'    => ['minor' => false, 'mayor' => false, 'items' => [], 'minorItems' => [], 'mayorItems' => []],
            'body'    => ['minor' => false, 'mayor' => false, 'items' => [], 'minorItems' => [], 'mayorItems' => []],
            'finnage' => ['minor' => false, 'mayor' => false, 'items' => [], 'minorItems' => [], 'mayorItems' => []],
        ];
        foreach ($parts as $p) {
            $key = "raw_{$p}_penalty";
            $defs = $defectData[$key] ?? ['0'];
            if (is_string($defs)) $defs = [$defs];
            
            foreach ($defs as $d) {
                $d = self::normalizeDefectName($d);
                if ($d && $d !== '0') {
                    $partStatus[$p]['items'][] = $d;
                    if (in_array($d, self::MINOR_DEFECTS)) { 
                        $partStatus[$p]['minor'] = true;
                        $partStatus[$p]['minorItems'][] = $d;
                    }
                    if (in_array($d, self::MAYOR_DEFECTS)) { 
                        $partStatus[$p]['mayor'] = true;
                        $partStatus[$p]['mayorItems'][] = $d;
                    }
                }
            }
        }

        // ★ FIX: 3 KOMPONEN berbeda punya minor = naik MAYOR (bukan 3 minor di satu komponen)
        $componentsWithMinor = 0;
        foreach ($parts as $p) {
            if ($partStatus[$p]['minor']) $componentsWithMinor++;
        }
        $isGlobalMayor = $componentsWithMinor >= 3;
        $minorCount = $componentsWithMinor; // ★ untuk kompatibilitas dgn baris return di bawah
        $results = [];
        $totalDeductionPercent = 0;
        
        foreach ($parts as $p) {
            $result = [
                'items' => [],
                'percent' => '',
                'percent_value' => 0,
                'is_minor' => false,
                'is_mayor' => false,
                'label' => ''
            ];
            
            if (count($partStatus[$p]['items']) > 0) {
                // Mayor jika: ada defect mayor langsung, ATAU ada minor tapi total minor global >= 3
                $isMayor = $partStatus[$p]['mayor'] || ($partStatus[$p]['minor'] && $isGlobalMayor);
                $percentValue = $isMayor ? 30 : 10;
                $percent = $isMayor ? '30%' : '10%';
                $totalDeductionPercent += $percentValue;
                
                $result['items'] = $partStatus[$p]['items'];
                $result['percent'] = $percent;
                $result['percent_value'] = $percentValue;
                $result['is_minor'] = $partStatus[$p]['minor'];
                $result['is_mayor'] = $isMayor;
                
                if ($isMayor) {
                    // Prioritaskan tampilkan Mayor items dulu
                    if (!empty($partStatus[$p]['mayorItems'])) {
                        $result['label'] = 'Mayor: ' . implode(', ', $partStatus[$p]['mayorItems']);
                    } else {
                        // Mayor karena akumulasi 3+ minor
                        $result['label'] = 'Mayor (3+ Minor Global): ' . implode(', ', $partStatus[$p]['minorItems']);
                    }
                } else {
                    $result['label'] = 'Minor: ' . implode(', ', $partStatus[$p]['minorItems']);
                }
            }
            
            $results[$p] = $result;
        }
        
        $results['total_deduction_percent'] = $totalDeductionPercent;
        $results['minor_count_global'] = $minorCount;
        $results['is_global_mayor'] = $isGlobalMayor;
        
        return $results;
    }
}