<?php

namespace App\Exports\Sheets;

use App\Models\ScoringPointConfig;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class RumusPenilaianSheet implements FromArray, WithTitle, WithEvents
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
        // SECTION 1: BOBOT & PERSEN PER KATEGORI
        // ══════════════════════════════════════════

        $row1 = ['KATEGORI', 'OVERALL', '', 'HEAD', '', '', 'FACE', '', 'BODY SHAPE', '', '', '', 'MARKING', '', '', '', 'PEARL', '', '', '', 'COLOUR', '', '', '', 'FINNAGE', '', ''];
        $row2 = ['', 'BOBOT', 'POINT', 'BOBOT', '%SIZE', '%BENTUK', 'BOBOT', '%FACE', 'BOBOT', '%BENTUK', '%PROPORSIONAL', '%PANGKAL', 'BOBOT', '%FULLNESS', '%CONTRAST', '%BENTUK', 'BOBOT', '%SHINNING', '%FULLNESS', '%BENTUK', 'BOBOT', '%KOMPOSISI', '%KECERAHAN', '%FULLNESS', 'BOBOT', '%BENTUK', '%KECERAHAN'];

        $rows[] = $row1;
        $rows[] = $row2;

        foreach ($configs as $cfg) {
            $rows[] = [
                strtoupper($cfg->kategori),
                (float) $cfg->overall_bobot,
                (float) $cfg->overall_point,
                (float) $cfg->head_bobot,
                (float) $cfg->head_size_pct,
                (float) $cfg->head_bentuk_k_pct,
                (float) $cfg->face_bobot,
                (float) $cfg->face_face_pct,
                (float) $cfg->body_bobot,
                (float) $cfg->body_bentuk_pct,
                (float) $cfg->body_proposional_pct,
                (float) $cfg->body_pangkal_pct,
                (float) $cfg->marking_bobot,
                (float) $cfg->marking_fullness_pct,
                (float) $cfg->marking_contrast_pct,
                (float) $cfg->marking_bentuk_pct,
                (float) $cfg->pearl_bobot,
                (float) $cfg->pearl_shinning_pct,
                (float) $cfg->pearl_fullnes_pct,
                (float) $cfg->pearl_bentuk_pearl_pct,
                (float) $cfg->color_bobot,
                (float) $cfg->color_komposisi_pct,
                (float) $cfg->color_kecerahan_pct,
                (float) $cfg->color_fullness_colour_pct,
                (float) $cfg->finnage_bobot,
                (float) $cfg->finnage_bentuk_sirip_ekor_pct,
                (float) $cfg->finnage_kecerahan_pct,
            ];
        }

        $rows[] = array_fill(0, 27, '');

        // ══════════════════════════════════════════
        // SECTION 2: RUMUS PERHITUNGAN POINT
        // ══════════════════════════════════════════
        $rows[] = ['RUMUS PERHITUNGAN POINT'];
        $rows[] = ['', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        $rows[] = ['LANGKAH', 'RUMUS / CONTOH', 'KETERANGAN'];
        $rows[] = ['1. Nilai per Komponen', 'Nilai dari setiap juri per komponen', 'Setiap komponen (Impression, Size, Face, dll) dinilai oleh juri'];
        $rows[] = ['2. Point per Komponen', 'Nilai × Bobot Kategori × Persen Komponen / 100', 'Contoh: Size 54, Bobot Head 17.5, %Size 60 → (54×17.5×60)/100 = 567 point'];
        $rows[] = ['3. Subtotal per Kategori', 'Jumlah Point semua komponen dalam 1 kategori', 'Head = Point Size + Point Bentuk Kepala'];
        $rows[] = ['4. Pengurangan Defect', 'Subtotal × (1 - Penalty%) jika ada defect', 'Minor = -10%, Mayor = -30%. Jika total minor ≥3 di seluruh ikan, semua minor naik jadi -30%'];
        $rows[] = ['5. Total Point', 'Jumlah semua Subtotal Kategori (setelah defect)', 'Overall + Head + Face + Body + Marking + Pearl + Colour + Finnage'];
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

                $sheet->getColumnDimension('A')->setAutoSize(false)->setWidth(20);
                for ($c = 2; $c <= 27; $c++) {
                    $letter = Coordinate::stringFromColumnIndex($c);
                    $sheet->getColumnDimension($letter)->setAutoSize(false)->setWidth(14);
                }

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

                $sheet->getStyle("A1:AA1")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '6D28D9']],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                ]);

                $sheet->getStyle("A2:AA2")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '8B5CF6']],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                ]);

                $dataEnd = 3;
                for ($r = 3; $r <= $lastRow; $r++) {
                    $val = $sheet->getCell("A{$r}")->getValue();
                    if ($val === '') { $dataEnd = $r - 1; break; }
                }

                $sheet->getStyle("A3:A{$dataEnd}")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '4C1D95']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F5F3FF']],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                ]);

                $bobotCols = ['B', 'D', 'G', 'I', 'M', 'Q', 'U', 'Y'];
                foreach ($bobotCols as $col) {
                    $sheet->getStyle("{$col}3:{$col}{$dataEnd}")->applyFromArray([
                        'font'      => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '92400E']],
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FEF9C3']],
                        'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                        'numberFormat' => ['formatCode' => '0.0'],
                    ]);
                }

                $pctCols = ['C', 'E', 'F', 'H', 'J', 'K', 'L', 'N', 'O', 'P', 'R', 'S', 'T', 'V', 'W', 'X', 'Z', 'AA'];
                foreach ($pctCols as $col) {
                    $sheet->getStyle("{$col}3:{$col}{$dataEnd}")->applyFromArray([
                        'font'      => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '1E40AF']],
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'DBEAFE']],
                        'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                    ]);
                }

                for ($r = 3; $r <= $dataEnd; $r++) {
                    if (($r - 3) % 2 === 1) {
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

                $sheet->getStyle("A2:AA{$dataEnd}")->applyFromArray([
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDD6FE']],
                    ],
                ]);

                for ($r = $dataEnd + 2; $r <= $lastRow; $r++) {
                    $val = $sheet->getCell("A{$r}")->getValue();
                    if ($val === 'RUMUS PERHITUNGAN POINT') {
                        $sheet->mergeCells("A{$r}:AA{$r}");
                        $sheet->getStyle("A{$r}:AA{$r}")->applyFromArray([
                            'font'      => ['bold' => true, 'size' => 13, 'color' => ['rgb' => '4C1D95']],
                            'alignment' => ['horizontal' => 'left', 'vertical' => 'center'],
                        ]);
                        $headerR = $r + 2;
                        $sheet->getStyle("A{$headerR}:C{$headerR}")->applyFromArray([
                            'font'      => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                            'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '4C1D95']],
                            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                        ]);

                        for ($dr = $headerR + 1; $dr <= $lastRow; $dr++) {
                            $sheet->mergeCells("B{$dr}:C{$dr}");
                            $sheet->getStyle("A{$dr}")->applyFromArray([
                                'font'      => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '6D28D9']],
                                'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F5F3FF']],
                                'alignment' => ['vertical' => 'center'],
                            ]);
                            $sheet->getStyle("B{$dr}")->applyFromArray([
                                'font'      => ['size' => 10, 'color' => ['rgb' => '1E293B']],
                                'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FEF9C3']],
                                'alignment' => ['vertical' => 'center', 'wrapText' => true],
                            ]);
                            $sheet->getStyle("D{$dr}")->applyFromArray([
                                'font'      => ['size' => 10, 'color' => ['rgb' => '475569']],
                                'alignment' => ['vertical' => 'center', 'wrapText' => true],
                            ]);
                        }

                        $sheet->getStyle("A{$headerR}:D{$lastRow}")->applyFromArray([
                            'borders' => [
                                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDD6FE']],
                            ],
                        ]);

                        break;
                    }
                }

                $sheet->freezePane('B3');
                $sheet->getRowDimension(1)->setRowHeight(22);
                $sheet->getRowDimension(2)->setRowHeight(30);
            },
        ];
    }
}