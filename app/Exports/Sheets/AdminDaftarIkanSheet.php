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
            'TOTAL NILAI', 'POINT', 'BONUS', 'FINAL POINT', 'STATUS'
        ]];

        $no = 1;
        foreach ($ikans as $ikan) {
            $peserta = $ikan->peserta;
            $scorings = $ikan->scorings;
            $latestScoring = $scorings->first();

            // Track grand juri
            $grandJuriName = null;
            foreach ($scorings as $s) {
                if ($s->edited_by_grand_juri && $s->grandJuri) {
                    $grandJuriName = $s->grandJuri->name;
                }
            }

            // Hitung total dari semua juri
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

            $totalPoint = PointCalculator::hitungPoint($ikan->kategori, $finalAvgDetail);
            $totalBonus = (int) $ikan->bonusPoints->sum('points');
            $finalPoint = $totalPoint + $totalBonus;

            // Status
            if ($grandJuriName) {
                $status = 'GRAND JURI EDIT';
            } elseif ($latestScoring) {
                $status = 'SUDAH DINILAI';
            } else {
                $status = 'BELUM DINILAI';
            }

            $rows[] = [
                $no++,
                $peserta->nama_peserta ?? '—',
                strtoupper($ikan->kategori),
                $latestScoring ? ($latestScoring->kelas ?? $ikan->kelas) : ($ikan->kelas ?? '—'),
                $ikan->nomor_tank ?? '—',
                $peserta->jenis_keanggotaan ?? '—',
                $peserta->detail_anggota ?? '—',
                $jumlahJuri,
                $totalNilaiSemua,
                (float) $totalPoint,
                $totalBonus,
                (float) $finalPoint,
                $status,
            ];
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

        // Status column coloring (kolom M = ke-13)
        $statusCol = 'M';
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

        // Final Point column bold (kolom L = ke-12)
        $fpCol = 'L';
        $sheet->getStyle("{$fpCol}2:{$fpCol}{$lastRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 11],
        ]);

        $sheet->freezePane('A2');
    }
}