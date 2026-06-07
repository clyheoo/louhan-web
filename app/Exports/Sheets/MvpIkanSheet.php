<?php

namespace App\Exports\Sheets;

use App\Models\Ikan;
use App\Models\Peserta;
use App\Helpers\PointCalculator;
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

    // Helper: Hitung Nilai, Bonus, Total (Sama persis dengan Controller)
    private function calculateMetrics($ikan)
    {
        $scorings = $ikan->scorings;
        if ($scorings->isEmpty()) {
            return ['nilai' => 0, 'bonus' => 0, 'total' => 0];
        }

        $jumlahJuriYangNilai = 0;
        $avgDetail = [];

        foreach ($scorings as $s) {
            if ($s->total_nilai) $jumlahJuriYangNilai++;
            if ($s->nilai_detail && is_array($s->nilai_detail)) {
                foreach ($s->nilai_detail as $kat => $fields) {
                    if (!is_array($fields)) continue;
                    foreach ($fields as $fid => $val) {
                        if (!isset($avgDetail[$kat][$fid])) {
                            $avgDetail[$kat][$fid] = ['sum' => 0, 'count' => 0];
                        }
                        $avgDetail[$kat][$fid]['sum'] += (float)($val ?? 0);
                        $avgDetail[$kat][$fid]['count']++;
                    }
                }
            }
        }

        $finalAvgDetail = [];
        if ($jumlahJuriYangNilai > 0) {
            foreach ($avgDetail as $kat => $fields) {
                $finalAvgDetail[$kat] = [];
                foreach ($fields as $fid => $d) {
                    $finalAvgDetail[$kat][$fid] = $d['count'] > 0 ? $d['sum'] / $d['count'] : 0;
                }
            }
        }

        $nilaiPoint = PointCalculator::hitungPoint($ikan->kategori, $finalAvgDetail);
        $bonus = (int) $ikan->bonusPoints->sum('points');
        $finalPoint = $nilaiPoint + $bonus;

        return [
            'nilai' => round($nilaiPoint, 2),
            'bonus' => $bonus,
            'total' => round($finalPoint, 2)
        ];
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
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
                ];
                $styleBorder = [
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDD6FE']]],
                ];
                $styleTitle = [
                    'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '4C1D95']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F5F3FF']],
                ];

                // ═══════════════════════════════════════════════════════
                // 2. DATA RETRIEVAL
                // ═══════════════════════════════════════════════════════
                $allIkans = Ikan::where('is_mvp', true)
                    ->whereHas('peserta', fn($q) => $q->where('is_mvp_submitted', true))
                    ->with(['peserta', 'bonusPoints', 'scorings'])
                    ->orderBy('peserta_id')->orderBy('kategori')->get();

                // Grouping untuk Tabel Kategori
                $groupedKategori = $allIkans->groupBy('kategori');
                
                // Grouping untuk Tabel Kelas
                $groupedKelas = $allIkans->groupBy('kelas');

                $pesertas = Peserta::where('is_mvp_submitted', true)->orderBy('nama_peserta')->get();

                // ═══════════════════════════════════════════════════════
                // 3. TABEL 1: REKAP UTAMA (FLAT LIST PER IKAN)
                // ═══════════════════════════════════════════════════════
                $row = 1;
                // Header: NO, PESERTA, TEAM, KATEGORI, KELAS, TANK, NILAI, BONUS, TOTAL POINT
                $headersT1 = ['NO', 'NAMA PESERTA', 'DETAIL ANGGOTA', 'KATEGORI', 'KELAS', 'TANK', 'NILAI', 'BONUS', 'TOTAL POINT'];
                $sheet->fromArray($headersT1, null, "A{$row}");
                $sheet->getStyle("A{$row}:I{$row}")->applyFromArray($styleHeader);
                $row++;

                $no = 1;
                $lastPesertaId = null;

                foreach ($allIkans as $ikan) {
                    $p = $ikan->peserta;
                    $m = $this->calculateMetrics($ikan);

                    // ★ Snapshot per ikan (1 email bisa pakai >1 identitas)
                    // Kunci grouping = kombinasi snapshot, supaya identitas yang berbeda
                    // tetap tampil berurutan tanpa di-merge jadi 1.
                    $snapshotKey = ($ikan->nama_peserta ?? '-') . '|' . ($ikan->detail_anggota ?? '-');

                    $namaPeserta = '';
                    $detailAnggota = '';

                    if ($snapshotKey !== ($lastPesertaId ?? null)) {
                        $namaPeserta   = $ikan->nama_peserta   ?? $p?->nama_peserta   ?? '-';
                        $detailAnggota = $ikan->detail_anggota ?? $p?->detail_anggota ?? '-';
                        $lastPesertaId = $snapshotKey; // reuse variable name tetap, tapi isinya snapshot key
                    }

                    $rowData = [
                        $no++,
                        $namaPeserta,
                        $detailAnggota,
                        $ikan->kategori,
                        $ikan->kelas,
                        $ikan->nomor_tank ? 'Tank ' . $ikan->nomor_tank : '-',
                        $m['nilai'],
                        $m['bonus'],
                        $m['total']
                    ];

                    $sheet->fromArray($rowData, null, "A{$row}");
                    $row++;
                }
                // Styling body Tabel 1
                if ($row > 2) $sheet->getStyle("A2:I" . ($row - 1))->applyFromArray($styleBorder);

                // Jarak antar Tabel
                $row += 2;

                // ═══════════════════════════════════════════════════════
                // 4. TABEL 2: MVP PER KATEGORI (1 KATEGORI = 1 TABEL)
                // ═══════════════════════════════════════════════════════
                foreach ($groupedKategori as $kategori => $ikansInKat) {
                    // Header Kategori
                    $sheet->mergeCells("A{$row}:E{$row}");
                    $sheet->setCellValue("A{$row}", 'KATEGORI: ' . $kategori);
                    $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleTitle);
                    $row++;

                    // Header Tabel
                    $sheet->fromArray([['NO', 'PESERTA', 'DETAIL', 'JML IKAN', 'TOTAL POINT']], null, "A{$row}");
                    $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleHeader);
                    $row++;

                    // Data: Group by Peserta dalam kategori ini
                    $groupedPeserta = $ikansInKat->groupBy('peserta_id');
                    $no = 1;
                    $startDataRow = $row;

                    foreach ($groupedPeserta as $list) {
                        $firstIkan = $list->first();
                        $p         = $firstIkan->peserta;
                        
                        $totalPoint = 0;
                        foreach($list as $i) { $totalPoint += $this->calculateMetrics($i)['total']; }

                        // ★ Pakai snapshot dari ikan pertama di grup (1 peserta bisa pakai >1 identitas;
                        //   detail beda per ikan tetap kelihatan jelas di Tabel 1)
                        $sheet->setCellValue("A{$row}", $no++);
                        $sheet->setCellValue("B{$row}", $firstIkan->nama_peserta   ?? $p?->nama_peserta   ?? '-');
                        $sheet->setCellValue("C{$row}", $firstIkan->detail_anggota ?? $p?->detail_anggota ?? '-');
                        $sheet->setCellValue("D{$row}", $list->count());
                        $sheet->setCellValue("E{$row}", $totalPoint);
                        $row++;
                    }
                    // Styling Body
                    if ($row > $startDataRow) $sheet->getStyle("A" . ($startDataRow) . ":E" . ($row-1))->applyFromArray($styleBorder);

                    // Jarak sebelum kategori berikutnya
                    $row += 2;
                }

                // ═══════════════════════════════════════════════════════
                // 5. TABEL 3: MVP PER KELAS (1 KELAS = 1 TABEL)
                // ═══════════════════════════════════════════════════════
                foreach ($groupedKelas as $kelas => $ikansInKelas) {
                    // Header Kelas
                    $sheet->mergeCells("A{$row}:E{$row}");
                    $sheet->setCellValue("A{$row}", 'KELAS: ' . $kelas);
                    $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleTitle);
                    $row++;

                    // Header Tabel
                    $sheet->fromArray([['NO', 'PESERTA', 'DETAIL', 'JML IKAN', 'TOTAL POINT']], null, "A{$row}");
                    $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleHeader);
                    $row++;

                    // Data
                    $groupedPeserta = $ikansInKelas->groupBy('peserta_id');
                    $no = 1;
                    $startDataRow = $row;

                    foreach ($groupedPeserta as $list) {
                        $firstIkan = $list->first();
                        $p         = $firstIkan->peserta;
                        
                        $totalPoint = 0;
                        foreach($list as $i) { $totalPoint += $this->calculateMetrics($i)['total']; }

                        // ★ Pakai snapshot dari ikan pertama di grup (1 peserta bisa pakai >1 identitas;
                        //   detail beda per ikan tetap kelihatan jelas di Tabel 1)
                        $sheet->setCellValue("A{$row}", $no++);
                        $sheet->setCellValue("B{$row}", $firstIkan->nama_peserta   ?? $p?->nama_peserta   ?? '-');
                        $sheet->setCellValue("C{$row}", $firstIkan->detail_anggota ?? $p?->detail_anggota ?? '-');
                        $sheet->setCellValue("D{$row}", $list->count());
                        $sheet->setCellValue("E{$row}", $totalPoint);
                        $row++;
                    }
                    if ($row > $startDataRow) $sheet->getStyle("A" . ($startDataRow) . ":E" . ($row-1))->applyFromArray($styleBorder);
                    
                    $row += 2;
                }

                // ═══════════════════════════════════════════════════════
                // 6. TABEL DETAIL PESERTA (HORIZONTAL LAYOUT)
                // ═══════════════════════════════════════════════════════
                // (Menggunakan logic yang sama seperti sebelumnya, tapi pastikan pakai $this->calculateMetrics)
                
                $blocksPerRow = 3; 
                $blockWidth = 9;   
                $blockHeight = 35; 
                $currentCol = 1;   
                $startRowDetail = $row; 

                foreach ($pesertas as $idx => $peserta) {
                    if ($idx > 0 && ($idx % $blocksPerRow == 0)) {
                        $startRowDetail += $blockHeight;
                        $currentCol = 1;
                    }

                    $ikansPeserta = Ikan::where('peserta_id', $peserta->id)
                        ->where('is_mvp', true)
                        ->with(['scorings', 'bonusPoints'])
                        ->orderBy('kategori')
                        ->limit(30)
                        ->get();

                    $colStartStr = Coordinate::stringFromColumnIndex($currentCol);
                    $colEndStr = Coordinate::stringFromColumnIndex($currentCol + 7); 

                    $sheet->setCellValue($colStartStr . $startRowDetail, 'PESERTA: ' . ($peserta->nama_peserta ?? '-'));
                    $sheet->mergeCells("{$colStartStr}{$startRowDetail}:{$colEndStr}{$startRowDetail}");
                    $sheet->getStyle("{$colStartStr}{$startRowDetail}")->applyFromArray($styleTitle);

                    $sheet->setCellValue($colStartStr . ($startRowDetail + 1), 'TEAM: ' . ($peserta->detail_anggota ?? '-'));
                    $sheet->mergeCells("{$colStartStr}" . ($startRowDetail + 1) . ":{$colEndStr}" . ($startRowDetail + 1));
                    $sheet->getStyle("{$colStartStr}" . ($startRowDetail + 1))->applyFromArray($styleTitle);

                    $headerRow = $startRowDetail + 2;
                    $headers = ['NO', 'KAT', 'KELAS', 'TANK', 'NILAI', 'BONUS', 'TOTAL', 'STATUS'];
                    $sheet->fromArray($headers, null, "{$colStartStr}{$headerRow}");
                    $sheet->getStyle("{$colStartStr}{$headerRow}:{$colEndStr}{$headerRow}")->applyFromArray($styleHeader);

                    $dataStart = $startRowDetail + 3;
                    for ($i = 1; $i <= 30; $i++) {
                        $r = $dataStart + $i - 1;
                        $ikan = $ikansPeserta->get($i - 1); 

                        $kategori = ''; $kelas = ''; $tank = ''; $nilai = ''; $bonus = ''; $total = ''; $status = 'Kosong';

                        if ($ikan) {
                            $kategori = $ikan->kategori;
                            $kelas = $ikan->kelas;
                            $tank = $ikan->nomor_tank ? 'Tank ' . $ikan->nomor_tank : '-';
                            
                            $m = $this->calculateMetrics($ikan);
                            $nilai = $m['nilai'];
                            $bonus = $m['bonus'];
                            $total = $m['total'];
                            $status = 'MVP';
                        }

                        $rowData = [$i, $kategori, $kelas, $tank, $nilai, $bonus, $total, $status];
                        for($c=0; $c<8; $c++){
                            $sheet->setCellValue(Coordinate::stringFromColumnIndex($currentCol + $c) . $r, $rowData[$c]);
                        }
                    }

                    $bodyEnd = $dataStart + 29;
                    $sheet->getStyle("{$colStartStr}{$dataStart}:{$colEndStr}{$bodyEnd}")->applyFromArray($styleBorder);
                    $currentCol += $blockWidth;
                }

                // Auto Size
                foreach (range('A', 'Z') as $columnID) {
                    $sheet->getColumnDimension($columnID)->setAutoSize(true);
                }
                
                $sheet->freezePane('A2');
            },
        ];
    }
}