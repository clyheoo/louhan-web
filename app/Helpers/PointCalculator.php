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

    public static function hitungPoint(string $kategori, array $nd): float
    {
        $cfg = ScoringPointConfig::where('kategori', $kategori)->first();
        if (!$cfg || empty($nd)) return 0;

        $tp = 0;

        // Overall
        $v = (float)($nd['overall']['impression'] ?? 0);
        $tp += ($v / self::MAX['impression']) * (float)$cfg->overall_bobot;

        // Head
        $v = (float)($nd['head']['size'] ?? 0);
        $tp += ($v / self::MAX['size']) * (float)$cfg->head_bobot;
        $v = (float)($nd['head']['bentuk'] ?? 0);
        $tp += ($v / self::MAX['bentuk_head']) * (float)$cfg->head_bobot;

        // Face
        foreach (['pipi','mata','bibir','kondisi'] as $k) {
            $v = (float)($nd['face'][$k] ?? 0);
            $tp += ($v / self::MAX[$k]) * (float)$cfg->face_bobot;
        }

        // Body
        $v = (float)($nd['body']['bentuk'] ?? 0);
        $tp += ($v / self::MAX['bentuk_body']) * (float)$cfg->body_bobot;
        $v = (float)($nd['body']['proporsi'] ?? 0);
        $tp += ($v / self::MAX['proporsi']) * (float)$cfg->body_bobot;
        $v = (float)($nd['body']['pangkal'] ?? 0);
        $tp += ($v / self::MAX['pangkal']) * (float)$cfg->body_bobot;

        // Marking
        $v = (float)($nd['marking']['fullness'] ?? 0);
        $tp += ($v / self::MAX['fullness_m']) * (float)$cfg->marking_bobot;
        $v = (float)($nd['marking']['contrast'] ?? 0);
        $tp += ($v / self::MAX['contrast']) * (float)$cfg->marking_bobot;
        $v = (float)($nd['marking']['bentuk'] ?? 0);
        $tp += ($v / self::MAX['bentuk_m']) * (float)$cfg->marking_bobot;

        // Pearl
        $v = (float)($nd['pearl']['shining'] ?? 0);
        $tp += ($v / self::MAX['shining']) * (float)$cfg->pearl_bobot;
        $v = (float)($nd['pearl']['fullness'] ?? 0);
        $tp += ($v / self::MAX['fullness_p']) * (float)$cfg->pearl_bobot;
        $v = (float)($nd['pearl']['bentuk'] ?? 0);
        $tp += ($v / self::MAX['bentuk_p']) * (float)$cfg->pearl_bobot;

        // Color
        $v = (float)($nd['color']['komposisi'] ?? 0);
        $tp += ($v / self::MAX['komposisi']) * (float)$cfg->color_bobot;
        $v = (float)($nd['color']['kecerahan'] ?? 0);
        $tp += ($v / self::MAX['kecerahan_c']) * (float)$cfg->color_bobot;
        $v = (float)($nd['color']['fullness'] ?? 0);
        $tp += ($v / self::MAX['fullness_c']) * (float)$cfg->color_bobot;

        // Finnage
        $v = (float)($nd['finnage']['bentuk'] ?? 0);
        $tp += ($v / self::MAX['bentuk_fn']) * (float)$cfg->finnage_bobot;
        $v = (float)($nd['finnage']['kecerahan'] ?? 0);
        $tp += ($v / self::MAX['kecerahan_fn']) * (float)$cfg->finnage_bobot;

        return round($tp, 5);
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
                $parts[] = "({$v}/60)×{$bobots['head']}=" . round($p1, 3);
                $parts[] = "({$v2}/40)×{$bobots['head']}=" . round($p2, 3);
            } elseif ($kat === 'face') {
                foreach (['pipi','mata','bibir','kondisi'] as $k) {
                    $v = (float)($nd['face'][$k] ?? 0);
                    $p = ($v / self::MAX[$k]) * $bobots['face'];
                    $parts[] = "({$v}/25)=" . round($p, 3);
                    $pt += $p;
                }
            } elseif ($kat === 'body') {
                $pairs = [['bentuk','bentuk_body',50],['proporsi','proporsi',40],['pangkal','pangkal',10]];
                foreach ($pairs as $pr) {
                    $v = (float)($nd['body'][$pr[0]] ?? 0);
                    $p = ($v / self::MAX[$pr[1]]) * $bobots['body'];
                    $parts[] = "({$v}/{$pr[2]})=" . round($p, 3);
                    $pt += $p;
                }
            } elseif ($kat === 'marking') {
                $pairs = [['fullness','fullness_m',40],['contrast','contrast',40],['bentuk','bentuk_m',20]];
                foreach ($pairs as $pr) {
                    $v = (float)($nd['marking'][$pr[0]] ?? 0);
                    $p = ($v / self::MAX[$pr[1]]) * $bobots['marking'];
                    $parts[] = "({$v}/{$pr[2]})=" . round($p, 3);
                    $pt += $p;
                }
            } elseif ($kat === 'pearl') {
                $pairs = [['shining','shining',45],['fullness','fullness_p',35],['bentuk','bentuk_p',20]];
                foreach ($pairs as $pr) {
                    $v = (float)($nd['pearl'][$pr[0]] ?? 0);
                    $p = ($v / self::MAX[$pr[1]]) * $bobots['pearl'];
                    $parts[] = "({$v}/{$pr[2]})=" . round($p, 3);
                    $pt += $p;
                }
            } elseif ($kat === 'color') {
                $pairs = [['komposisi','komposisi',45],['kecerahan','kecerahan_c',35],['fullness','fullness_c',20]];
                foreach ($pairs as $pr) {
                    $v = (float)($nd['color'][$pr[0]] ?? 0);
                    $p = ($v / self::MAX[$pr[1]]) * $bobots['color'];
                    $parts[] = "({$v}/{$pr[2]})=" . round($p, 3);
                    $pt += $p;
                }
            } elseif ($kat === 'finnage') {
                $pairs = [['bentuk','bentuk_fn',75],['kecerahan','kecerahan_fn',25]];
                foreach ($pairs as $pr) {
                    $v = (float)($nd['finnage'][$pr[0]] ?? 0);
                    $p = ($v / self::MAX[$pr[1]]) * $bobots['finnage'];
                    $parts[] = "({$v}/{$pr[2]})=" . round($p, 3);
                    $pt += $p;
                }
            }

            $b[$kat] = [
                'label'  => $label,
                'bobot'  => $bobots[$kat],
                'point'  => round($pt, 5),
                'parts'  => $parts,
            ];
        }

        $b['total'] = round(array_sum(array_column($b, 'point')), 5);
        return $b;
    }

    public static function hitungRankPoints(array $items, string $key = 'total_point'): array
    {
        usort($items, function ($a, $b) use ($key) {
            return ($b[$key] ?? 0) <=> ($a[$key] ?? 0);
        });
        $total = count($items);
        $start = max(0, 100 - $total + 1);
        foreach ($items as $i => &$item) {
            $item['rank_point'] = $start + $i;
        }
        return $items;
    }
}