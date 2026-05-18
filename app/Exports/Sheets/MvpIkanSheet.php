<?php

namespace App\Exports\Sheets;

use App\Models\Ikan;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class MvpIkanSheet implements FromArray, WithTitle, WithStyles, ShouldAutoSize
{
    public function title(): string
    {
        return 'DATA IKAN MVP';
    }

    public function array(): array
    {
        $ikans = Ikan::where('is_mvp', true)
            ->whereHas('peserta', fn($q) => $q->where('is_mvp_submitted', true))
            ->with('peserta')
            ->orderBy('peserta_id')
            ->orderBy('kategori')
            ->get()
            ->groupBy('peserta_id');

        $rows = [];
        $rows[] = ['NO', 'NAMA PESERTA', 'DETAIL ANGGOTA (TEAM)', 'TOTAL IKAN MVP', 'DAFTAR IKAN (KATEGORI - KELAS - TANK)'];

        $no = 1;
        foreach ($ikans as $pesertaId => $ikanList) {
            $peserta = $ikanList->first()->peserta;
            $daftarIkan = [];
            foreach ($ikanList as $ikan) {
                $daftarIkan[] = $ikan->kategori . ' - ' . ($ikan->kelas ?? '—') . ' - Tank ' . ($ikan->nomor_tank ?? '—');
            }

            $rows[] = [
                $no++,
                $peserta->nama_peserta ?? '—',
                $peserta->detail_anggota ?? '—',
                $ikanList->count(),
                implode("\n", $daftarIkan),
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
        $sheet->getStyle('E:E')->getAlignment()->setWrapText(true);
        $sheet->freezePane('A2');
    }
}