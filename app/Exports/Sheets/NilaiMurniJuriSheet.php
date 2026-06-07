<?php

namespace App\Exports\Sheets;

use App\Models\Scoring;
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
                $styleBorder = [
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '93C5FD']]],
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
                // 2. HITUNG POSISI KOLOM & GARIS PEMISAH
                // ═══════════════════════════════════════════
                // A=1: NO, B=2: NAMA JURI, C=3: NO TANK, D=4: PESERTA/TEAM
                $col = 5; 
                $catStartCols = [];
                $defectCols = [];
                $separatorCols = [5]; // Kolom pertama komponen (OVERALL)

                foreach (self::CATS as $kat => $info) {
                    $catStartCols[$kat] = $col;
                    $fieldCount = count($info['fields']);
                    $hasDefect = in_array($kat, self::DEFECT_CATS);

                    $col += $fieldCount;

                    if ($hasDefect) {
                        $defectCols[$kat] = $col;
                        $col++;
                    }
                    
                    // Tandai kolom awal komponen berikutnya untuk garis pemisah tebal
                    if ($kat !== 'overall') {
                        $separatorCols[] = $catStartCols[$kat];
                    }
                }

                $totalNilaiCol = $col;
                $separatorCols[] = $totalNilaiCol;
                $col++;
                $editStatusCol = $col;
                $lastCol = $editStatusCol;
                $lastColLetter = Coordinate::stringFromColumnIndex($lastCol);

                // ═══════════════════════════════════════════
                // 3. TULIS HEADER ROW 1 (Kategori)
                // ═══════════════════════════════════════════
                $row = 1;

                $sheet->mergeCells('A1:A2');
                $sheet->setCellValue('A1', 'NO');
                $sheet->getStyle('A1:A2')->applyFromArray($styleHeaderMain);

                $sheet->mergeCells('B1:B2');
                $sheet->setCellValue('B1', 'NAMA JURI');
                $sheet->getStyle('B1:B2')->applyFromArray($styleHeaderMain);

                $sheet->mergeCells('C1:C2');
                $sheet->setCellValue('C1', 'NO TANK');
                $sheet->getStyle('C1:C2')->applyFromArray($styleHeaderMain);

                $sheet->mergeCells('D1:D2');
                $sheet->setCellValue('D1', 'PESERTA / TEAM');
                $sheet->getStyle('D1:D2')->applyFromArray($styleHeaderMain);

                foreach (self::CATS as $kat => $info) {
                    $startCol = $catStartCols[$kat];
                    $endCol = $startCol + count($info['fields']) - 1;
                    if (isset($defectCols[$kat])) {
                        $endCol = $defectCols[$kat];
                    }

                    $startLetter = Coordinate::stringFromColumnIndex($startCol);
                    $endLetter = Coordinate::stringFromColumnIndex($endCol);

                    if ($startCol === $endCol) {
                        $sheet->setCellValue("{$startLetter}1", $info['label']);
                        $sheet->getStyle("{$startLetter}1")->applyFromArray($styleHeaderMain);
                    } else {
                        $sheet->mergeCells("{$startLetter}1:{$endLetter}1");
                        $sheet->setCellValue("{$startLetter}1", $info['label']);
                        $sheet->getStyle("{$startLetter}1:{$endLetter}1")->applyFromArray($styleHeaderMain);
                    }
                }

                $totalColLetter = Coordinate::stringFromColumnIndex($totalNilaiCol);
                $editColLetter = Coordinate::stringFromColumnIndex($editStatusCol);

                $sheet->mergeCells("{$totalColLetter}1:{$totalColLetter}2");
                $sheet->setCellValue("{$totalColLetter}1", 'TOTAL');
                $sheet->getStyle("{$totalColLetter}1:{$totalColLetter}2")->applyFromArray($styleHeaderMain);

                $sheet->mergeCells("{$editColLetter}1:{$editColLetter}2");
                $sheet->setCellValue("{$editColLetter}1", 'EDIT STATUS');
                $sheet->getStyle("{$editColLetter}1:{$editColLetter}2")->applyFromArray($styleHeaderMain);

                // ═══════════════════════════════════════════
                // 4. TULIS HEADER ROW 2 (Sub-komponen)
                // ═══════════════════════════════════════════
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
                // 5. DATA RETRIEVAL (DIURUTKAN PER JURI, LALU TANK)
                // ═══════════════════════════════════════════
                $scorings = Scoring::with(['juri', 'ikan.peserta'])
                    ->whereHas('ikan', fn($q) => $q->whereNotNull('nomor_tank'))
                    ->orderBy('juri_id')
                    ->orderBy('ikan_id')
                    ->get();

                // ═══════════════════════════════════════════
                // 6. TULIS DATA BERURUTAN
                // ═══════════════════════════════════════════
                $row = 3;
                $no = 1;
                $lastJuriId = null;
                $startJuriRow = $row;
                $juriMergeRanges = [];

                foreach ($scorings as $scoring) {
                    $currentJuriId = $scoring->juri_id;
                    $isEdited = (bool) $scoring->edited_by_grand_juri;

                    // Jika ganti juri, simpan range merge untuk juri sebelumnya
                    if ($lastJuriId !== null && $currentJuriId !== $lastJuriId) {
                        if ($row - 1 >= $startJuriRow) {
                            $juriMergeRanges[] = [$startJuriRow, $row - 1];
                        }
                        $startJuriRow = $row;
                    }

                    if ($currentJuriId !== $lastJuriId) {
                        $sheet->setCellValue("A{$row}", $no++);
                    }

                    // Kolom B: NAMA JURI (Hanya diisi saat pertama kali juri muncul)
                    if ($currentJuriId !== $lastJuriId) {
                        $juriName = $scoring->juri ? $scoring->juri->name : '-';
                        if ($isEdited && $scoring->grandJuri) {
                            $juriName .= "\n✎ " . $scoring->grandJuri->name;
                        }
                        $sheet->setCellValue("B{$row}", $juriName);
                    }

                    // Kolom C: NO TANK (Hanya angka saja)
                    $tankStr = $scoring->ikan->nomor_tank ?? '-';
                    $sheet->setCellValue("C{$row}", $tankStr);

                    // Kolom D: PESERTA / TEAM (pakai snapshot ikan, bukan data Peserta terkini)
                    $ikanRef     = $scoring->ikan;
                    $pesertaName = $ikanRef->nama_peserta   ?? $ikanRef?->peserta?->nama_peserta   ?? '-';
                    $team        = $ikanRef->detail_anggota ?? $ikanRef?->peserta?->detail_anggota ?? '-';
                    $kat         = strtoupper($ikanRef->kategori ?? '-');
                    $kelas       = $ikanRef->kelas ?? '-';
                    $sheet->setCellValue("D{$row}", "{$pesertaName} ({$kat}-{$kelas})\n{$team}");

                    // Nilai per komponen
                    $nd = $scoring->nilai_detail ?: [];
                    foreach (self::CATS as $katKey => $info) {
                        $col = $catStartCols[$katKey];
                        $catData = $nd[$katKey] ?? [];

                        foreach ($info['fields'] as $f) {
                            $colLetter = Coordinate::stringFromColumnIndex($col);
                            $fieldId = $f['id'];
                            if ($katKey === 'pearl' && $fieldId === 'shinning') {
                                $val = $catData['shinning'] ?? $catData['shining'] ?? 0;
                            } else {
                                $val = $catData[$fieldId] ?? 0;
                            }
                            $sheet->setCellValue("{$colLetter}{$row}", (float) $val);
                            $col++;
                        }

                        if (isset($defectCols[$katKey])) {
                            $colLetter = Coordinate::stringFromColumnIndex($defectCols[$katKey]);
                            $defectKey = "raw_{$katKey}_penalty";
                            $defects = $scoring->$defectKey ?? [];
                            if (is_string($defects)) $defects = [$defects];
                            $defectText = [];
                            foreach ($defects as $d) {
                                if ($d && $d !== '0') $defectText[] = $d;
                            }
                            $sheet->setCellValue("{$colLetter}{$row}", implode(', ', $defectText) ?: '-');
                        }
                    }

                    $sheet->setCellValue("{$totalColLetter}{$row}", $scoring->total_nilai ?? 0);

                    $editStatus = $isEdited ? 'Edited Grand Juri' : 'Asli';
                    $sheet->setCellValue("{$editColLetter}{$row}", $editStatus);

                    $lastJuriId = $currentJuriId;
                    $row++;
                }

                // Simpan merge juri terakhir
                if ($row - 1 >= $startJuriRow) {
                    $juriMergeRanges[] = [$startJuriRow, $row - 1];
                }

                // ═══════════════════════════════════════════
                // 7. APPLY MERGE VERTIKAL UNTUK JURI
                // ═══════════════════════════════════════════
                foreach ($juriMergeRanges as $range) {
                    if ($range[0] < $range[1]) { 
                        $sheet->mergeCells("A{$range[0]}:A{$range[1]}"); // Merge NO
                        $sheet->mergeCells("B{$range[0]}:B{$range[1]}"); // Merge Nama Juri
                    }
                }

                // ═══════════════════════════════════════════
                // 8. STYLING AKHIR
                // ═══════════════════════════════════════════
                $lastRow = $row - 1;
                if ($lastRow >= 3) {
                    $sheet->getStyle("A3:{$lastColLetter}{$lastRow}")->applyFromArray($styleBorder);
                    $sheet->getStyle("A3:{$lastColLetter}{$lastRow}")
                        ->getAlignment()->setHorizontal('center')->setVertical('center');

                    $sheet->getStyle("B3:B{$lastRow}")->getAlignment()->setHorizontal('center')->setVertical('center')->setWrapText(true);
                    $sheet->getStyle("D3:D{$lastRow}")->getAlignment()->setHorizontal('left')->setWrapText(true);

                    $sheet->getStyle("{$totalColLetter}3:{$totalColLetter}{$lastRow}")->applyFromArray($styleTotal);

                    // Warna merah untuk kolom Defect
                    foreach ($defectCols as $colIdx) {
                        $colLetter = Coordinate::stringFromColumnIndex($colIdx);
                        for ($r = 3; $r <= $lastRow; $r++) {
                            $val = $sheet->getCell("{$colLetter}{$r}")->getValue();
                            if ($val && $val !== '-') {
                                $sheet->getStyle("{$colLetter}{$r}")->applyFromArray($styleDefect);
                            }
                        }
                    }
                }

                // ═══════════════════════════════════════════
                // 9. GARIS PEMISAH KOMPONEN (VERTIKAL TEBAL)
                // ═══════════════════════════════════════════
                foreach ($separatorCols as $colIdx) {
                    $colLetter = Coordinate::stringFromColumnIndex($colIdx);
                    for ($r = 1; $r <= $lastRow; $r++) {
                        // Menggunakan getBorders()->getLeft() agar garis atas/bawah/kanan tidak tertimpa
                        $sheet->getStyle("{$colLetter}{$r}")->getBorders()->getLeft()
                            ->setBorderStyle(Border::BORDER_MEDIUM)
                            ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('1E3A8A'));
                    }
                }

                // ═══════════════════════════════════════════
                // 10. SET COLUMN WIDTHS
                // ═══════════════════════════════════════════
                $sheet->getColumnDimension('A')->setWidth(5);
                $sheet->getColumnDimension('B')->setWidth(25);
                $sheet->getColumnDimension('C')->setWidth(10);
                $sheet->getColumnDimension('D')->setWidth(32);

                foreach (self::CATS as $kat => $info) {
                    $col = $catStartCols[$kat];
                    foreach ($info['fields'] as $f) {
                        $colLetter = Coordinate::stringFromColumnIndex($col);
                        $sheet->getColumnDimension($colLetter)->setWidth(11);
                        $col++;
                    }
                    if (isset($defectCols[$kat])) {
                        $colLetter = Coordinate::stringFromColumnIndex($defectCols[$kat]);
                        $sheet->getColumnDimension($colLetter)->setWidth(22);
                    }
                }

                $sheet->getColumnDimension($totalColLetter)->setWidth(9);
                $sheet->getColumnDimension($editColLetter)->setWidth(18);

                // ═══════════════════════════════════════════
                // 11. FINAL SETTINGS
                // ═══════════════════════════════════════════
                $sheet->freezePane('E3'); 
                $sheet->getRowDimension(1)->setRowHeight(22);
                $sheet->getRowDimension(2)->setRowHeight(40);
            },
        ];
    }
}