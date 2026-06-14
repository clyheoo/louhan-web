<?php

namespace App\Support;

use App\Models\Ikan;
use App\Helpers\PointCalculator;

class PesertaRankingBuilder
{
    private static ?array $cache = null;

    /** Mengembalikan semua ikan yang dinilai + juara(posisi), bonus, rank point. Dihitung sekali. */
    public static function build(): array
    {
        if (self::$cache !== null) return self::$cache;

        $ikans = Ikan::whereHas('scorings')
            ->with(['peserta', 'scorings', 'bonusPoints'])
            ->get();

        $processed = [];

        foreach ($ikans as $ikan) {
            $scorings = $ikan->scorings;

            // Rata-rata nilai detail dari semua juri
            $avgDetail = [];
            $jumlahJuri = 0;
            foreach ($scorings as $s) {
                if ($s->total_nilai) $jumlahJuri++;
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
            $finalAvgDetail = [];
            if ($jumlahJuri > 0) {
                foreach ($avgDetail as $kat => $fields) {
                    foreach ($fields as $fid => $d) {
                        $finalAvgDetail[$kat][$fid] = $d['count'] > 0 ? $d['sum'] / $d['count'] : 0;
                    }
                }
            }

            // Gabungkan defect dari semua juri (union)
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

            $totalPoint = PointCalculator::hitungPoint($ikan->kategori, $finalAvgDetail, $defectDataForCalc);
            $totalBonus = (int) $ikan->bonusPoints->sum('points');
            $peserta    = $ikan->peserta;

            $processed[] = [
                'ikan_id'     => $ikan->id,
                'nama'        => $ikan->nama_peserta ?? $peserta?->nama_peserta ?? '—',
                'kategori'    => strtoupper($ikan->kategori),
                'kelas'       => $ikan->kelas ?? '—',
                'nomor_tank'  => $ikan->nomor_tank ?? '—',
                'team'        => $ikan->detail_anggota ?? $peserta?->detail_anggota ?? '—',
                'jenis'       => strtolower($ikan->jenis_keanggotaan ?? $peserta?->jenis_keanggotaan ?? 'perorangan'),
                'bonus'       => $totalBonus,
                'total_point' => $totalPoint,
                'total_bonus' => $totalBonus,
            ];
        }

        // Kelompokkan per kategori+kelas, hitung posisi (juara) & rank point
        $groups = [];
        foreach ($processed as $i => $p) {
            $groups[$p['kategori'] . ' - ' . $p['kelas']][] = $i;
        }
        foreach ($groups as $idxs) {
            $rankInput = [];
            foreach ($idxs as $i) {
                $rankInput[] = [
                    'ikan_id'     => $processed[$i]['ikan_id'],
                    'total_point' => $processed[$i]['total_point'],
                    'total_bonus' => $processed[$i]['total_bonus'],
                ];
            }
            $ranked = PointCalculator::hitungRankPoints($rankInput, 'total_point');
            $lookup = [];
            foreach ($ranked as $ri) $lookup[$ri['ikan_id']] = $ri;
            foreach ($idxs as $i) {
                $ri = $lookup[$processed[$i]['ikan_id']] ?? null;
                $processed[$i]['rank_point'] = $ri['final_rank_point'] ?? 0;
                $processed[$i]['juara']      = $ri['position'] ?? 0;
            }
        }

        self::$cache = $processed;
        return $processed;
    }
}