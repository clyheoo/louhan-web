<?php

namespace App\Helpers;

use App\Models\ScoringPointConfig;

class PointCalculator
{
    const MAX = [
        'impression'     => 100,
        'size'           => 60,
        'bentuk_head'    => 40,
        'pipi'           => 25,
        'mata'           => 25,
        'bibir'          => 25,
        'kondisi'        => 25,
        'bentuk_body'    => 50,
        'proporsi'        => 40,
        'pangkal'        => 10,
        'fullness_m'     => 40,
        'contrast'       => 40,
        'bentuk_m'       => 20,
        'shining'        => 45,
        'fullness_p'     => 35,
        'bentuk_p'       => 20,
        'komposisi'      => 45,
        'kecerahan_c'    => 35,
        'fullness_c'     => 20,
        'bentuk_fn'      => 75,
        'kecerahan_fn'   => 25,
    ];

    // ★ DAFTAR DEFECT MINOR
    const MINOR_DEFECTS = [
        'Kutil', 
        'Bibir Miring', 
        'Katarak', 
        'Abses / Luka', 
        'Fintail Bleaching', 
        'Pangkal Ekor Naik/Trn', 
        'Dayung Tdk Seimbang'
    ];

    // ★ DAFTAR DEFECT MAYOR
    const MAYOR_DEFECTS = [
        'Bagian Bibir Hilang', 
        'Mulut Terbuka Terus', 
        'Muka Miring', 
        'Pangkal Bengkok/Patah', 
        'Fin/Tulang Hilang 1 Ruas'
    ];

    public static function hitungPoint(string $kategori, array $nd, array $defectData = []): float
    {
        $cfg = ScoringPointConfig::where('kategori', $kategori)->first();
        if (!$cfg || empty($nd)) return 0;

        // ★ HITUNG POINT PER KATEGORI DULU (SEBELUM DEFECT)
        $categoryPoints = [];

        // Overall (tidak ada defect)
        $v = (float)($nd['overall']['impression'] ?? 0);
        $categoryPoints['overall'] = ($v / self::MAX['impression']) * (float)$cfg->overall_bobot;

        // Head
        $headPt = 0;
        $v = (float)($nd['head']['size'] ?? 0);
        $headPt += ($v / self::MAX['size']) * (float)$cfg->head_bobot;
        $v = (float)($nd['head']['bentuk'] ?? 0);
        $headPt += ($v / self::MAX['bentuk_head']) * (float)$cfg->head_bobot;
        $categoryPoints['head'] = $headPt;

        // Face
        if (isset($nd['face']['face'])) {
            $v = (float)$nd['face']['face'];
            $categoryPoints['face'] = ($v / 90) * (float)$cfg->face_bobot;
        } else {
            $facePt = 0;
            foreach (['pipi','mata','bibir','kondisi'] as $k) {
                $v = (float)($nd['face'][$k] ?? 0);
                $facePt += ($v / self::MAX[$k]) * (float)$cfg->face_bobot;
            }
            $categoryPoints['face'] = $facePt;
        }

        // Body
        $bodyPt = 0;
        $v = (float)($nd['body']['bentuk'] ?? 0);
        $bodyPt += ($v / self::MAX['bentuk_body']) * (float)$cfg->body_bobot;
        $v = (float)($nd['body']['proporsi'] ?? 0);
        $bodyPt += ($v / self::MAX['proporsi']) * (float)$cfg->body_bobot;
        $v = (float)($nd['body']['pangkal'] ?? 0);
        $bodyPt += ($v / self::MAX['pangkal']) * (float)$cfg->body_bobot;
        $categoryPoints['body'] = $bodyPt;

        // Marking
        $markingPt = 0;
        $v = (float)($nd['marking']['fullness'] ?? 0);
        $markingPt += ($v / self::MAX['fullness_m']) * (float)$cfg->marking_bobot;
        $v = (float)($nd['marking']['contrast'] ?? 0);
        $markingPt += ($v / self::MAX['contrast']) * (float)$cfg->marking_bobot;
        $v = (float)($nd['marking']['bentuk'] ?? 0);
        $markingPt += ($v / self::MAX['bentuk_m']) * (float)$cfg->marking_bobot;
        $categoryPoints['marking'] = $markingPt;

        // Pearl
        $pearlPt = 0;
        $v = (float)($nd['pearl']['shining'] ?? 0);
        $pearlPt += ($v / self::MAX['shining']) * (float)$cfg->pearl_bobot;
        $v = (float)($nd['pearl']['fullness'] ?? 0);
        $pearlPt += ($v / self::MAX['fullness_p']) * (float)$cfg->pearl_bobot;
        $v = (float)($nd['pearl']['bentuk'] ?? 0);
        $pearlPt += ($v / self::MAX['bentuk_p']) * (float)$cfg->pearl_bobot;
        $categoryPoints['pearl'] = $pearlPt;

        // Color
        $colorPt = 0;
        $v = (float)($nd['color']['komposisi'] ?? 0);
        $colorPt += ($v / self::MAX['komposisi']) * (float)$cfg->color_bobot;
        $v = (float)($nd['color']['kecerahan'] ?? 0);
        $colorPt += ($v / self::MAX['kecerahan_c']) * (float)$cfg->color_bobot;
        $v = (float)($nd['color']['fullness'] ?? 0);
        $colorPt += ($v / self::MAX['fullness_c']) * (float)$cfg->color_bobot;
        $categoryPoints['color'] = $colorPt;

        // Finnage
        $finnagePt = 0;
        $v = (float)($nd['finnage']['bentuk'] ?? 0);
        $finnagePt += ($v / self::MAX['bentuk_fn']) * (float)$cfg->finnage_bobot;
        $v = (float)($nd['finnage']['kecerahan'] ?? 0);
        $finnagePt += ($v / self::MAX['kecerahan_fn']) * (float)$cfg->finnage_bobot;
        $categoryPoints['finnage'] = $finnagePt;

        // ★ TERAPKAN DEFECT PENALTY (PENGURANGAN POINT)
        if (!empty($defectData)) {
            $evaluated = self::evaluateDefects($defectData);
            $defectParts = ['head', 'face', 'body', 'finnage'];
            
            foreach ($defectParts as $p) {
                $penaltyKey = "{$p}_penalty";
                if (!empty($evaluated[$penaltyKey])) {
                    // Ekstrak persen dari string "10%" atau "30%"
                    $penaltyPercent = (float)str_replace('%', '', $evaluated[$penaltyKey]);
                    
                    // Kurangi point kategori tersebut
                    // MINOR 10% → point × 0.90
                    // MAYOR 30% → point × 0.70
                    $categoryPoints[$p] = $categoryPoints[$p] * (1 - ($penaltyPercent / 100));
                }
            }
        }

        // ★ TOTAL POINT AKHIR (SETELAH DEFECT)
        $tp = array_sum($categoryPoints);
        
        return round($tp, 2);
    }

    // ★ FUNGSI BARU: Evaluasi Defect (Sama dengan kode React pertama)
    public static function evaluateDefects(array $defectData): array
    {
        $parts = ['head', 'face', 'body', 'finnage'];
        
        $partStatus = [
            'head'    => ['minor' => false, 'mayor' => false, 'items' => []],
            'face'    => ['minor' => false, 'mayor' => false, 'items' => []],
            'body'    => ['minor' => false, 'mayor' => false, 'items' => []],
            'finnage' => ['minor' => false, 'mayor' => false, 'items' => []],
        ];
        
        $minorCount = 0;
        
        foreach ($parts as $p) {
            $key = "raw_{$p}_penalty";
            $defs = $defectData[$key] ?? ['0'];
            
            if (is_string($defs)) {
                $defs = [$defs];
            }
            
            foreach ($defs as $d) {
                if ($d && $d !== '0') {
                    $partStatus[$p]['items'][] = $d;
                    if (in_array($d, self::MINOR_DEFECTS)) {
                        $minorCount++;
                        $partStatus[$p]['minor'] = true;
                    }
                    if (in_array($d, self::MAYOR_DEFECTS)) {
                        $partStatus[$p]['mayor'] = true;
                    }
                }
            }
        }
        
        // ★ LOGIKA GLOBAL MAYOR: 3+ minor di bagian berbeda = semua minor jadi mayor
        $isGlobalMayor = $minorCount >= 3;
        $results = [];
        $globalNotes = [];
        
        foreach ($parts as $p) {
            if (count($partStatus[$p]['items']) > 0) {
                $isMayor = $partStatus[$p]['mayor'] || ($partStatus[$p]['minor'] && $isGlobalMayor);
                $score = $isMayor ? '30%' : '10%';
                
                $results["{$p}_penalty"] = $score;
                $globalNotes[] = strtoupper($p) . ': ' . implode(', ', $partStatus[$p]['items']);
            } else {
                $results["{$p}_penalty"] = '';
            }
        }
        
        $results['keterangan'] = implode(' | ', $globalNotes);
        
        return $results;
    }

    public static function hitungBreakdown(string $kategori, array $nd): array
    {
        $cfg = ScoringPointConfig::where('kategori', $kategori)->first();
        if (!$cfg || empty($nd)) return [];

        $b = [];
        $labels = [
            'overall' => 'Overall Impression',
            'head'    => 'Head',
            'face'    => 'Face',
            'body'    => 'Body Shape',
            'marking' => 'Marking',
            'pearl'   => 'Pearl',
            'color'   => 'Color',
            'finnage' => 'Finnage',
        ];
        $bobots = [
            'overall' => (float)$cfg->overall_bobot,
            'head'    => (float)$cfg->head_bobot,
            'face'    => (float)$cfg->face_bobot,
            'body'    => (float)$cfg->body_bobot,
            'marking' => (float)$cfg->marking_bobot,
            'pearl'   => (float)$cfg->pearl_bobot,
            'color'   => (float)$cfg->color_bobot,
            'finnage' => (float)$cfg->finnage_bobot,
        ];

        foreach ($labels as $kat => $label) {
            $pt = 0;
            $parts = [];

            if ($kat === 'overall') {
                $v = (float)($nd['overall']['impression'] ?? 0);
                $pt = ($v / self::MAX['impression']) * $bobots['overall'];
                $parts[] = "({$v}/100)×{$bobots['overall']}";
            } elseif ($kat === 'head') {
                $v = (float)($nd['head']['size'] ?? 0);
                $p1 = ($v / self::MAX['size']) * $bobots['head'];
                $v2 = (float)($nd['head']['bentuk'] ?? 0);
                $p2 = ($v2 / self::MAX['bentuk_head']) * $bobots['head'];
                $pt = $p1 + $p2;
                $parts[] = "({$v}/60)×{$bobots['head']}=" . round($p1);
                $parts[] = "({$v2}/40)×{$bobots['head']}=" . round($p2);
            } elseif ($kat === 'face') {
                if (isset($nd['face']['face'])) {
                    // Format baru
                    $v = (float)$nd['face']['face'];
                    $p = ($v / 90) * $bobots['face'];
                    $parts[] = "({$v}/90)=" . round($p);
                    $pt = $p;
                } else {
                    // Format lama
                    foreach (['pipi','mata','bibir','kondisi'] as $k) {
                        $v = (float)($nd['face'][$k] ?? 0);
                        $p = ($v / self::MAX[$k]) * $bobots['face'];
                        $parts[] = "({$v}/25)=" . round($p);
                        $pt += $p;
                    }
                }
            } elseif ($kat === 'body') {
                $pairs = [['bentuk','bentuk_body',50],['proporsi','proporsi',40],['pangkal','pangkal',10]];
                foreach ($pairs as $pr) {
                    $v = (float)($nd['body'][$pr[0]] ?? 0);
                    $p = ($v / self::MAX[$pr[1]]) * $bobots['body'];
                    $parts[] = "({$v}/{$pr[2]})=" . round($p);
                    $pt += $p;
                }
            } elseif ($kat === 'marking') {
                $pairs = [['fullness','fullness_m',40],['contrast','contrast',40],['bentuk','bentuk_m',20]];
                foreach ($pairs as $pr) {
                    $v = (float)($nd['marking'][$pr[0]] ?? 0);
                    $p = ($v / self::MAX[$pr[1]]) * $bobots['marking'];
                    $parts[] = "({$v}/{$pr[2]})=" . round($p);
                    $pt += $p;
                }
            } elseif ($kat === 'pearl') {
                $pairs = [['shining','shining',45],['fullness','fullness_p',35],['bentuk','bentuk_p',20]];
                foreach ($pairs as $pr) {
                    $v = (float)($nd['pearl'][$pr[0]] ?? 0);
                    $p = ($v / self::MAX[$pr[1]]) * $bobots['pearl'];
                    $parts[] = "({$v}/{$pr[2]})=" . round($p);
                    $pt += $p;
                }
            } elseif ($kat === 'color') {
                $pairs = [['komposisi','komposisi',45],['kecerahan','kecerahan_c',35],['fullness','fullness_c',20]];
                foreach ($pairs as $pr) {
                    $v = (float)($nd['color'][$pr[0]] ?? 0);
                    $p = ($v / self::MAX[$pr[1]]) * $bobots['color'];
                    $parts[] = "({$v}/{$pr[2]})=" . round($p);
                    $pt += $p;
                }
            } elseif ($kat === 'finnage') {
                $pairs = [['bentuk','bentuk_fn',75],['kecerahan','kecerahan_fn',25]];
                foreach ($pairs as $pr) {
                    $v = (float)($nd['finnage'][$pr[0]] ?? 0);
                    $p = ($v / self::MAX[$pr[1]]) * $bobots['finnage'];
                    $parts[] = "({$v}/{$pr[2]})=" . round($p);
                    $pt += $p;
                }
            }

            $b[$kat] = [
                'label'  => $label,
                'bobot'  => $bobots[$kat],
                'point'  => round($pt),
                'parts'  => $parts,
            ];
        }

        $b['total'] = round(array_sum(array_column($b, 'point')));
        return $b;
    }

    public static function hitungRankPoints(array $items, string $key = 'total_point'): array
    {
        usort($items, function ($a, $b) use ($key) {
            $va = $a[$key] ?? 0;
            $vb = $b[$key] ?? 0;
            if ($va === $vb) return 0;
            return $va < $vb ? 1 : -1;
        });

        foreach ($items as $i => &$item) {
            $item['rank_point'] = max(1, 100 - $i);
        }

        return $items;
    }
}