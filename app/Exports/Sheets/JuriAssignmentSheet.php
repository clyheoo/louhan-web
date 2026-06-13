<?php

namespace App\Exports\Sheets;

use App\Models\User;
use App\Models\JuriAssignment;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class JuriAssignmentSheet implements WithTitle, WithEvents
{
    public function title(): string
    {
        return 'PENUGASAN JURI';
    }

    private function formatAssignments($rows): string
    {
        $byKat = [];
        foreach ($rows as $r) {
            $byKat[$r->kategori][] = $r->kelas; // kelas bisa null
        }

        if (empty($byKat)) {
            return '-';
        }

        ksort($byKat);
        $out = [];

        foreach ($byKat as $kat => $kelasList) {
            $hasSemua    = in_array(null, $kelasList, true) || in_array('', $kelasList, true);
            $kelasBersih = array_values(array_filter($kelasList, fn ($k) => $k !== null && $k !== ''));
            sort($kelasBersih);

            if ($hasSemua || empty($kelasBersih)) {
                $out[] = $kat . ': Semua Kelas';
            } else {
                $out[] = $kat . ': ' . implode(', ', $kelasBersih);
            }
        }

        return implode(' | ', $out);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $styleTitle = [
                    'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '4C1D95']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F5F3FF']],
                ];
                $styleHeader = [
                    'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '4C1D95']],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
                ];
                $styleBorder = [
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDD6FE']]],
                ];
                $styleAssigned = [
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'DCFCE7']],
                    'font' => ['color' => ['rgb' => '166534'], 'bold' => true],
                ];
                $styleUnassigned = [
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FEE2E2']],
                    'font' => ['color' => ['rgb' => '991B1B'], 'bold' => true],
                ];

                $juris       = User::where('role', 'juri')->orderBy('name')->get();
                $assignments = JuriAssignment::all()->groupBy('juri_id');

                $totalAssigned = 0;
                foreach ($juris as $j) {
                    if (($assignments->get($j->id, collect()))->count() > 0) {
                        $totalAssigned++;
                    }
                }
                $totalBelum = $juris->count() - $totalAssigned;

                $headers = ['NO', 'NAMA JURI', 'EMAIL', 'STATUS', 'JUMLAH KOMBINASI', 'PENUGASAN (KATEGORI : KELAS)'];

                $row = 1;
                $sheet->mergeCells("A{$row}:F{$row}");
                $sheet->setCellValue(
                    "A{$row}",
                    "PENUGASAN JURI | Total Juri: {$juris->count()} | Sudah Ditugaskan: {$totalAssigned} | Belum: {$totalBelum}"
                );
                $sheet->getStyle("A{$row}:F{$row}")->applyFromArray($styleTitle);
                $sheet->getStyle("A{$row}:F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $row += 2;

                $sheet->fromArray([$headers], null, "A{$row}");
                $sheet->getStyle("A{$row}:F{$row}")->applyFromArray($styleHeader);
                $sheet->getRowDimension($row)->setRowHeight(28);
                $row++;

                $no = 1;
                $startDataRow = $row;

                foreach ($juris as $j) {
                    $rows  = $assignments->get($j->id, collect());
                    $count = $rows->count();
                    $teks  = $this->formatAssignments($rows);

                    $sheet->setCellValue("A{$row}", $no++);
                    $sheet->setCellValue("B{$row}", $j->name);
                    $sheet->setCellValue("C{$row}", $j->email);
                    $sheet->setCellValue("D{$row}", $count > 0 ? 'DITUGASKAN' : 'BELUM DITUGASKAN');
                    $sheet->setCellValue("E{$row}", $count);
                    $sheet->setCellValue("F{$row}", $teks);

                    $sheet->getStyle("D{$row}")->applyFromArray($count > 0 ? $styleAssigned : $styleUnassigned);
                    $sheet->getStyle("F{$row}")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);

                    $row++;
                }

                if ($row === $startDataRow) {
                    $sheet->mergeCells("A{$row}:F{$row}");
                    $sheet->setCellValue("A{$row}", "Belum ada user dengan role Juri.");
                    $row++;
                }

                if ($row > $startDataRow) {
                    $sheet->getStyle("A{$startDataRow}:F" . ($row - 1))->applyFromArray($styleBorder);
                }

                $sheet->getColumnDimension('A')->setWidth(5);
                $sheet->getColumnDimension('B')->setWidth(24);
                $sheet->getColumnDimension('C')->setWidth(26);
                $sheet->getColumnDimension('D')->setWidth(18);
                $sheet->getColumnDimension('E')->setWidth(16);
                $sheet->getColumnDimension('F')->setWidth(60);

                $sheet->getStyle("A:F")->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
                $sheet->freezePane('A4');
            },
        ];
    }
}