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
use PhpOffice\PhpSpreadsheet\Style\Border;

class MvpIkanSheet implements FromArray, WithTitle, WithStyles, ShouldAutoSize
{
    public function title(): string { return 'DATA IKAN MVP'; }

    public function array(): array
    {
        $ikans = Ikan::where('is_mvp', true)->whereHas('peserta', fn($q) => $q->where('is_mvp_submitted', true))->with('peserta')->orderBy('peserta_id')->orderBy('kategori')->get()->groupBy('peserta_id');
        $rows = [['NO', 'NAMA PESERTA', 'DETAIL ANGGOTA (TEAM)', 'TOTAL IKAN MVP', 'DAFTAR IKAN (KATEGORI - KELAS - TANK)']];
        $no = 1;
        foreach ($ikans as $ikanList) {
            $peserta = $ikanList->first()->peserta;
            $daftar = [];
            foreach ($ikanList as $ikan) $daftar[] = $ikan->kategori . ' - ' . ($ikan->kelas ?? '—') . ' - Tank ' . ($ikan->nomor_tank ?? '—');
            $rows[] = [$no++, $peserta->nama_peserta ?? '—', $peserta->detail_anggota ?? '—', $ikanList->count(), implode("\n", $daftar)];
        }
        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        $lastCol = $sheet->getHighestColumn();
        $lastRow = $sheet->getHighestRow();

        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '4C1D95']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
        ]);

        // Semua data: center, E wrap text
        $sheet->getStyle("A2:{$lastCol}{$lastRow}")->applyFromArray([
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDD6FE']]],
        ]);
        $sheet->getStyle("E2:E{$lastRow}")->getAlignment()->setWrapText(true);

        $sheet->freezePane('A2');
    }
}