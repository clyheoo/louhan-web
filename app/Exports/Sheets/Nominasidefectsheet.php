<?php

namespace App\Exports\Sheets;

use App\Models\Nominasi;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class NominasiDefectSheet implements WithTitle, WithEvents
{
    /**
     * Ganti ke ['pending','approved','rejected'] bila ingin semua status,
     * atau ['approved'] untuk hanya nominasi final (default).
     */
    private array $statusFilter = ['approved'];

    public function title(): string
    {
        return 'NOMINASI DEFECT';
    }

    private function normalizeDefectArray($value): array
    {
        if (is_string($value)) {
            $value = [$value];
        }
        if (!is_array($value)) {
            return [];
        }
        return collect($value)
            ->map(function ($v) {
                $v = trim((string) $v);
                $v = preg_replace('/\s+Sempurna/u', '', $v) ?? $v;
                return $v;
            })
            ->filter(fn ($v) => $v !== '' && $v !== '0')
            ->unique()
            ->values()
            ->toArray();
    }

    private function formatNominasiDefect(Nominasi $n): string
    {
        $parts = [
            'HEAD'    => $this->normalizeDefectArray($n->raw_head_penalty ?? ['0']),
            'FACE'    => $this->normalizeDefectArray($n->raw_face_penalty ?? ['0']),
            'BODY'    => $this->normalizeDefectArray($n->raw_body_penalty ?? ['0']),
            'FINNAGE' => $this->normalizeDefectArray($n->raw_finnage_penalty ?? ['0']),
        ];
        $out = [];
        foreach ($parts as $label => $items) {
            if (!empty($items)) {
                $out[] = $label . ': ' . implode(', ', $items);
            }
        }
        return empty($out) ? '-' : implode(' | ', $out);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $styleTitle = [
                    'font'      => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'B45309']],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                ];
                $styleHeader = [
                    'font'      => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '78350F']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FDE68A']],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FCD34D']]],
                ];
                $styleBorder = [
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FDE68A']]],
                ];
                $styleDefectCell = [
                    'font'      => ['color' => ['rgb' => '92400E'], 'bold' => true],
                    'alignment' => ['wrapText' => true, 'vertical' => 'top'],
                ];

                $rows = Nominasi::whereIn('status', $this->statusFilter)
                    ->with('ikan.peserta')
                    ->get()
                    ->filter(fn ($n) => $n->ikan !== null)
                    ->map(fn ($n) => ['ikan' => $n->ikan, 'defect' => $this->formatNominasiDefect($n)])
                    ->filter(fn ($x) => $x['defect'] !== '-')
                    ->sortBy(function ($x) {
                        $t = $x['ikan']->nomor_tank;
                        return is_numeric($t) ? (int) $t : 999999;
                    })
                    ->values();

                $sheet->mergeCells('A1:C1');
                $sheet->setCellValue('A1', 'NOMINASI TERKENA DEFECT  |  Total: ' . $rows->count());
                $sheet->getStyle('A1:C1')->applyFromArray($styleTitle);
                $sheet->getRowDimension(1)->setRowHeight(22);

                $headerRow = 3;
                $sheet->fromArray([['NO TANK', 'KATEGORI', 'DEFECT']], null, "A{$headerRow}");
                $sheet->getStyle("A{$headerRow}:C{$headerRow}")->applyFromArray($styleHeader);
                $sheet->getRowDimension($headerRow)->setRowHeight(24);

                $row = $headerRow + 1;
                $start = $row;
                foreach ($rows as $x) {
                    $ik = $x['ikan'];
                    $sheet->setCellValue("A{$row}", $ik->nomor_tank ?? '-');
                    $sheet->setCellValue("B{$row}", $ik->kategori ?? '-');
                    $sheet->setCellValue("C{$row}", $x['defect']);
                    $sheet->getStyle("C{$row}")->applyFromArray($styleDefectCell);
                    $row++;
                }

                if ($row === $start) {
                    $sheet->mergeCells("A{$row}:C{$row}");
                    $sheet->setCellValue("A{$row}", 'Tidak ada nominasi yang terkena defect.');
                    $sheet->getStyle("A{$row}:C{$row}")->applyFromArray($styleBorder);
                    $row++;
                }

                if ($row > $start) {
                    $sheet->getStyle("A{$start}:C" . ($row - 1))->applyFromArray($styleBorder);
                }

                $sheet->getColumnDimension('A')->setWidth(12);
                $sheet->getColumnDimension('B')->setWidth(20);
                $sheet->getColumnDimension('C')->setWidth(70);

                $sheet->getStyle('A:B')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_TOP);
                $sheet->getStyle('C:C')->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
                $sheet->freezePane('A4');
            },
        ];
    }
}