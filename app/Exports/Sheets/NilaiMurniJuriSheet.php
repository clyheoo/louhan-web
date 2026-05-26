<?php

namespace App\Exports\Sheets;

use App\Models\Ikan;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class NilaiMurniJuriSheet implements WithTitle, WithEvents
{
    private const DEFECT_CATS = ['head', 'face', 'body', 'finnage'];

    private const CATS = [
        'overall'  => ['label' => 'OVERALL',   'fields' => [['id'=>'impression','label'=>'Impression']]],
        'head'     => ['label' => 'HEAD',       'fields' => [['id'=>'size','label'=>'Size'],['id'=>'bentuk','label'=>'Bentuk Kepala']]],
        'face'     => ['label' => 'FACE',       'fields' => [['id'=>'face','label'=>'Face']]],
        'body'     => ['label' => 'BODY SHAPE', 'fields' => [['id'=>'bentuk','label'=>'Bentuk Badan'],['id'=>'proporsi','label'=>'Proporsional'],['id'=>'pangkal','label'=>'Pangkal']]],
        'marking'  => ['label' => 'MARKING',    'fields' => [['id'=>'fullness','label'=>'Fullness'],['id'=>'contrast','label'=>'Contrast'],['id'=>'bentuk','label'=>'Bentuk']]],
        'pearl'    => ['label' => 'PEARL',      'fields' => [['id'=>'shinning','label'=>'Shinning'],['id'=>'fullness','label'=>'Fullness'],['id'=>'bentuk','label'=>'Bentuk']]],
        'color'    => ['label' => 'COLOUR',     'fields' => [['id'=>'komposisi','label'=>'Komposisi'],['id'=>'kecerahan','label'=>'Kecerahan'],['id'=>'fullness','label'=>'Fullness']]],
        'finnage'  => ['label' => 'FINNAGE',    'fields' => [['id'=>'bentuk','label'=>'Bentuk Sirip & Ekor'],['id'=>'kecerahan','label'=>'Kecerahan']]],
    ];

    public function title(): string
    {
        return 'NILAI MURNI JURI';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // ═══════════════════════════════════════════
                // 1. STYLE DEFINITIONS
                // ═══════════════════════════════════════════
                $styleHeaderMain = [
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '1E40AF']],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '1E3A8A']]],
                ];
                $styleHeaderSub = [
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '3B82F6']],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '2563EB']]],
                ];
                $styleTankHeader = [
                    'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '1E3A8A']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'DBEAFE']],
                    'alignment' => ['horizontal' => 'left', 'vertical' => 'center'],
                    'borders' => ['bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '1E40AF']]],
                ];
                $styleBorder = [
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '93C5FD']]],
                ];
                $styleEdited = [
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FEF3C7']],
                ];
                $styleEditedFont = [
                    'font' => ['italic' => true, 'color' => ['rgb' => '92400E']],
                ];
                $styleDefect = [
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FEE2E2']],
                    'font' => ['size' => 8, 'color' => ['rgb' => '991B1B']],
                    'alignment' => ['horizontal' => 'left', 'vertical' => 'center', 'wrapText' => true],
                ];
                $styleTotal = [
                    'font' => ['bold' => true, 'size' => 11],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'EFF6FF']],
                ];

                // ═══════════════════════════════════════════
                // 2. HITUNG POSISI KOLOM
                // ═══════════════════════════════════════════
                $col = 3; // Mulai dari kolom C (setelah NO, NAMA JURI)
                $catStartCols = [];
                $defectCols = [];

                foreach (self::CATS as $kat => $info) {
                    $catStartCols[$kat] = $col;
                    $fieldCount = count($info['fields']);
                    $hasDefect = in_array($kat, self::DEFECT_CATS);

                    $col += $fieldCount;

                    if ($hasDefect) {
                        $defectCols[$kat] = $col;
                        $col++;
                    }
                }

                $totalNilaiCol = $col;
                $col++;
                $statusCol = $col;
                $lastCol = $col;
                $lastColLetter = Coordinate::stringFromColumnIndex($lastCol);

                // ═══════════════════════════════════════════
                // 3. TULIS HEADER ROW 1 (Kategori)
                // ═══════════════════════════════════════════
                $row = 1;

                // Fixed cols: NO, NAMA JURI
                $sheet->mergeCells('A1:A2');
                $sheet->setCellValue('A1', 'NO');
                $sheet->getStyle('A1:A2')->applyFromArray($styleHeaderMain);

                $sheet->mergeCells('B1:B2');
                $sheet->setCellValue('B1', 'NAMA JURI');
                $sheet->getStyle('B1:B2')->applyFromArray($styleHeaderMain);

                // Dynamic category headers
                foreach (self::CATS as $kat => $info) {
                    $startCol = $catStartCols[$kat];
                    $endCol = $startCol + count($info['fields']) - 1;
                    if (isset($defectCols[$kat])) {
                        $endCol = $defectCols[$kat];
                    }

                    $startLetter = Coordinate::stringFromColumnIndex($startCol);
                    $endLetter = Coordinate::stringFromColumnIndex($endCol);

                    $sheet->mergeCells("{$startLetter}1:{$endLetter}1");
                    $sheet->setCellValue("{$startLetter}1", $info['label']);
                    $sheet->getStyle("{$startLetter}1:{$endLetter}1")->applyFromArray($styleHeaderMain);
                }

                // Fixed end cols: TOTAL, STATUS
                $totalColLetter = Coordinate::stringFromColumnIndex($totalNilaiCol);
                $statusColLetter = Coordinate::stringFromColumnIndex($statusCol);

                $sheet->mergeCells("{$totalColLetter}1:{$totalColLetter}2");
                $sheet->setCellValue("{$totalColLetter}1", 'TOTAL');
                $sheet->getStyle("{$totalColLetter}1:{$totalColLetter}2")->applyFromArray($styleHeaderMain);

                $submitCol = $statusCol + 1;
                $lastCol = $submitCol;
                $lastColLetter = Coordinate::stringFromColumnIndex($lastCol);
                $submitColLetter = Coordinate::stringFromColumnIndex($submitCol);

                $sheet->mergeCells("{$statusColLetter}1:{$statusColLetter}2");
                $sheet->setCellValue("{$statusColLetter}1", 'EDIT STATUS');
                $sheet->getStyle("{$statusColLetter}1:{$statusColLetter}2")->applyFromArray($styleHeaderMain);

                $sheet->mergeCells("{$submitColLetter}1:{$submitColLetter}2");
                $sheet->setCellValue("{$submitColLetter}1", 'SUBMIT');
                $sheet->getStyle("{$submitColLetter}1:{$submitColLetter}2")->applyFromArray($styleHeaderMain);

                // ═══════════════════════════════════════════
                // 4. TULIS HEADER ROW 2 (Sub-komponen)
                // ═══════════════════════════════════════════
                $row = 2;

                foreach (self::CATS as $kat => $info) {
                    $col = $catStartCols[$kat];
                    foreach ($info['fields'] as $f) {
                        $colLetter = Coordinate::stringFromColumnIndex($col);
                        $sheet->setCellValue("{$colLetter}2", $f['label']);
                        $sheet->getStyle("{$colLetter}2")->applyFromArray($styleHeaderSub);
                        $col++;
                    }
                    if (isset($defectCols[$kat])) {
                        $colLetter = Coordinate::stringFromColumnIndex($defectCols[$kat]);
                        $sheet->setCellValue("{$colLetter}2", 'DEFECT');
                        $sheet->getStyle("{$colLetter}2")->applyFromArray($styleHeaderSub);
                    }
                }

                // ═══════════════════════════════════════════
                // 5. DATA RETRIEVAL
                // ═══════════════════════════════════════════
                $ikans = Ikan::whereNotNull('nomor_tank')
                    ->whereHas('scorings')
                    ->with(['peserta', 'scorings' => fn($q) => $q->with('juri', 'grandJuri')])
                    ->orderBy('nomor_tank')
                    ->get();

                // ═══════════════════════════════════════════
                // 6. TULIS DATA PER TANK
                // ═══════════════════════════════════════════
                $row = 3;

                foreach ($ikans as $ikan) {
                    $scorings = $ikan->scorings;
                    if ($scorings->isEmpty()) continue;

                    // ── Header Tank ──
                    $sheet->mergeCells("A{$row}:{$lastColLetter}{$row}");
                    $sheet->setCellValue("A{$row}", sprintf(
                        '▶ TANK %s  │  %s  │  %s - Kelas %s  │  Team: %s  │  %d Juri',
                        $ikan->nomor_tank,
                        $ikan->peserta->nama_peserta ?? '-',
                        strtoupper($ikan->kategori),
                        $ikan->kelas ?? '-',
                        $ikan->peserta->detail_anggota ?? '-',
                        $scorings->count()
                    ));
                    $sheet->getStyle("A{$row}:{$lastColLetter}{$row}")->applyFromArray($styleTankHeader);
                    $sheet->getRowDimension($row)->setRowHeight(25);
                    $row++;

                    // ── Data Rows per Juri ──
                    $no = 1;
                    $startDataRow = $row;

                    foreach ($scorings as $scoring) {
                        $nd = $scoring->nilai_detail ?: [];
                        $isEdited = (bool) $scoring->edited_by_grand_juri;

                        // NO
                        $sheet->setCellValue("A{$row}", $no++);

                        // NAMA JURI
                        $juriName = $scoring->juri ? $scoring->juri->name : '-';
                        if ($isEdited && $scoring->grandJuri) {
                            $juriName .= "\n✎ " . $scoring->grandJuri->name;
                        }
                        $sheet->setCellValue("B{$row}", $juriName);
                        if ($isEdited) {
                            $sheet->getStyle("B{$row}")->applyFromArray($styleEditedFont);
                        }

                        // Nilai per komponen
                        foreach (self::CATS as $kat => $info) {
                            $col = $catStartCols[$kat];
                            $catData = $nd[$kat] ?? [];

                            foreach ($info['fields'] as $f) {
                                $colLetter = Coordinate::stringFromColumnIndex($col);
                                // Handle typo shinning vs shining
                                $fieldId = $f['id'];
                                if ($kat === 'pearl' && $fieldId === 'shinning') {
                                    $val = $catData['shinning'] ?? $catData['shining'] ?? 0;
                                } else {
                                    $val = $catData[$fieldId] ?? 0;
                                }
                                $sheet->setCellValue("{$colLetter}{$row}", (float) $val);
                                $col++;
                            }

                            // Defect
                            if (isset($defectCols[$kat])) {
                                $colLetter = Coordinate::stringFromColumnIndex($defectCols[$kat]);
                                $defectKey = "raw_{$kat}_penalty";
                                $defects = $scoring->$defectKey ?? [];
                                if (is_string($defects)) $defects = [$defects];
                                $defectText = [];
                                foreach ($defects as $d) {
                                    if ($d && $d !== '0') {
                                        $defectText[] = $d;
                                    }
                                }
                                $sheet->setCellValue("{$colLetter}{$row}", implode(', ', $defectText) ?: '-');
                            }
                        }

                        // TOTAL NILAI
                        $sheet->setCellValue("{$totalColLetter}{$row}", $scoring->total_nilai ?? 0);

                        // EDIT STATUS
                        $editStatus = 'Asli';
                        if ($isEdited) {
                            $editStatus = 'Edited Grand Juri';
                        }
                        $sheet->setCellValue("{$statusColLetter}{$row}", $editStatus);

                        // SUBMIT STATUS
                        $submitStatus = $scoring->submitted_to_grand ? 'Sudah' : 'Draft';
                        $sheet->setCellValue("{$submitColLetter}{$row}", $submitStatus);

                        // Jika masih draft, beri warna abu-abu
                        if (!$scoring->submitted_to_grand) {
                            $sheet->getStyle("A{$row}:{$lastColLetter}{$row}")->applyFromArray([
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F3F4F6']],
                                'font' => ['color' => ['rgb' => '6B7280']]
                            ]);
                        }

                        $row++;
                    }

                    // ── Styling Data Rows ──
                    if ($row > $startDataRow) {
                        // Apply border
                        $sheet->getStyle("A{$startDataRow}:{$lastColLetter}" . ($row - 1))->applyFromArray($styleBorder);

                        // Apply edited styling per row
                        foreach ($scorings as $idx => $scoring) {
                            if ($scoring->edited_by_grand_juri) {
                                $r = $startDataRow + $idx;
                                $sheet->getStyle("A{$r}:{$lastColLetter}{$r}")->applyFromArray($styleEdited);
                                $sheet->getStyle("B{$r}")->applyFromArray($styleEditedFont);
                            }
                        }

                        // Apply defect styling
                        foreach ($defectCols as $colIdx) {
                            $colLetter = Coordinate::stringFromColumnIndex($colIdx);
                            for ($r = $startDataRow; $r < $row; $r++) {
                                $val = $sheet->getCell("{$colLetter}{$r}")->getValue();
                                if ($val && $val !== '-') {
                                    $sheet->getStyle("{$colLetter}{$r}")->applyFromArray($styleDefect);
                                }
                            }
                        }

                        // Apply total styling
                        $sheet->getStyle("{$totalColLetter}{$startDataRow}:{$totalColLetter}" . ($row - 1))->applyFromArray($styleTotal);
                    }

                    // ── Jarak antar tank ──
                    $row += 2;
                }

                // ═══════════════════════════════════════════
                // 7. SET COLUMN WIDTHS
                // ═══════════════════════════════════════════
                $sheet->getColumnDimension('A')->setWidth(5);
                $sheet->getColumnDimension('B')->setWidth(28);

                foreach (self::CATS as $kat => $info) {
                    $col = $catStartCols[$kat];
                    foreach ($info['fields'] as $f) {
                        $colLetter = Coordinate::stringFromColumnIndex($col);
                        $sheet->getColumnDimension($colLetter)->setWidth(11);
                        $col++;
                    }
                    if (isset($defectCols[$kat])) {
                        $colLetter = Coordinate::stringFromColumnIndex($defectCols[$kat]);
                        $sheet->getColumnDimension($colLetter)->setWidth(20);
                    }
                }

                $sheet->getColumnDimension($totalColLetter)->setWidth(9);
                $sheet->getColumnDimension($statusColLetter)->setWidth(18);
                $sheet->getColumnDimension($submitColLetter)->setWidth(10);

                // ═══════════════════════════════════════════
                // 8. FINAL SETTINGS
                // ═══════════════════════════════════════════
                $sheet->freezePane('C3');
                $sheet->getRowDimension(1)->setRowHeight(22);
                $sheet->getRowDimension(2)->setRowHeight(40);

                // Alignment center untuk semua data
                $lastRow = $sheet->getHighestRow();
                $sheet->getStyle("A3:{$lastColLetter}{$lastRow}")
                    ->getAlignment()
                    ->setHorizontal('center')
                    ->setVertical('center');
            },
        ];
    }
}