<?php

namespace App\Exports\Sheets;

use App\Support\PesertaRankingBuilder;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class IndividualRankingSheet implements FromArray, WithTitle, WithEvents
{
    public function title(): string { return 'HASIL PESERTA'; }

    public function array(): array
    {
        $all = PesertaRankingBuilder::build();

        // Kelompokkan SEMUA peserta yang dinilai per kategori + kelas
        $groups = [];
        foreach ($all as $p) {
            $groups[$p['kategori'] . ' - Kelas ' . $p['kelas']][] = $p;
        }
        ksort($groups);

        $rows = [];
        foreach ($groups as $groupName => $items) {
            // Urutkan dari peringkat tertinggi (posisi 1) ke bawah — semua ditampilkan
            usort($items, fn ($a, $b) => ($a['juara'] ?: 9999) <=> ($b['juara'] ?: 9999));

            $rows[] = ['§TITLE§' . $groupName . ' (' . count($items) . ' peserta)', '', '', '', '', ''];
            $rows[] = ['NO', 'NAMA PESERTA', 'NO TANK', 'JUARA', 'BONUS', 'RANK POINT'];

            $no = 1;
            foreach ($items as $p) {
                $juara = ($p['juara'] >= 1 && $p['juara'] <= 10) ? 'Juara ' . $p['juara'] : '-';
                $rows[] = [$no++, $p['nama'], $p['nomor_tank'], $juara, $p['bonus'], $p['rank_point']];
            }
            $rows[] = ['', '', '', '', '', ''];
        }

        if (empty($rows)) $rows[] = ['Belum ada peserta yang dinilai.', '', '', '', '', ''];

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();

                $widths = ['A'=>5,'B'=>28,'C'=>10,'D'=>12,'E'=>10,'F'=>14];
                foreach ($widths as $col => $w) $sheet->getColumnDimension($col)->setAutoSize(false)->setWidth($w);

                for ($r = 1; $r <= $lastRow; $r++) {
                    $a = (string) $sheet->getCell("A{$r}")->getValue();

                    if (str_starts_with($a, '§TITLE§')) {
                        $sheet->setCellValue("A{$r}", substr($a, strlen('§TITLE§')));
                        $sheet->mergeCells("A{$r}:F{$r}");
                        $sheet->getStyle("A{$r}:F{$r}")->applyFromArray([
                            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '6D28D9']],
                            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                        ]);
                        $sheet->getRowDimension($r)->setRowHeight(22);
                    } elseif ($a === 'NO') {
                        $sheet->getStyle("A{$r}:F{$r}")->applyFromArray([
                            'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '1E40AF']],
                            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'BFDBFE']]],
                        ]);
                    } elseif ($a !== '' && is_numeric($a)) {
                        $sheet->getStyle("A{$r}:F{$r}")->applyFromArray([
                            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDD6FE']]],
                        ]);
                        $sheet->getStyle("B{$r}")->getAlignment()->setHorizontal('left');
                        $sheet->getStyle("F{$r}")->applyFromArray([
                            'font' => ['bold' => true],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FEF9C3']],
                        ]);
                    }
                }
            },
        ];
    }
}