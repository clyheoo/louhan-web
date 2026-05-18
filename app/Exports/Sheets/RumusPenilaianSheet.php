<?php

namespace App\Exports\Sheets;

use App\Models\ScoringPointConfig;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class RumusPenilaianSheet implements FromArray, WithTitle, WithEvents, ShouldAutoSize
{
    public function title(): string
    {
        return 'RUMUS PENILAIAN';
    }

    public function array(): array
    {
        $configs = ScoringPointConfig::orderBy('kategori')->get();

        $rows = [];

        // ══════════════════════════════════════════
        // SECTION 1: BOBOT & NILAI MAKS PER KATEGORI
        // ══════════════════════════════════════════
        //
        // Kolom (27 total: A s/d AA):
        // A=KATEGORI, B-C=OVERALL, D-F=HEAD, G-H=FACE, I-L=BODY,
        // M-P=MARKING, Q-T=PEARL, U-X=COLOUR, Y-AA=FINNAGE
        //

        // Row 1: Merged category headers
        $row1 = ['KATEGORI', 'OVERALL', '', 'HEAD', '', '', 'FACE', '', 'BODY SHAPE', '', '', '', 'MARKING', '', '', '', 'PEARL', '', '', '', 'COLOUR', '', '', '', 'FINNAGE', '', ''];
        // Row 2: Sub-headers (BOBOT / MAX labels)
        $row2 = ['', 'BOBOT', 'MAX', 'BOBOT', '%SIZE', '%BENTUK', 'BOBOT', '%FACE', 'BOBOT', '%BENTUK', '%PROPORSIONAL', '%PANGKAL', 'BOBOT', '%FULLNESS', '%CONTRAST', '%BENTUK', 'BOBOT', '%SHINNING', '%FULLNESS', '%BENTUK', 'BOBOT', '%KOMPOSISI', '%KECERAHAN', '%FULLNESS', 'BOBOT', '%BENTUK', '%KECERAHAN'];

        $rows[] = $row1;
        $rows[] = $row2;

        // Data rows: one per kategori
        foreach ($configs as $cfg) {
            $rows[] = [
                strtoupper($cfg->kategori),
                (float) $cfg->overall_bobot,
                100,
                (float) $cfg->head_bobot,
                60,
                40,
                (float) $cfg->face_bobot,
                100,
                (float) $cfg->body_bobot,
                50,
                40,
                10,
                (float) $cfg->marking_bobot,
                40,
                40,
                20,
                (float) $cfg->pearl_bobot,
                45,
                35,
                20,
                (float) $cfg->color_bobot,
                45,
                35,
                20,
                (float) $cfg->finnage_bobot,
                75,
                25,
            ];
        }

        // Empty separator row
        $rows[] = array_fill(0, 27, '');

        // ══════════════════════════════════════════
        // SECTION 2: RUMUS PERHITUNGAN POINT
        // ══════════════════════════════════════════
        $rows[] = ['RUMUS PERHITUNGAN POINT'];
        $rows[] = ['', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        $rows[] = ['LANGKAH', 'RUMUS / CONTOH', 'KETERANGAN'];
        $rows[] = ['1. Rata-rata per Komponen', '(Nilai Juri 1 + Nilai Juri 2) / Jumlah Juri', 'Setiap komponen (Impression, Size, dll) dirata-rata dari semua juri yang menilai'];
        $rows[] = ['2. Hitung Persentase', 'Rata-rata / Nilai Maks × 100%', 'Mengkonversi ke skala 0-100%. Misal: Size rata-rata 54, maks 60 → 90%'];
        $rows[] = ['3. Point per Komponen', 'Persentase × Bobot Kategori / 100', 'Contoh: 90% × Bobot Head 17.5 / 100 = 15.75 point'];
        $rows[] = ['4. Subtotal per Kategori', 'Jumlah Point semua komponen dalam 1 kategori', 'Head = Point Size + Point Bentuk Kepala'];
        $rows[] = ['5. Total Point', 'Jumlah semua Subtotal Kategori', 'Overall + Head + Face + Body + Marking + Pearl + Colour + Finnage'];
        $rows[] = ['6. Tambah Bonus', 'Total Point + Total Bonus (jika ada)', 'Setiap bonus (Best of The Best, dll) bernilai +100 point'];
        $rows[] = ['7. Final Point', 'Hasil akhir = Total Point + Bonus', 'Inilah nilai yang digunakan untuk menentukan ranking'];
        $rows[] = ['8. Rank Point', '100, 99, 98, ... menurun per posisi', 'Peserta dengan Final Point tertinggi mendapat Rank 100, selanjutnya 99, 98, dst'];

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $lastRow = $sheet->getHighestRow();

                // ── Set minimum widths ──
                $sheet->getColumnDimension('A')->setAutoSize(false)->setWidth(20);
                for ($c = 2; $c <= 27; $c++) {
                    $letter = Coordinate::stringFromColumnIndex($c);
                    $sheet->getColumnDimension($letter)->setAutoSize(false)->setWidth(14);
                }

                // ═══════════════════════════════
                // SECTION 1: MERGED HEADERS
                // ═══════════════════════════════
                // Row 1 merges: OVERALL=B:C, HEAD=D:F, FACE=G:H, BODY SHAPE=I:L,
                //               MARKING=M:P, PEARL=Q:T, COLOUR=U:X, FINNAGE=Y:AA
                $mergeMap = [
                    'OVERALL'     => ['B', 'C'],
                    'HEAD'        => ['D', 'F'],
                    'FACE'        => ['G', 'H'],
                    'BODY SHAPE'  => ['I', 'L'],
                    'MARKING'     => ['M', 'P'],
                    'PEARL'       => ['Q', 'T'],
                    'COLOUR'      => ['U', 'X'],
                    'FINNAGE'     => ['Y', 'AA'],
                ];
                foreach ($mergeMap as $label => $cols) {
                    $sheet->mergeCells("{$cols[0]}1:{$cols[1]}1");
                }

                // Style row 1: Category merged headers (purple)
                $sheet->getStyle("A1:AA1")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '6D28D9']],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                ]);

                // Style row 2: Sub-headers (lighter purple)
                $sheet->getStyle("A2:AA2")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '8B5CF6']],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                ]);

                // Style data rows (row 3 onwards until separator)
                $dataEnd = 3;
                for ($r = 3; $r <= $lastRow; $r++) {
                    $val = $sheet->getCell("A{$r}")->getValue();
                    if ($val === '') { $dataEnd = $r - 1; break; }
                }

                // KATEGORI column: bold purple background
                $sheet->getStyle("A3:A{$dataEnd}")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '4C1D95']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F5F3FF']],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                ]);

                // BOBOT columns: light yellow background
                $bobotCols = ['B', 'D', 'G', 'I', 'M', 'Q', 'U', 'Y'];
                foreach ($bobotCols as $col) {
                    $sheet->getStyle("{$col}3:{$col}{$dataEnd}")->applyFromArray([
                        'font'      => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '92400E']],
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FEF9C3']],
                        'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                        'numberFormat' => ['formatCode' => '0.0'],
                    ]);
                }

                // MAX columns: light blue background
                $maxCols = ['C', 'E', 'F', 'H', 'J', 'K', 'L', 'N', 'O', 'P', 'R', 'S', 'T', 'V', 'W', 'X', 'Z', 'AA'];
                foreach ($maxCols as $col) {
                    $sheet->getStyle("{$col}3:{$col}{$dataEnd}")->applyFromArray([
                        'font'      => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '1E40AF']],
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'DBEAFE']],
                        'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                    ]);
                }

                // Alternating rows for data
                for ($r = 3; $r <= $dataEnd; $r++) {
                    if (($r - 3) % 2 === 1) {
                        // Skip A column (already styled) and bobot/max columns (already styled)
                        foreach (range(1, 27) as $c) {
                            $letter = Coordinate::stringFromColumnIndex($c);
                            $existingFill = $sheet->getStyle("{$letter}{$r}")->getFill()->getStartColor()->getRGB();
                            if ($existingFill === 'FFFFFF' || $existingFill === '000000') {
                                $sheet->getStyle("{$letter}{$r}")->applyFromArray([
                                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FAFAFE']],
                                ]);
                            }
                        }
                    }
                }

                // Borders for section 1
                $sheet->getStyle("A2:AA{$dataEnd}")->applyFromArray([
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDD6FE']],
                    ],
                ]);

                // ═══════════════════════════════
                // SECTION 2: RUMUS PERHITUNGAN
                // ═══════════════════════════════
                // Find "RUMUS PERHITUNGAN POINT" row
                for ($r = $dataEnd + 2; $r <= $lastRow; $r++) {
                    $val = $sheet->getCell("A{$r}")->getValue();
                    if ($val === 'RUMUS PERHITUNGAN POINT') {
                        // Merge this title row
                        $sheet->mergeCells("A{$r}:AA{$r}");
                        $sheet->getStyle("A{$r}:AA{$r}")->applyFromArray([
                            'font'      => ['bold' => true, 'size' => 13, 'color' => ['rgb' => '4C1D95']],
                            'alignment' => ['horizontal' => 'left', 'vertical' => 'center'],
                        ]);
                        // Header row is 2 rows below
                        $headerR = $r + 2;
                        $sheet->getStyle("A{$headerR}:C{$headerR}")->applyFromArray([
                            'font'      => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                            'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '4C1D95']],
                            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                        ]);

                        // Data rows
                        for ($dr = $headerR + 1; $dr <= $lastRow; $dr++) {
                            $sheet->mergeCells("B{$dr}:C{$dr}");
                            // LANGKAH column
                            $sheet->getStyle("A{$dr}")->applyFromArray([
                                'font'      => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '6D28D9']],
                                'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F5F3FF']],
                                'alignment' => ['vertical' => 'center'],
                            ]);
                            // RUMUS column
                            $sheet->getStyle("B{$dr}")->applyFromArray([
                                'font'      => ['size' => 10, 'color' => ['rgb' => '1E293B']],
                                'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FEF9C3']],
                                'alignment' => ['vertical' => 'center', 'wrapText' => true],
                            ]);
                            // KETERANGAN column
                            $sheet->getStyle("D{$dr}")->applyFromArray([
                                'font'      => ['size' => 10, 'color' => ['rgb' => '475569']],
                                'alignment' => ['vertical' => 'center', 'wrapText' => true],
                            ]);
                        }

                        // Borders for rumus section
                        $sheet->getStyle("A{$headerR}:D{$lastRow}")->applyFromArray([
                            'borders' => [
                                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDD6FE']],
                            ],
                        ]);

                        break;
                    }
                }

                // ── Freeze top rows ──
                $sheet->freezePane('B3');

                // ── Row heights ──
                $sheet->getRowDimension(1)->setRowHeight(22);
                $sheet->getRowDimension(2)->setRowHeight(30);
            },
        ];
    }
}