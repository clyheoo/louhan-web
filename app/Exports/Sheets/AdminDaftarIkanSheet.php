<?php

namespace App\Exports\Sheets;

use App\Models\Ikan;
use App\Helpers\PointCalculator;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class AdminDaftarIkanSheet implements FromArray, WithTitle, WithStyles, ShouldAutoSize
{
    public function title(): string
    {
        return 'DAFTAR IKAN';
    }

    public function array(): array
    {
        $ikans = Ikan::where(function ($q) {
            $q->whereNotNull('nomor_tank')
              ->orWhereHas('scorings');
        })
        ->with(['peserta', 'scorings' => function ($q) {
            $q->orderBy('created_at', 'desc');
        }, 'scorings.juri', 'scorings.grandJuri', 'bonusPoints'])
        ->orderBy('nomor_tank')
        ->get();

        $rows = [[
            'NO', 'NAMA PESERTA', 'KATEGORI', 'KELAS', 'NO TANK',
            'JENIS KEANGGOTAAN', 'ASAL / TEAM', 'JML JURI',
            'TOTAL NILAI', 'POINT', 'BONUS', 'RANK POINT', 'KETERANGAN BONUS', 'JUARA', 'STATUS'
        ]];

        // ★ STEP 1: Proses hitung point & defect per ikan (SAMA PERSIS LOGIKNYA DENGAN PointRankingSheet)
        $processedIkans = [];
        foreach ($ikans as $ikan) {
            $peserta = $ikan->peserta;
            $scorings = $ikan->scorings;
            $latestScoring = $scorings->first();

            $grandJuriName = null;
            foreach ($scorings as $s) {
                if ($s->edited_by_grand_juri && $s->grandJuri) {
                    $grandJuriName = $s->grandJuri->name;
                }
            }

            $totalNilaiSemua = 0;
            $jumlahJuri = 0;
            $avgDetail = [];

            foreach ($scorings as $s) {
                if ($s->total_nilai) {
                    $totalNilaiSemua += $s->total_nilai;
                    $jumlahJuri++;
                }
                if ($s->nilai_detail && is_array($s->nilai_detail)) {
                    foreach ($s->nilai_detail as $kat => $fields) {
                        if (!is_array($fields)) continue;
                        foreach ($fields as $fid => $val) {
                            if ($fid === 'defect') continue;
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
            if ($jumlahJuri > 0) {
                foreach ($avgDetail as $kat => $fields) {
                    $finalAvgDetail[$kat] = [];
                    foreach ($fields as $fid => $d) {
                        $finalAvgDetail[$kat][$fid] = $d['count'] > 0
                            ? $d['sum'] / $d['count']
                            : 0;
                    }
                }
            }

            // ★ GABUNGKAN DEFECT DARI SEMUA JURI (UNION TANPA DUPLIKAT)
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
            
            $defectDataForCalc = [];
            foreach ($combinedDefects as $dk => $defs) {
                $defectDataForCalc[$dk] = count($defs) > 0 ? $defs : ['0'];
            }

            // ★ HITUNG POINT DENGAN DEFECT (KONSISTEN DENGAN HELPER)
            $totalPoint = PointCalculator::hitungPoint($ikan->kategori, $finalAvgDetail, $defectDataForCalc);
            $totalBonus = (int) $ikan->bonusPoints->sum('points');
            
            // ★ KETERANGAN BONUS (Ambil dari bonus_type)
            $keteranganBonus = $ikan->bonusPoints->pluck('bonus_type')->filter()->implode(', ');

            // Status
            if ($grandJuriName) {
                $status = 'GRAND JURI EDIT';
            } elseif ($latestScoring) {
                $status = 'SUDAH DINILAI';
            } else {
                $status = 'BELUM DINILAI';
            }

            $processedIkans[] = [
                'ikan_id' => $ikan->id,
                'data' => [
                    'nama_peserta'      => $ikan->nama_peserta ?? $peserta?->nama_peserta ?? '—',
                    'kategori'          => strtoupper($ikan->kategori),
                    'kelas'             => $latestScoring ? ($latestScoring->kelas ?? $ikan->kelas) : ($ikan->kelas ?? '—'),
                    'nomor_tank'        => $ikan->nomor_tank ?? '—',
                    'jenis_keanggotaan' => $ikan->jenis_keanggotaan ?? $peserta?->jenis_keanggotaan ?? '—',
                    'detail_anggota'    => $ikan->detail_anggota ?? $peserta?->detail_anggota ?? '—',
                    'jumlahJuri'        => $jumlahJuri,
                    'totalNilaiSemua'   => $totalNilaiSemua,
                    'totalPoint'        => $totalPoint,
                    'totalBonus'        => $totalBonus,
                    'keteranganBonus'   => $keteranganBonus ?: '—',
                    'status'            => $status,
                ],
                'total_point' => $totalPoint,
                'total_bonus' => $totalBonus,
            ];
        }

        // ★ STEP 2: KELOMPOKKAN PER KATEGORI & KELAS, LALU HITUNG RANK POINT & JUARA
        $groups = [];
        foreach ($processedIkans as $item) {
            $key = $item['data']['kategori'] . ' - Kelas ' . $item['data']['kelas'];
            $groups[$key][] = $item;
        }
        ksort($groups);

        $no = 1;
        foreach ($groups as $groupName => $items) {
            $rankInput = [];
            foreach ($items as $it) {
                $rankInput[] = [
                    'ikan_id'     => $it['ikan_id'],
                    'total_point' => $it['total_point'],
                    'total_bonus' => $it['total_bonus'],
                ];
            }
            
            // ★ GUNAKAN HELPER RANK POINT (SAMA PERSIS DENGAN PointRankingSheet)
            $rankedItems = PointCalculator::hitungRankPoints($rankInput, 'total_point');
            $rankLookup = [];
            foreach ($rankedItems as $ri) {
                $rankLookup[$ri['ikan_id']] = $ri;
            }

            // Urutkan berdasarkan final_rank_point tertinggi
            usort($items, fn($a, $b) => ($rankLookup[$b['ikan_id']]['final_rank_point'] ?? 0) <=> ($rankLookup[$a['ikan_id']]['final_rank_point'] ?? 0));

            foreach ($items as $item) {
                $ri = $rankLookup[$item['ikan_id']] ?? null;
                $rankPoint = $ri['final_rank_point'] ?? 0; // Base rank point + bonus
                $position = $ri['position'] ?? 0;

                // ★ JUARA (Tanpa icon medali)
                $juaraText = '-';
                if ($position === 1) $juaraText = 'JUARA 1';
                elseif ($position === 2) $juaraText = 'JUARA 2';
                elseif ($position === 3) $juaraText = 'JUARA 3';
                elseif ($position >= 4 && $position <= 10) $juaraText = 'Top 10 (#' . $position . ')';

                $d = $item['data'];
                $rows[] = [
                    $no++,
                    $d['nama_peserta'],
                    $d['kategori'],
                    $d['kelas'],
                    $d['nomor_tank'],
                    $d['jenis_keanggotaan'],
                    $d['detail_anggota'],
                    $d['jumlahJuri'],
                    $d['totalNilaiSemua'],
                    (float) $d['totalPoint'],
                    $d['totalBonus'],
                    $rankPoint,
                    $d['keteranganBonus'],
                    $juaraText,
                    $d['status'],
                ];
            }
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        $lastCol = $sheet->getHighestColumn();
        $lastRow = $sheet->getHighestRow();

        // Header
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '1E40AF']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
        ]);

        // Data rows
        $sheet->getStyle("A2:{$lastCol}{$lastRow}")->applyFromArray([
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'BFDBFE']]],
        ]);

        // Alternating rows
        for ($r = 2; $r <= $lastRow; $r++) {
            if (($r - 2) % 2 === 1) {
                $sheet->getStyle("A{$r}:{$lastCol}{$r}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F0F7FF']],
                ]);
            }
        }

        // Status column coloring (kolom O = ke-15)
        $statusCol = 'O';
        for ($r = 2; $r <= $lastRow; $r++) {
            $val = $sheet->getCell("{$statusCol}{$r}")->getValue();
            if ($val === 'GRAND JURI EDIT') {
                $sheet->getStyle("{$statusCol}{$r}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '6D28D9']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F5F3FF']],
                ]);
            } elseif ($val === 'SUDAH DINILAI') {
                $sheet->getStyle("{$statusCol}{$r}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '15803D']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'DCFCE7']],
                ]);
            } elseif ($val === 'BELUM DINILAI') {
                $sheet->getStyle("{$statusCol}{$r}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '92400E']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FEF3C7']],
                ]);
            }
        }

        // Rank Point column bold (kolom L = ke-12)
        $rpCol = 'L';
        $sheet->getStyle("{$rpCol}2:{$rpCol}{$lastRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FEF9C3']], // Warna emas muda
        ]);

        // ★ TAMBAHKAN: Juara column styling (kolom N = ke-14)
        $juaraCol = 'N';
        for ($r = 2; $r <= $lastRow; $r++) {
            $val = $sheet->getCell("{$juaraCol}{$r}")->getValue();
            if ($val === 'JUARA 1') {
                $sheet->getStyle("{$juaraCol}{$r}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '92400E']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FEF3C7']],
                ]);
            } elseif ($val === 'JUARA 2') {
                $sheet->getStyle("{$juaraCol}{$r}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '57534E']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'E7E5E4']],
                ]);
            } elseif ($val === 'JUARA 3') {
                $sheet->getStyle("{$juaraCol}{$r}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '9A3412']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FED7AA']],
                ]);
            } elseif (str_contains($val, 'Top 10')) {
                $sheet->getStyle("{$juaraCol}{$r}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '6D28D9']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F5F3FF']],
                ]);
            }
        }

        $sheet->freezePane('A2');
    }
}