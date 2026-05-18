<?php

namespace App\Exports\Sheets;

use App\Models\Ikan;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class DaftarIkanSheet implements FromArray, WithTitle, WithStyles, ShouldAutoSize
{
    public function title(): string
    {
        return 'DAFTAR IKAN';
    }

    public function array(): array
    {
        $ikans = Ikan::with('peserta')
            ->orderBy('kategori')
            ->orderBy('kelas')
            ->orderBy('nomor_tank')
            ->get();

        $rows = [];
        $rows[] = ['NO', 'NAMA PESERTA', 'KATEGORI', 'KELAS', 'NO TANK', 'JENIS KEANGGOTAAN', 'DETAIL ANGGOTA (TEAM)', 'STATUS NILAI'];

        $no = 1;
        foreach ($ikans as $ikan) {
            $status = $ikan->is_locked ? 'TERKUNCI (FINAL)' : 'Belum Dikunci';
            $rows[] = [
                $no++,
                $ikan->peserta->nama_peserta ?? '—',
                strtoupper($ikan->kategori),
                $ikan->kelas ?? '—',
                $ikan->nomor_tank ?? '—',
                $ikan->peserta->jenis_keanggotaan ?? '—',
                $ikan->peserta->detail_anggota ?? '—',
                $status,
            ];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('1:1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '4C1D95']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
        ]);
        $sheet->freezePane('A2');
    }
}