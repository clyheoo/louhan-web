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

                // ═══════════════════════════════════════════
                // 2. DATA RETRIEVAL
                // ═══════════════════════════════════════════
                $nominations = Nominasi::whereIn('status', ['approved', 'rejected'])
                    ->with(['juri', 'ikan.peserta', 'reviewer'])
                    ->orderBy('created_at')
                    ->get();

                // Group by peserta
                $groupedByPeserta = $nominations->groupBy(function ($n) {
                    $pesertaId = optional($n->ikan)->peserta_id;
                    return $pesertaId ?? 'unknown_' . $n->id;
                })->sortKeys();

                // Separate for summary
                $approvedList = $nominations->where('status', 'approved');
                $rejectedList = $nominations->where('status', 'rejected');

                $headers = ['NO', 'TANK', 'KATEGORI', 'KELAS', 'STATUS', 'NOMINASI OLEH', 'REVIEW OLEH', 'TANGGAL REVIEW', 'CATATAN'];
                $row = 1;

                // ═══════════════════════════════════════════
                // 3. TABEL PER PESERTA
                // ═══════════════════════════════════════════
                foreach ($groupedByPeserta as $pesertaId => $items) {
                    $firstItem = $items->first();
                    $peserta = optional($firstItem->ikan)->peserta;
                    $pesertaName = $peserta ? $peserta->nama_peserta : 'Unknown';
                    $detailAnggota = $peserta ? ($peserta->detail_anggota ?? '-') : '-';

                    // Hitung jumlah diterima & ditolak untuk peserta ini
                    $jmlApproved = $items->where('status', 'approved')->count();
                    $jmlRejected = $items->where('status', 'rejected')->count();

                    // Title: Nama Peserta
                    $sheet->mergeCells("A{$row}:I{$row}");
                    $sheet->setCellValue("A{$row}", "PESERTA: {$pesertaName}  |  Diterima: {$jmlApproved}  |  Ditolak: {$jmlRejected}");
                    $sheet->getStyle("A{$row}:I{$row}")->applyFromArray($styleTitle);
                    $row++;

                    // Subtitle: Team
                    $sheet->mergeCells("A{$row}:I{$row}");
                    $sheet->setCellValue("A{$row}", "TEAM: {$detailAnggota}");
                    $sheet->getStyle("A{$row}:I{$row}")->applyFromArray($styleTitle);
                    $row++;

                    // Header Tabel
                    $sheet->fromArray([$headers], null, "A{$row}");
                    $sheet->getStyle("A{$row}:I{$row}")->applyFromArray($styleHeader);
                    $row++;

                    // Data rows
                    $no = 1;
                    $startDataRow = $row;

                    foreach ($items as $n) {
                        $ikan = $n->ikan;
                        $statusText = $n->status === 'approved' ? 'DISETUJUI' : 'DITOLAK';
                        $tankStr = $ikan ? ('Tank ' . ($ikan->nomor_tank ?? '-')) : '-';

                        $sheet->setCellValue("A{$row}", $no++);
                        $sheet->setCellValue("B{$row}", $tankStr);
                        $sheet->setCellValue("C{$row}", $ikan->kategori ?? '-');
                        $sheet->setCellValue("D{$row}", $ikan->kelas ?? '-');
                        $sheet->setCellValue("E{$row}", $statusText);
                        $sheet->setCellValue("F{$row}", $n->juri ? $n->juri->name : '-');
                        $sheet->setCellValue("G{$row}", $n->reviewer ? $n->reviewer->name : '-');
                        $sheet->setCellValue("H{$row}", $n->reviewed_at ? $n->reviewed_at->format('d M Y, H:i') : '-');
                        $sheet->setCellValue("I{$row}", $n->catatan ?: '-');

                        // Warna baris sesuai status
                        if ($n->status === 'approved') {
                            $sheet->getStyle("A{$row}:I{$row}")->applyFromArray($styleRowApproved);
                        } else {
                            $sheet->getStyle("A{$row}:I{$row}")->applyFromArray($styleRowRejected);
                        }
                        $row++;
                    }

                    // Border data
                    if ($row > $startDataRow) {
                        $sheet->getStyle("A{$startDataRow}:I" . ($row - 1))->applyFromArray($styleBorder);
                    }

                    // Jarak antar peserta
                    $row += 2;
                }

                // ═══════════════════════════════════════════
                // 4. RINGKASAN: NOMINASI DITERIMA
                // ═══════════════════════════════════════════
                $row += 1;
                $sheet->mergeCells("A{$row}:I{$row}");
                $sheet->setCellValue("A{$row}", "═══════ RINGKASAN NOMINASI DITERIMA ({$approvedList->count()} IKAN) ═══════");
                $sheet->getStyle("A{$row}:I{$row}")->applyFromArray($styleTitleGreen);
                $row += 2;

                $headersSummary = ['NO', 'NAMA PESERTA', 'TEAM', 'TANK', 'KATEGORI', 'KELAS', 'NOMINASI OLEH', 'REVIEW OLEH', 'TANGGAL REVIEW'];
                $sheet->fromArray([$headersSummary], null, "A{$row}");
                $sheet->getStyle("A{$row}:I{$row}")->applyFromArray($styleHeaderGreen);
                $row++;

                $no = 1;
                $startDataRow = $row;

                foreach ($approvedList as $n) {
                    $ikan = $n->ikan;
                    $peserta = $ikan ? $ikan->peserta : null;

                    $sheet->setCellValue("A{$row}", $no++);
                    $sheet->setCellValue("B{$row}", $peserta ? $peserta->nama_peserta : '-');
                    $sheet->setCellValue("C{$row}", $peserta ? ($peserta->detail_anggota ?? '-') : '-');
                    $sheet->setCellValue("D{$row}", $ikan ? ('Tank ' . ($ikan->nomor_tank ?? '-')) : '-');
                    $sheet->setCellValue("E{$row}", $ikan->kategori ?? '-');
                    $sheet->setCellValue("F{$row}", $ikan->kelas ?? '-');
                    $sheet->setCellValue("G{$row}", $n->juri ? $n->juri->name : '-');
                    $sheet->setCellValue("H{$row}", $n->reviewer ? $n->reviewer->name : '-');
                    $sheet->setCellValue("I{$row}", $n->reviewed_at ? $n->reviewed_at->format('d M Y, H:i') : '-');

                    $sheet->getStyle("A{$row}:I{$row}")->applyFromArray($styleRowApproved);
                    $row++;
                }

                if ($row > $startDataRow) {
                    $sheet->getStyle("A{$startDataRow}:I" . ($row - 1))->applyFromArray($styleBorderGreen);
                }

                // ═══════════════════════════════════════════
                // 5. RINGKASAN: NOMINASI DITOLAK
                // ═══════════════════════════════════════════
                $row += 2;
                $sheet->mergeCells("A{$row}:I{$row}");
                $sheet->setCellValue("A{$row}", "═══════ RINGKASAN NOMINASI DITOLAK ({$rejectedList->count()} IKAN) ═══════");
                $sheet->getStyle("A{$row}:I{$row}")->applyFromArray($styleTitleRed);
                $row += 2;

                $headersSummaryReject = ['NO', 'NAMA PESERTA', 'TEAM', 'TANK', 'KATEGORI', 'KELAS', 'NOMINASI OLEH', 'REVIEW OLEH', 'CATATAN'];
                $sheet->fromArray([$headersSummaryReject], null, "A{$row}");
                $sheet->getStyle("A{$row}:I{$row}")->applyFromArray($styleHeaderRed);
                $row++;

                $no = 1;
                $startDataRow = $row;

                foreach ($rejectedList as $n) {
                    $ikan = $n->ikan;
                    $peserta = $ikan ? $ikan->peserta : null;

                    $sheet->setCellValue("A{$row}", $no++);
                    $sheet->setCellValue("B{$row}", $peserta ? $peserta->nama_peserta : '-');
                    $sheet->setCellValue("C{$row}", $peserta ? ($peserta->detail_anggota ?? '-') : '-');
                    $sheet->setCellValue("D{$row}", $ikan ? ('Tank ' . ($ikan->nomor_tank ?? '-')) : '-');
                    $sheet->setCellValue("E{$row}", $ikan->kategori ?? '-');
                    $sheet->setCellValue("F{$row}", $ikan->kelas ?? '-');
                    $sheet->setCellValue("G{$row}", $n->juri ? $n->juri->name : '-');
                    $sheet->setCellValue("H{$row}", $n->reviewer ? $n->reviewer->name : '-');
                    $sheet->setCellValue("I{$row}", $n->catatan ?: '-');

                    $sheet->getStyle("A{$row}:I{$row}")->applyFromArray($styleRowRejected);
                    $row++;
                }

                if ($row > $startDataRow) {
                    $sheet->getStyle("A{$startDataRow}:I" . ($row - 1))->applyFromArray($styleBorderRed);
                }

                // ═══════════════════════════════════════════
                // 6. COLUMN SIZING
                // ═══════════════════════════════════════════
                $sheet->getColumnDimension('A')->setWidth(5);
                $sheet->getColumnDimension('B')->setWidth(22);
                $sheet->getColumnDimension('C')->setWidth(22);
                $sheet->getColumnDimension('D')->setWidth(14);
                $sheet->getColumnDimension('E')->setWidth(14);
                $sheet->getColumnDimension('F')->setWidth(18);
                $sheet->getColumnDimension('G')->setWidth(18);
                $sheet->getColumnDimension('H')->setWidth(20);
                $sheet->getColumnDimension('I')->setWidth(35);

                $sheet->freezePane('A1');
            },
        ];
    }
}