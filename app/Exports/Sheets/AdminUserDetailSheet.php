<?php

namespace App\Exports\Sheets;

use App\Models\User;
use App\Models\Peserta;
use App\Models\Scoring;
use App\Models\GrandJuriEdit;
use App\Models\PasswordHistory;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class AdminUserDetailSheet implements WithTitle, WithEvents
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
                // STYLE DEFINITIONS
                // ═══════════════════════════════════════════════════════
                $styleHeader = [
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '1E40AF']],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
                ];
                $styleBorder = [
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'BFDBFE']]],
                ];
                $styleTitle = [
                    'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => '1E40AF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'EFF6FF']],
                    'alignment' => ['horizontal' => 'left', 'vertical' => 'center'],
                ];
                $styleSubTitle = [
                    'font' => ['italic' => true, 'size' => 10, 'color' => ['rgb' => '3B82F6']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F0F7FF']],
                    'alignment' => ['horizontal' => 'left', 'vertical' => 'center'],
                ];
                $styleBlockHeader = [
                    'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '1E40AF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'DBEAFE']],
                    'alignment' => ['horizontal' => 'left', 'vertical' => 'center'],
                ];

                $row = 1;

                // ═══════════════════════════════════════════════════════
                // SECTION 1: DAFTAR LENGKAP SEMUA AKUN
                // ═══════════════════════════════════════════════════════
                $sheet->mergeCells("A{$row}:J{$row}");
                $sheet->setCellValue("A{$row}", 'DAFTAR LENGKAP SEMUA AKUN');
                $sheet->getStyle("A{$row}:J{$row}")->applyFromArray($styleTitle);
                $row++;

                $sheet->mergeCells("A{$row}:J{$row}");
                $sheet->setCellValue("A{$row}", 'Semua akun yang terdaftar dalam sistem termasuk Admin, Grand Juri, Juri, dan Peserta');
                $sheet->getStyle("A{$row}:J{$row}")->applyFromArray($styleSubTitle);
                $row += 2;

                $headers1 = ['NO', 'NAMA LENGKAP', 'EMAIL', 'ROLE', 'PASSWORD AKTIF', 'STATUS PESERTA', 'JML IKAN', 'JML PASSWORD DIUBAH', 'TGL DIBUAT', 'TERAKHIR UPDATE'];
                $sheet->fromArray([$headers1], null, "A{$row}");
                $sheet->getStyle("A{$row}:J{$row}")->applyFromArray($styleHeader);
                $row++;

                $allUsers = User::orderByRaw("FIELD(role, 'admin', 'grand_juri', 'juri', 'user')")->orderBy('name')->get();

                // Hitung jumlah password change per user
                $pwdCountMap = PasswordHistory::selectRaw('user_id, COUNT(*) as total')
                    ->groupBy('user_id')
                    ->pluck('total', 'user_id')
                    ->toArray();

                $no = 1;
                $startData = $row;
                foreach ($allUsers as $u) {
                    $peserta = Peserta::where('user_id', $u->id)->first();
                    $jumlahIkan = $peserta ? $peserta->ikans()->count() : 0;
                    $pwdCount = $pwdCountMap[$u->id] ?? 0;

                    $sheet->setCellValue("A{$row}", $no++);
                    $sheet->setCellValue("B{$row}", $u->name);
                    $sheet->setCellValue("C{$row}", $u->email);
                    $sheet->setCellValue("D{$row}", strtoupper(str_replace('_', ' ', $u->role)));
                    $sheet->setCellValue("E{$row}", $u->plain_password ?? '—');
                    $sheet->setCellValue("F{$row}", $peserta ? 'Terdaftar' : 'Belum Registrasi');
                    $sheet->setCellValue("G{$row}", $jumlahIkan);
                    $sheet->setCellValue("H{$row}", $pwdCount);
                    $sheet->setCellValue("I{$row}", $u->created_at->format('d-m-Y H:i:s'));
                    $sheet->setCellValue("J{$row}", $u->updated_at->format('d-m-Y H:i:s'));
                    $row++;
                }
                $endData = $row - 1;

                // Styling: border + alternating
                $sheet->getStyle("A{$startData}:J{$endData}")->applyFromArray($styleBorder);
                for ($r = $startData; $r <= $endData; $r++) {
                    if (($r - $startData) % 2 === 1) {
                        $sheet->getStyle("A{$r}:J{$r}")->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F8FAFC']],
                        ]);
                    }
                }

                // Warna role
                for ($r = $startData; $r <= $endData; $r++) {
                    $v = $sheet->getCell("D{$r}")->getValue();
                    $map = [
                        'ADMIN'      => ['FFFFFF', '2563EB'],
                        'GRAND JURI' => ['FFFFFF', '7C3AED'],
                        'JURI'       => ['15803D', 'DCFCE7'],
                        'USER'       => ['475569', 'F1F5F9'],
                    ];
                    if (isset($map[$v])) {
                        $sheet->getStyle("D{$r}")->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['rgb' => $map[$v][0]]],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => $map[$v][1]]],
                        ]);
                    }
                }

                $row += 2;

                // ═══════════════════════════════════════════════════════
                // SECTION 2: RIWAYAT PERUBAHAN PASSWORD
                // ═══════════════════════════════════════════════════════
                $sheet->mergeCells("A{$row}:J{$row}");
                $sheet->setCellValue("A{$row}", 'RIWAYAT PERUBAHAN PASSWORD');
                $sheet->getStyle("A{$row}:J{$row}")->applyFromArray($styleTitle);
                $row++;

                $sheet->mergeCells("A{$row}:J{$row}");
                $sheet->setCellValue("A{$row}", 'Password awal saat pembuatan akun ditandai sebagai "PASSWORD AWAL". Perubahan password dicatat lengkap beserta siapa yang mengubah.');
                $sheet->getStyle("A{$row}:J{$row}")->applyFromArray($styleSubTitle);
                $row += 2;

                $headers2 = ['NO', 'NAMA USER', 'EMAIL', 'ROLE USER', 'PASSWORD LAMA', 'PASSWORD BARU', 'DIUBAH OLEH', 'ROLE PENGUBAH', 'TANGGAL & WAKTU', 'SELISIH DARI SEBELUMNYA'];
                $sheet->fromArray([$headers2], null, "A{$row}");
                $sheet->getStyle("A{$row}:J{$row}")->applyFromArray($styleHeader);
                $row++;

                $histories = PasswordHistory::orderBy('created_at', 'desc')->get();

                if ($histories->isEmpty()) {
                    $sheet->mergeCells("A{$row}:J{$row}");
                    $sheet->setCellValue("A{$row}", 'Belum ada riwayat perubahan password.');
                    $sheet->getStyle("A{$row}:J{$row}")->getFont()->setItalic(true);;
                    $row++;
                } else {
                    $no = 1;
                    $startData = $row;

                    foreach ($histories as $h) {
                        $user = User::find($h->user_id);
                        $userName  = $user ? $user->name : '(Akun Dihapus)';
                        $userEmail = $user ? $user->email : '—';
                        $userRole  = $user ? strtoupper(str_replace('_', ' ', $user->role)) : '—';
                        $oldPwd    = ($h->old_password && $h->old_password !== '')
                                    ? $h->old_password
                                    : 'PASSWORD AWAL (saat pembuatan akun)';
                        $newPwd    = $h->new_password ?? '—';
                        $changedBy = $h->changed_by ?? 'Sistem';

                        // Cari role pengubah
                        $changerRole = '—';
                        if ($changedBy !== 'Sistem') {
                            $changer = User::where('name', $changedBy)->first();
                            if ($changer) {
                                $changerRole = strtoupper(str_replace('_', ' ', $changer->role));
                            }
                        }

                        $sheet->setCellValue("A{$row}", $no++);
                        $sheet->setCellValue("B{$row}", $userName);
                        $sheet->setCellValue("C{$row}", $userEmail);
                        $sheet->setCellValue("D{$row}", $userRole);
                        $sheet->setCellValue("E{$row}", $oldPwd);
                        $sheet->setCellValue("F{$row}", $newPwd);
                        $sheet->setCellValue("G{$row}", $changedBy);
                        $sheet->setCellValue("H{$row}", $changerRole);
                        $sheet->setCellValue("I{$row}", $h->created_at->format('d-m-Y H:i:s'));

                        // Kolom J: keterangan selisih
                        if (str_contains($oldPwd, 'PASSWORD AWAL')) {
                            $sheet->setCellValue("J{$row}", 'Pembuatan akun baru');
                        } elseif ($oldPwd !== $newPwd) {
                            $sheet->setCellValue("J{$row}", 'Password diganti');
                        } else {
                            $sheet->setCellValue("J{$row}", '—');
                        }

                        $row++;
                    }
                    $endData = $row - 1;
                    $sheet->getStyle("A{$startData}:J{$endData}")->applyFromArray($styleBorder);

                    // Alternating
                    for ($r = $startData; $r <= $endData; $r++) {
                        if (($r - $startData) % 2 === 1) {
                            $sheet->getStyle("A{$r}:J{$r}")->applyFromArray([
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F8FAFC']],
                            ]);
                        }
                    }

                    // Highlight baris "PASSWORD AWAL"
                    for ($r = $startData; $r <= $endData; $r++) {
                        $oldVal = $sheet->getCell("E{$r}")->getValue();
                        if ($oldVal && str_contains($oldVal, 'PASSWORD AWAL')) {
                            $sheet->getStyle("E{$r}")->applyFromArray([
                                'font' => ['italic' => true, 'color' => ['rgb' => '64748B']],
                            ]);
                            $sheet->getStyle("J{$r}")->applyFromArray([
                                'font' => ['italic' => true, 'color' => ['rgb' => '3B82F6']],
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'EFF6FF']],
                            ]);
                        } else {
                            $sheet->getStyle("J{$r}")->applyFromArray([
                                'font' => ['bold' => true, 'color' => ['rgb' => '15803D']],
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'DCFCE7']],
                            ]);
                        }
                    }
                }

                $row += 2;

                // ═══════════════════════════════════════════════════════
                // SECTION 3: AKTIVITAS PENILAIAN PER JURI
                // ═══════════════════════════════════════════════════════
                $juris = User::where('role', 'juri')->orderBy('name')->get();

                $sheet->mergeCells("A{$row}:J{$row}");
                $sheet->setCellValue("A{$row}", 'AKTIVITAS PENILAIAN PER JURI');
                $sheet->getStyle("A{$row}:J{$row}")->applyFromArray($styleTitle);
                $row++;

                $sheet->mergeCells("A{$row}:J{$row}");
                $sheet->setCellValue("A{$row}", 'Daftar penilaian yang sudah dikirim setiap juri ke sistem Grand Juri');
                $sheet->getStyle("A{$row}:J{$row}")->applyFromArray($styleSubTitle);
                $row += 2;

                foreach ($juris as $juri) {
                    $sheet->mergeCells("A{$row}:J{$row}");
                    $sheet->setCellValue("A{$row}", 'JURI: ' . $juri->name . ' (' . $juri->email . ')');
                    $sheet->getStyle("A{$row}:J{$row}")->applyFromArray($styleBlockHeader);
                    $row++;

                    $headers3 = ['NO', 'PESERTA', 'KATEGORI', 'KELAS', 'NO TANK', 'TOTAL NILAI', 'SUBMITTED GRAND', 'DIKUNCI', 'WAKTU PENILAIAN', 'WAKTU SUBMIT'];
                    $sheet->fromArray([$headers3], null, "A{$row}");
                    $sheet->getStyle("A{$row}:J{$row}")->applyFromArray($styleHeader);
                    $row++;

                    $scorings = Scoring::where('juri_id', $juri->id)
                        ->where('submitted_to_grand', true)
                        ->with(['ikan.peserta'])
                        ->orderBy('created_at', 'desc')
                        ->get();

                    if ($scorings->isEmpty()) {
                        $sheet->mergeCells("A{$row}:J{$row}");
                        $sheet->setCellValue("A{$row}", 'Belum ada data penilaian yang dikirim.');
                        $sheet->getStyle("A{$row}:J{$row}")->getFont()->setItalic(true);;
                        $row++;
                    } else {
                        $no = 1;
                        $startData = $row;
                        foreach ($scorings as $s) {
                            $ikan = $s->ikan;
                            $peserta = $ikan ? $ikan->peserta : null;
                            $isLocked = $ikan ? ($ikan->is_locked ?? false) : false;

                            $sheet->setCellValue("A{$row}", $no++);
                            // ★ snapshot ikan, bukan Peserta terkini
                            $sheet->setCellValue("B{$row}", $ikan?->nama_peserta ?? $peserta?->nama_peserta ?? '-');
                            $sheet->setCellValue("C{$row}", $ikan->kategori ?? '-');
                            $sheet->setCellValue("D{$row}", $s->kelas ?? ($ikan->kelas ?? '-'));
                            $sheet->setCellValue("E{$row}", $ikan->nomor_tank ? 'Tank ' . $ikan->nomor_tank : '-');
                            $sheet->setCellValue("F{$row}", $s->total_nilai ?? 0);
                            $sheet->setCellValue("G{$row}", 'Ya');
                            $sheet->setCellValue("H{$row}", $isLocked ? 'Ya (FINAL)' : 'Belum');
                            $sheet->setCellValue("I{$row}", $s->created_at ? $s->created_at->format('d-m-Y H:i:s') : '-');
                            $sheet->setCellValue("J{$row}", $s->updated_at ? $s->updated_at->format('d-m-Y H:i:s') : '-');
                            $row++;
                        }
                        $endData = $row - 1;
                        $sheet->getStyle("A{$startData}:J{$endData}")->applyFromArray($styleBorder);

                        for ($r = $startData; $r <= $endData; $r++) {
                            if (($r - $startData) % 2 === 1) {
                                $sheet->getStyle("A{$r}:J{$r}")->applyFromArray([
                                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F8FAFC']],
                                ]);
                            }
                            // Warna kolom DIKUNCI
                            $lockVal = $sheet->getCell("H{$r}")->getValue();
                            if ($lockVal && str_contains($lockVal, 'Ya')) {
                                $sheet->getStyle("H{$r}")->applyFromArray([
                                    'font' => ['bold' => true, 'color' => ['rgb' => '15803D']],
                                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'DCFCE7']],
                                ]);
                            }
                        }
                    }
                    $row += 2;
                }

                // ═══════════════════════════════════════════════════════
                // SECTION 4: RIWAYAT INTERVENSI GRAND JURI
                // ═══════════════════════════════════════════════════════
                $grandJuris = User::where('role', 'grand_juri')->orderBy('name')->get();

                $sheet->mergeCells("A{$row}:J{$row}");
                $sheet->setCellValue("A{$row}", 'RIWAYAT INTERVENSI GRAND JURI');
                $sheet->getStyle("A{$row}:J{$row}")->applyFromArray($styleTitle);
                $row++;

                $sheet->mergeCells("A{$row}:J{$row}");
                $sheet->setCellValue("A{$row}", 'Setiap perubahan nilai oleh Grand Juri terhadap penilaian Juri dicatat lengkap di sini');
                $sheet->getStyle("A{$row}:J{$row}")->applyFromArray($styleSubTitle);
                $row += 2;

                foreach ($grandJuris as $gj) {
                    $sheet->mergeCells("A{$row}:J{$row}");
                    $sheet->setCellValue("A{$row}", 'GRAND JURI: ' . $gj->name . ' (' . $gj->email . ')');
                    $sheet->getStyle("A{$row}:J{$row}")->applyFromArray($styleBlockHeader);
                    $row++;

                    $headers4 = ['NO', 'PESERTA', 'NILAI SEBELUM', 'NILAI SESUDAH', 'SELISIH (+/-)', 'KOMPONEN YANG DIUBAH', 'WAKTU EDIT', 'STATUS KUNCI', '', ''];
                    $sheet->fromArray([$headers4], null, "A{$row}");
                    $sheet->getStyle("A{$row}:J{$row}")->applyFromArray($styleHeader);
                    $row++;

                    $edits = GrandJuriEdit::where('grand_juri_id', $gj->id)
                        ->with(['peserta', 'scoring.ikan'])  // ★ load ikan via scoring untuk snapshot
                        ->orderBy('created_at', 'desc')
                        ->get();

                    if ($edits->isEmpty()) {
                        $sheet->mergeCells("A{$row}:J{$row}");
                        $sheet->setCellValue("A{$row}", 'Tidak ada riwayat intervensi nilai.');
                        $sheet->getStyle("A{$row}:J{$row}")->getFont()->setItalic(true);;
                        $row++;
                    } else {
                        $no = 1;
                        $startData = $row;
                        foreach ($edits as $e) {
                            $selisih = ($e->total_sesudah ?? 0) - ($e->total_sebelum ?? 0);
                            $selisihStr = $selisih > 0 ? '+' . $selisih : (string) $selisih;

                            // Ringkasan komponen yang diubah
                            $changedStr = '—';
                            if ($e->changed_fields && is_array($e->changed_fields)) {
                                $parts = [];
                                foreach ($e->changed_fields as $kat => $fields) {
                                    if (is_array($fields)) {
                                        $parts[] = strtoupper($kat) . ': ' . implode(', ', array_keys($fields));
                                    }
                                }
                                if ($parts) $changedStr = implode('; ', $parts);
                            }

                            // Cek status kunci ikan
                            $isLocked = '—';
                            if ($e->peserta) {
                                $ikanLocked = \App\Models\Ikan::where('peserta_id', $e->peserta_id)
                                    ->where('is_locked', true)->exists();
                                $isLocked = $ikanLocked ? 'Terkunci' : 'Belum';
                            }

                            $sheet->setCellValue("A{$row}", $no++);
                            // ★ Snapshot ikan saat diedit; fallback ke Peserta terkini bila tidak ada
                            $snapshotName = $e->scoring?->ikan?->nama_peserta
                                         ?? $e->peserta?->nama_peserta
                                         ?? '-';
                            $sheet->setCellValue("B{$row}", $snapshotName);
                            $sheet->setCellValue("C{$row}", $e->total_sebelum ?? 0);
                            $sheet->setCellValue("D{$row}", $e->total_sesudah ?? 0);
                            $sheet->setCellValue("E{$row}", $selisihStr);
                            $sheet->setCellValue("F{$row}", $changedStr);
                            $sheet->setCellValue("G{$row}", $e->created_at->format('d-m-Y H:i:s'));
                            $sheet->setCellValue("H{$row}", $isLocked);
                            $row++;
                        }
                        $endData = $row - 1;
                        $sheet->getStyle("A{$startData}:H{$endData}")->applyFromArray($styleBorder);

                        // Alternating + warna selisih
                        for ($r = $startData; $r <= $endData; $r++) {
                            if (($r - $startData) % 2 === 1) {
                                $sheet->getStyle("A{$r}:H{$r}")->applyFromArray([
                                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F8FAFC']],
                                ]);
                            }
                            $selisihVal = $sheet->getCell("E{$r}")->getValue();
                            if (is_numeric($selisihVal)) {
                                if ($selisihVal > 0) {
                                    $sheet->getStyle("E{$r}")->applyFromArray([
                                        'font' => ['bold' => true, 'color' => ['rgb' => '15803D']],
                                        'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'DCFCE7']],
                                    ]);
                                } elseif ($selisihVal < 0) {
                                    $sheet->getStyle("E{$r}")->applyFromArray([
                                        'font' => ['bold' => true, 'color' => ['rgb' => 'DC2626']],
                                        'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FEE2E2']],
                                    ]);
                                }
                            }
                            // Warna status kunci
                            $lockVal = $sheet->getCell("H{$r}")->getValue();
                            if ($lockVal === 'Terkunci') {
                                $sheet->getStyle("H{$r}")->applyFromArray([
                                    'font' => ['bold' => true, 'color' => ['rgb' => '15803D']],
                                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'DCFCE7']],
                                ]);
                            }
                        }
                    }
                    $row += 2;
                }

                // ═══════════════════════════════════════════════════════
                // COLUMN WIDTHS
                // ═══════════════════════════════════════════════════════
                $widths = [
                    'A' => 6,   // NO
                    'B' => 24,  // NAMA
                    'C' => 30,  // EMAIL
                    'D' => 16,  // ROLE
                    'E' => 30,  // PASSWORD LAMA / NILAI SEBELUM
                    'F' => 30,  // PASSWORD BARU / NILAI SESUDAH
                    'G' => 24,  // DIUBAH OLEH / KOMPONEN
                    'H' => 18,  // ROLE PENGUBAH / WAKTU
                    'I' => 22,  // TANGGAL
                    'J' => 24,  // SELISIH / STATUS
                ];
                foreach ($widths as $col => $w) {
                    $sheet->getColumnDimension($col)->setAutoSize(false)->setWidth($w);
                }

                $sheet->freezePane('A2');
            },
        ];
    }
}