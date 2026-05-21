<?php

namespace App\Exports\Sheets;

use App\Models\Ikan;
use App\Models\Peserta;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class MvpIkanSheet implements WithTitle, WithEvents
{
    public function title(): string
    {
        return 'DATA IKAN MVP';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // ═══════════════════════════════════════════════════════
                // 1. STYLE DEFINITIONS
                // ═══════════════════════════════════════════════════════
                $styleHeader = [
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '4C1D95']],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                ];
                $styleBorder = [
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDD6FE']]],
                ];
                $styleTitleBlock = [
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '4C1D95']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F5F3FF']],
                    'alignment' => ['vertical' => 'center'],
                ];

                // ═══════════════════════════════════════════════════════
                // 2. DATA RETRIEVAL
                // ═══════════════════════════════════════════════════════
                
                // Tabel 1: Rekap Utama
                $ikans = Ikan::where('is_mvp', true)
                    ->whereHas('peserta', fn($q) => $q->where('is_mvp_submitted', true))
                    ->with('peserta')
                    ->orderBy('peserta_id')->orderBy('kategori')
                    ->get()->groupBy('peserta_id');

                // Tabel 2 & 3: Grup Kategori & Kelas
                $mvpKategori = Ikan::where('is_mvp', true)
                    ->whereHas('peserta', fn($q) => $q->where('is_mvp_submitted', true))
                    ->with('peserta')->get()->groupBy('kategori');

                $mvpKelas = Ikan::where('is_mvp', true)
                    ->whereHas('peserta', fn($q) => $q->where('is_mvp_submitted', true))
                    ->with('peserta')->get()->groupBy('kelas');

                $pesertas = Peserta::where('is_mvp_submitted', true)->orderBy('nama_peserta')->get();

                // ═══════════════════════════════════════════════════════
                // 3. RENDERING TABEL ATAS (VERTIKAL)
                // ═══════════════════════════════════════════════════════
                $row = 1;

                // --- TABEL 1: REKAP UTAMA (A-E) ---
                $sheet->fromArray([['NO', 'NAMA PESERTA', 'DETAIL ANGGOTA', 'TOTAL', 'DAFTAR IKAN']], null, "A{$row}");
                $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleHeader);
                $row++;
                
                $no = 1;
                $startT1 = $row;
                foreach ($ikans as $list) {
                    $p = $list->first()->peserta;
                    $daftar = $list->map(fn($i) => $i->kategori . '-' . ($i->kelas ?? '-') . ' Tank ' . ($i->nomor_tank ?? '-'))->implode("\n");
                    
                    $sheet->setCellValue("A{$row}", $no++);
                    $sheet->setCellValue("B{$row}", $p->nama_peserta ?? '-');
                    $sheet->setCellValue("C{$row}", $p->detail_anggota ?? '-');
                    $sheet->setCellValue("D{$row}", $list->count());
                    $sheet->setCellValue("E{$row}", $daftar);
                    $sheet->getStyle("E{$row}")->getAlignment()->setWrapText(true);
                    $row++;
                }
                if ($row > $startT1) $sheet->getStyle("A{$startT1}:E" . ($row-1))->applyFromArray($styleBorder);
                $row += 2; // Jarak

                // --- TABEL 2 & 3 (SIDE BY SIDE) ---
                $rowTable23 = $row;
                
                // Tabel 2 (Kiri: A-E)
                $sheet->fromArray([['NO', 'KATEGORI', 'PESERTA', 'DETAIL', 'JML']], null, "A{$rowTable23}");
                $sheet->getStyle("A{$rowTable23}:E{$rowTable23}")->applyFromArray($styleHeader);
                $r2 = $rowTable23 + 1;
                $no = 1;
                foreach ($mvpKategori as $kat => $items) {
                    foreach ($items->groupBy('peserta_id') as $itemsPeserta) {
                        $p = $itemsPeserta->first()->peserta;
                        $sheet->setCellValue("A{$r2}", $no++);
                        $sheet->setCellValue("B{$r2}", $kat);
                        $sheet->setCellValue("C{$r2}", $p->nama_peserta ?? '-');
                        $sheet->setCellValue("D{$r2}", $p->detail_anggota ?? '-');
                        $sheet->setCellValue("E{$r2}", $itemsPeserta->count());
                        $r2++;
                    }
                }
                if ($r2 > $rowTable23 + 1) $sheet->getStyle("A" . ($rowTable23+1) . ":E" . ($r2-1))->applyFromArray($styleBorder);

                // Tabel 3 (Kanan: G-K)
                $sheet->fromArray([['NO', 'KELAS', 'PESERTA', 'DETAIL', 'JML']], null, "G{$rowTable23}");
                $sheet->getStyle("G{$rowTable23}:K{$rowTable23}")->applyFromArray($styleHeader);
                $r3 = $rowTable23 + 1;
                $no = 1;
                foreach ($mvpKelas as $kls => $items) {
                    foreach ($items->groupBy('peserta_id') as $itemsPeserta) {
                        $p = $itemsPeserta->first()->peserta;
                        $sheet->setCellValue("G{$r3}", $no++);
                        $sheet->setCellValue("H{$r3}", $kls);
                        $sheet->setCellValue("I{$r3}", $p->nama_peserta ?? '-');
                        $sheet->setCellValue("J{$r3}", $p->detail_anggota ?? '-');
                        $sheet->setCellValue("K{$r3}", $itemsPeserta->count());
                        $r3++;
                    }
                }
                if ($r3 > $rowTable23 + 1) $sheet->getStyle("G" . ($rowTable23+1) . ":K" . ($r3-1))->applyFromArray($styleBorder);

                // Update posisi row terakhir
                $row = max($r2, $r3) + 3; // Beri jarak 3 baris sebelum tabel detail

                // ═══════════════════════════════════════════════════════
                // 4. RENDERING TABEL DETAIL PESERTA (HORIZONTAL)
                // ═══════════════════════════════════════════════════════
                
                $blocksPerRow = 4; // Jumlah tabel per baris horizontal
                $blockWidth = 6;   // Lebar blok (5 Kolom + 1 Gap)
                $blockHeight = 35; // Tinggi header + 30 baris data
                $currentCol = 1;   // Mulai dari Kolom A
                $startRowDetail = $row; 

                foreach ($pesertas as $idx => $peserta) {
                    // Pindah ke baris baru jika sudah 4 tabel
                    if ($idx > 0 && ($idx % $blocksPerRow == 0)) {
                        $startRowDetail += $blockHeight;
                        $currentCol = 1;
                    }

                    // Ambil data ikan HANYA untuk peserta ini
                    $ikansPeserta = Ikan::where('peserta_id', $peserta->id)
                        ->where('is_mvp', true)
                        ->orderBy('kategori')
                        ->limit(30)
                        ->get();

                    // --- HEADER PESERTA (MERGED) ---
                    $colStartStr = Coordinate::stringFromColumnIndex($currentCol);
                    $colEndStr = Coordinate::stringFromColumnIndex($currentCol + 4); // 5 kolom width

                    $sheet->setCellValue($colStartStr . $startRowDetail, 'PESERTA: ' . ($peserta->nama_peserta ?? '-'));
                    $sheet->mergeCells("{$colStartStr}{$startRowDetail}:{$colEndStr}{$startRowDetail}");
                    $sheet->getStyle("{$colStartStr}{$startRowDetail}")->applyFromArray($styleTitleBlock);

                    $sheet->setCellValue($colStartStr . ($startRowDetail + 1), 'TEAM: ' . ($peserta->detail_anggota ?? '-'));
                    $sheet->mergeCells("{$colStartStr}" . ($startRowDetail + 1) . ":{$colEndStr}" . ($startRowDetail + 1));
                    $sheet->getStyle("{$colStartStr}" . ($startRowDetail + 1))->applyFromArray($styleTitleBlock);

                    // --- HEADER TABEL ---
                    $headerRow = $startRowDetail + 2;
                    $sheet->fromArray(['NO', 'KAT', 'KELAS', 'TANK', 'STATUS'], null, "{$colStartStr}{$headerRow}");
                    $sheet->getStyle("{$colStartStr}{$headerRow}:{$colEndStr}{$headerRow}")->applyFromArray($styleHeader);

                    // --- DATA 30 BARIS ---
                    $dataStart = $startRowDetail + 3;
                    for ($i = 1; $i <= 30; $i++) {
                        $r = $dataStart + $i - 1;
                        
                        // SAFE CHECK: Cek apakah index ke-(i-1) ada di collection
                        $ikan = $ikansPeserta->get($i - 1); 

                        // Inisialisasi default kosong
                        $kategori = '';
                        $kelas = '';
                        $tank = '';
                        $status = 'Kosong';

                        // Jika ikan ada, isi datanya
                        if ($ikan) {
                            $kategori = $ikan->kategori;
                            $kelas = $ikan->kelas;
                            $tank = $ikan->nomor_tank ? 'Tank ' . $ikan->nomor_tank : '-';
                            $status = 'MVP';
                        }

                        // Tulis ke sheet
                        $sheet->setCellValue($colStartStr . $r, $i); // Kolom NO
                        $sheet->setCellValue(Coordinate::stringFromColumnIndex($currentCol + 1) . $r, $kategori);
                        $sheet->setCellValue(Coordinate::stringFromColumnIndex($currentCol + 2) . $r, $kelas);
                        $sheet->setCellValue(Coordinate::stringFromColumnIndex($currentCol + 3) . $r, $tank);
                        $sheet->setCellValue(Coordinate::stringFromColumnIndex($currentCol + 4) . $r, $status);
                    }

                    // Styling Body Data (Border)
                    $bodyEnd = $dataStart + 29;
                    $sheet->getStyle("{$colStartStr}{$dataStart}:{$colEndStr}{$bodyEnd}")->applyFromArray($styleBorder);

                    // Lanjut ke kolom berikutnya di sebelah kanan
                    $currentCol += $blockWidth;
                }

                // Auto Size Columns
                foreach (range('A', 'Z') as $columnID) {
                    $sheet->getColumnDimension($columnID)->setAutoSize(true);
                }
                
                $sheet->freezePane('A2');
            },
        ];
    }
}