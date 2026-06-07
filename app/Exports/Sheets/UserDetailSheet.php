<?php

namespace App\Exports\Sheets;

use App\Models\User;
use App\Models\Scoring;
use App\Models\GrandJuriEdit;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class UserDetailSheet implements WithTitle, WithEvents
{
    public function title(): string
    {
        return 'DETAIL PENGGUNA';
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
                $styleTitle = [
                    'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '4C1D95']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F5F3FF']],
                ];

                $row = 1;

                // ═══════════════════════════════════════════════════════
                // 2. BAGIAN JURI (1 TABEL PER JURI)
                // ═══════════════════════════════════════════════════════
                $juris = User::where('role', 'juri')->orderBy('name')->get();

                $sheet->mergeCells("A{$row}:F{$row}");
                $sheet->setCellValue("A{$row}", 'LAPORAN AKTIVITAS JURI');
                $sheet->getStyle("A{$row}:F{$row}")->applyFromArray($styleTitle);
                $sheet->getStyle("A{$row}")->getFont()->setSize(14);
                $row += 2;

                foreach ($juris as $juri) {
                    // -- Header Juri Block --
                    $sheet->mergeCells("A{$row}:F{$row}");
                    $sheet->setCellValue("A{$row}", 'JURI: ' . $juri->name . ' (' . $juri->email . ')');
                    $sheet->getStyle("A{$row}:F{$row}")->applyFromArray($styleTitle);
                    $row++;

                    // -- Sub Header Tabel --
                    $headers = ['NO', 'PESERTA', 'KATEGORI', 'KELAS', 'TANK', 'TOTAL NILAI'];
                    $sheet->fromArray($headers, null, "A{$row}");
                    $sheet->getStyle("A{$row}:F{$row}")->applyFromArray($styleHeader);
                    $row++;

                    // -- Ambil Data Penilaian Juri Ini --
                    // Karena di User model tidak ada relasi scorings, kita query manual
                    $scorings = Scoring::where('juri_id', $juri->id)
                        ->where('submitted_to_grand', true) // Hanya yang sudah dikirim
                        ->with(['ikan.peserta'])
                        ->orderBy('created_at', 'desc')
                        ->get();

                    if ($scorings->isEmpty()) {
                        $sheet->mergeCells("A{$row}:F{$row}");
                        $sheet->setCellValue("A{$row}", 'Belum ada data penilaian.');
                        $sheet->getStyle("A{$row}:J{$row}")->getFont()->setItalic(true);
                        $row++;
                    } else {
                        $no = 1;
                        $startData = $row;
                        foreach ($scorings as $s) {
                            $ikan = $s->ikan;
                            $peserta = $ikan->peserta ?? null;

                            $sheet->setCellValue("A{$row}", $no++);
                            $sheet->setCellValue("B{$row}", $ikan?->nama_peserta ?? $peserta?->nama_peserta ?? '-');
                            $sheet->setCellValue("C{$row}", $ikan->kategori ?? '-');
                            $sheet->setCellValue("D{$row}", $ikan->kelas ?? '-');
                            $sheet->setCellValue("E{$row}", $ikan->nomor_tank ? 'Tank ' . $ikan->nomor_tank : '-');
                            $sheet->setCellValue("F{$row}", $s->total_nilai ?? 0);
                            $row++;
                        }
                        $endData = $row - 1;
                        $sheet->getStyle("A{$startData}:F{$endData}")->applyFromArray($styleBorder);
                    }

                    // Jarak antar juri
                    $row += 2;
                }

                // ═══════════════════════════════════════════════════════
                // 3. BAGIAN GRAND JURI (1 TABEL PER GRAND JURI)
                // ═══════════════════════════════════════════════════════
                $grandJuris = User::where('role', 'grand_juri')->orderBy('name')->get();

                $sheet->mergeCells("A{$row}:F{$row}");
                $sheet->setCellValue("A{$row}", 'LAPORAN INTERVENSI GRAND JURI');
                $sheet->getStyle("A{$row}:F{$row}")->applyFromArray($styleTitle);
                $sheet->getStyle("A{$row}")->getFont()->setSize(14);
                $row += 2;

                foreach ($grandJuris as $gj) {
                    // -- Header Grand Juri Block --
                    $sheet->mergeCells("A{$row}:F{$row}");
                    $sheet->setCellValue("A{$row}", 'GRAND JURI: ' . $gj->name . ' (' . $gj->email . ')');
                    $sheet->getStyle("A{$row}:F{$row}")->applyFromArray($styleTitle);
                    $row++;

                    // -- Sub Header Tabel --
                    $headers = ['NO', 'PESERTA', 'NILAI SEBELUM', 'NILAI SESUDAH', 'WAKTU EDIT', 'KETERANGAN'];
                    $sheet->fromArray($headers, null, "A{$row}");
                    $sheet->getStyle("A{$row}:F{$row}")->applyFromArray($styleHeader);
                    $row++;

                    // -- Ambil Riwayat Edit --
                    $edits = GrandJuriEdit::where('grand_juri_id', $gj->id)
                        ->with('peserta')
                        ->orderBy('created_at', 'desc')
                        ->get();

                    if ($edits->isEmpty()) {
                        $sheet->mergeCells("A{$row}:F{$row}");
                        $sheet->setCellValue("A{$row}", 'Tidak ada riwayat edit nilai.');
                        $sheet->getStyle("A{$row}:J{$row}")->getFont()->setItalic(true);
                        $row++;
                    } else {
                        $no = 1;
                        $startData = $row;
                        foreach ($edits as $e) {
                            $sheet->setCellValue("A{$row}", $no++);
                            $sheet->setCellValue("B{$row}", $e->peserta->nama_peserta ?? '-');
                            $sheet->setCellValue("C{$row}", $e->total_sebelum ?? 0);
                            $sheet->setCellValue("D{$row}", $e->total_sesudah ?? 0);
                            $sheet->setCellValue("E{$row}", $e->created_at->format('d-m-Y H:i'));
                            $sheet->setCellValue("F{$row}", $e->catatan ?? 'Edit Manual');
                            $row++;
                        }
                        $endData = $row - 1;
                        $sheet->getStyle("A{$startData}:F{$endData}")->applyFromArray($styleBorder);
                    }

                    // Jarak antar grand juri
                    $row += 2;
                }

                // ═══════════════════════════════════════════════════════
                // 4. BAGIAN PESERTA (1 TABEL REKAP)
                // ═══════════════════════════════════════════════════════
                $users = User::where('role', 'user')->orderBy('name')->get();

                $sheet->mergeCells("A{$row}:F{$row}");
                $sheet->setCellValue("A{$row}", 'DAFTAR PESERTA TERDAFTAR');
                $sheet->getStyle("A{$row}:F{$row}")->applyFromArray($styleTitle);
                $sheet->getStyle("A{$row}")->getFont()->setSize(14);
                $row += 2;

                // Header Tabel Peserta
                $headers = ['NO', 'NAMA USER', 'EMAIL', 'NAMA TEAM', 'JENIS', 'DETAIL ANGGOTA'];
                $sheet->fromArray($headers, null, "A{$row}");
                $sheet->getStyle("A{$row}:F{$row}")->applyFromArray($styleHeader);
                $row++;

                $no = 1;
                $startData = $row;
                foreach ($users as $u) {
                    // Cek apakah punya data peserta
                    $peserta = $u->peserta; // Asumsi relasi hasOne sudah didefinisikan atau pakai query manual jika belum.
                    
                    // Jika relasi peserta belum ada di model User, gunakan cara manual:
                    if (!isset($u->peserta)) {
                         // Query manual jika relasi tidak ada
                         $peserta = \App\Models\Peserta::where('user_id', $u->id)->first();
                    }

                    $sheet->setCellValue("A{$row}", $no++);
                    $sheet->setCellValue("B{$row}", $u->name);
                    $sheet->setCellValue("C{$row}", $u->email);
                    
                    if ($peserta) {
                        $sheet->setCellValue("D{$row}", $peserta->nama_peserta ?? '-');
                        $sheet->setCellValue("E{$row}", $peserta->jenis_keanggotaan ?? '-');
                        $sheet->setCellValue("F{$row}", $peserta->detail_anggota ?? '-');
                    } else {
                        $sheet->setCellValue("D{$row}", 'Belum Registrasi');
                        $sheet->setCellValue("E{$row}", '-');
                        $sheet->setCellValue("F{$row}", '-');
                    }
                    $row++;
                }

                $endData = $row - 1;
                $sheet->getStyle("A{$startData}:F{$endData}")->applyFromArray($styleBorder);

                // Auto Size
                foreach (range('A', 'F') as $columnID) {
                    $sheet->getColumnDimension($columnID)->setAutoSize(true);
                }
                
                $sheet->freezePane('A2');
            },
        ];
    }
}