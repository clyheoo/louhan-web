<?php

namespace App\Exports\Sheets;

use App\Models\Ikan;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class DaftarIkanSheet implements FromArray, WithTitle, WithStyles
{
    public function title(): string { return 'DAFTAR IKAN'; }

    public function array(): array
    {
        $ikans = Ikan::with('peserta')->orderBy('kategori')->orderBy('kelas')->orderBy('nomor_tank')->get();
        $rows = [['NO', 'NAMA PESERTA', 'KATEGORI', 'KELAS', 'NO TANK', 'JENIS KEANGGOTAAN', 'DETAIL ANGGOTA (TEAM)', 'STATUS NILAI']];
        $no = 1;
        foreach ($ikans as $ikan) {
            $p = $ikan->peserta;
            $rows[] = [
                $no++,
                $ikan->nama_peserta      ?? $p?->nama_peserta      ?? '—',
                strtoupper($ikan->kategori),
                $ikan->kelas ?? '—',
                $ikan->nomor_tank ?? '—',
                $ikan->jenis_keanggotaan ?? $p?->jenis_keanggotaan ?? '—',
                $ikan->detail_anggota    ?? $p?->detail_anggota    ?? '—',
                $ikan->is_locked ? 'TERKUNCI (FINAL)' : 'Belum Dikunci',
            ];
        }
        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        $lastCol = $sheet->getHighestColumn();
        $lastRow = $sheet->getHighestRow();

        // Lebar kolom manual (pengganti ShouldAutoSize yang lambat)
        $widths = ['A'=>5,'B'=>24,'C'=>14,'D'=>8,'E'=>9,'F'=>20,'G'=>26,'H'=>18];
        foreach ($widths as $col => $w) {
            $sheet->getColumnDimension($col)->setAutoSize(false)->setWidth($w);
        }

        // Header
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '4C1D95']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
        ]);

        // Semua data: center
        $sheet->getStyle("A2:{$lastCol}{$lastRow}")->applyFromArray([
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDD6FE']]],
        ]);

        $sheet->freezePane('A2');
    }
}