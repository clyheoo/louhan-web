<?php

namespace App\Exports\Sheets;

use App\Models\Nominasi;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class NominasiSheet implements WithTitle, WithEvents
{
    public function title(): string
    {
        return 'HASIL NOMINASI';
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

    private function statusLabel($status): string
    {
        if ($status === 'approved') return 'DISETUJUI';
        if ($status === 'rejected') return 'DITOLAK';
        if ($status === 'pending') return 'PENDING';

        return strtoupper((string) $status);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // ═══════════════════════════════════════════
                // 1. STYLE DEFINITIONS
                // ═══════════════════════════════════════════
                $styleHeader = [
                    'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '4C1D95']],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
                ];
                $styleHeaderGreen = [
                    'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '166534']],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
                ];
                $styleHeaderRed = [
                    'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '991B1B']],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
                ];
                $styleBorder = [
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDD6FE']]],
                ];
                $styleBorderGreen = [
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'BBF7D0']]],
                ];
                $styleBorderRed = [
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FECACA']]],
                ];
                $styleTitle = [
                    'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '4C1D95']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F5F3FF']],
                ];
                $styleTitleGreen = [
                    'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '166534']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F0FDF4']],
                    'alignment' => ['horizontal' => 'center'],
                ];
                $styleTitleRed = [
                    'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '991B1B']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FEF2F2']],
                    'alignment' => ['horizontal' => 'center'],
                ];
                $styleRowApproved = [
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'DCFCE7']],
                    'font' => ['color' => ['rgb' => '166534'], 'bold' => true],
                ];
                $styleRowRejected = [
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FEE2E2']],
                    'font' => ['color' => ['rgb' => '991B1B'], 'bold' => true],
                ];
                $styleRowPending = [
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FEF3C7']],
                    'font' => ['color' => ['rgb' => '92400E'], 'bold' => true],
                ];

                $styleDefectCell = [
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FDE68A']],
                    'font' => ['color' => ['rgb' => '78350F'], 'bold' => true],
                    'alignment' => ['wrapText' => true, 'vertical' => 'top'],
                ];

                // ═══════════════════════════════════════════
                // 2. DATA RETRIEVAL — SEMUA NOMINASI LANGSUNG
                // ═══════════════════════════════════════════
                $nominations = Nominasi::whereIn('status', ['pending', 'approved', 'rejected'])
                    ->with(['juri', 'ikan.peserta', 'reviewer'])
                    ->get()
                    ->sortBy(function ($n) {
                        $tank = $n->ikan?->nomor_tank;

                        if (is_numeric($tank)) {
                            return (int) $tank;
                        }

                        return 999999;
                    })
                    ->values();

                $totalPending  = $nominations->where('status', 'pending')->count();
                $totalApproved = $nominations->where('status', 'approved')->count();
                $totalRejected = $nominations->where('status', 'rejected')->count();

                $headers = [
                    'NO',
                    'TANK',
                    'NAMA PESERTA',
                    'TEAM / DETAIL',
                    'KATEGORI',
                    'KELAS',
                    'STATUS',
                    'NOMINASI OLEH',
                    'REVIEW OLEH',
                    'TANGGAL SUBMIT',
                    'TANGGAL REVIEW',
                    'DEFECT',
                    'CATATAN',
                ];

                $row = 1;

                // Judul
                $sheet->mergeCells("A{$row}:M{$row}");
                $sheet->setCellValue(
                    "A{$row}",
                    "SELURUH NOMINASI | Total: {$nominations->count()} | Pending: {$totalPending} | Diterima: {$totalApproved} | Ditolak: {$totalRejected}"
                );
                $sheet->getStyle("A{$row}:M{$row}")->applyFromArray($styleTitle);
                $sheet->getStyle("A{$row}:M{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $row += 2;

                // Header utama
                $sheet->fromArray([$headers], null, "A{$row}");
                $sheet->getStyle("A{$row}:M{$row}")->applyFromArray($styleHeader);
                $sheet->getRowDimension($row)->setRowHeight(30);
                $row++;

                $no = 1;
                $startDataRow = $row;

                foreach ($nominations as $n) {
                    $ikan    = $n->ikan;
                    $peserta = $ikan?->peserta;

                    $tankStr = $ikan ? ('Tank ' . ($ikan->nomor_tank ?? '-')) : '-';

                    $namaPeserta = $ikan?->nama_peserta
                        ?? $peserta?->nama_peserta
                        ?? '-';

                    $detailAnggota = $ikan?->detail_anggota
                        ?? $peserta?->detail_anggota
                        ?? '-';

                    $defectText = $this->formatNominasiDefect($n);

                    $sheet->setCellValue("A{$row}", $no++);
                    $sheet->setCellValue("B{$row}", $tankStr);
                    $sheet->setCellValue("C{$row}", $namaPeserta);
                    $sheet->setCellValue("D{$row}", $detailAnggota);
                    $sheet->setCellValue("E{$row}", $ikan?->kategori ?? '-');
                    $sheet->setCellValue("F{$row}", $ikan?->kelas ?? '-');
                    $sheet->setCellValue("G{$row}", $this->statusLabel($n->status));
                    $sheet->setCellValue("H{$row}", $n->juri ? $n->juri->name : '-');
                    $sheet->setCellValue("I{$row}", $n->reviewer ? $n->reviewer->name : '-');
                    $sheet->setCellValue("J{$row}", $n->created_at ? $n->created_at->format('d M Y, H:i') : '-');
                    $sheet->setCellValue("K{$row}", $n->reviewed_at ? $n->reviewed_at->format('d M Y, H:i') : '-');
                    $sheet->setCellValue("L{$row}", $defectText);
                    $sheet->setCellValue("M{$row}", $n->catatan ?: '-');

                    if ($n->status === 'approved') {
                        $sheet->getStyle("A{$row}:M{$row}")->applyFromArray($styleRowApproved);
                    } elseif ($n->status === 'rejected') {
                        $sheet->getStyle("A{$row}:M{$row}")->applyFromArray($styleRowRejected);
                    } else {
                        $sheet->getStyle("A{$row}:M{$row}")->applyFromArray($styleRowPending);
                    }

                    if ($defectText !== '-') {
                        $sheet->getStyle("L{$row}")->applyFromArray($styleDefectCell);
                    }

                    $sheet->getStyle("L{$row}:M{$row}")
                        ->getAlignment()
                        ->setWrapText(true)
                        ->setVertical(Alignment::VERTICAL_TOP);

                    $row++;
                }

                if ($row === $startDataRow) {
                    $sheet->mergeCells("A{$row}:M{$row}");
                    $sheet->setCellValue("A{$row}", "Belum ada data nominasi.");
                    $sheet->getStyle("A{$row}:M{$row}")->applyFromArray($styleBorder);
                    $row++;
                }

                if ($row > $startDataRow) {
                    $sheet->getStyle("A{$startDataRow}:M" . ($row - 1))->applyFromArray($styleBorder);
                }

                // ═══════════════════════════════════════════
                // 6. COLUMN SIZING
                // ═══════════════════════════════════════════
                $sheet->getColumnDimension('A')->setWidth(5);
                $sheet->getColumnDimension('B')->setWidth(13);
                $sheet->getColumnDimension('C')->setWidth(24);
                $sheet->getColumnDimension('D')->setWidth(22);
                $sheet->getColumnDimension('E')->setWidth(16);
                $sheet->getColumnDimension('F')->setWidth(10);
                $sheet->getColumnDimension('G')->setWidth(14);
                $sheet->getColumnDimension('H')->setWidth(18);
                $sheet->getColumnDimension('I')->setWidth(18);
                $sheet->getColumnDimension('J')->setWidth(20);
                $sheet->getColumnDimension('K')->setWidth(20);
                $sheet->getColumnDimension('L')->setWidth(55);
                $sheet->getColumnDimension('M')->setWidth(35);

                $sheet->getStyle("A:M")->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
                $sheet->getStyle("L:M")->getAlignment()->setWrapText(true);

                $sheet->freezePane('A4');
            },
        ];
    }
}