<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Scoring;
use App\Models\Ikan;
use App\Helpers\PointCalculator;
use App\Models\Nominasi;

class JuriController extends Controller
{
    public function getJuriData()
    {
        $juriId = auth()->id();

        $normalizeDefectArray = function ($value) {
            if (is_string($value)) {
                $value = [$value];
            }

            if (!is_array($value)) {
                return ['0'];
            }

            $items = collect($value)
                ->map(function ($v) {
                    $v = trim((string) $v);

                    // Normalisasi teks lama agar tampil & dihitung sebagai teks baru.
                    $v = preg_replace('/\s+Sempurna/u', '', $v) ?? $v;

                    return $v;
                })
                ->filter(fn ($v) => $v !== '')
                ->unique()
                ->values()
                ->toArray();

            if (empty($items)) {
                return ['0'];
            }

            if (in_array('0', $items, true) && count($items) > 1) {
                $items = array_values(array_filter($items, fn ($v) => $v !== '0'));
            }

            return empty($items) ? ['0'] : $items;
        };

        $mergeDefectRows = function ($rows) use ($normalizeDefectArray) {
            $keys = [
                'raw_head_penalty',
                'raw_face_penalty',
                'raw_body_penalty',
                'raw_finnage_penalty',
            ];

            $merged = [];

            foreach ($keys as $key) {
                $vals = [];

                foreach ($rows as $row) {
                    foreach ($normalizeDefectArray($row->{$key} ?? ['0']) as $v) {
                        if ($v && $v !== '0') {
                            $vals[$v] = true;
                        }
                    }
                }

                $merged[$key] = count($vals) ? array_values(array_keys($vals)) : ['0'];
            }

            return $merged;
        };

        // Ambil SEMUA tank yang sudah approved, baik dari admin, juri lain, maupun juri login.
        $approvedIkanIds = Nominasi::where('status', 'approved')
            ->pluck('ikan_id')
            ->unique()
            ->values()
            ->toArray();

        $availableTanks = Ikan::query()
            ->select([
                'id',
                'peserta_id',
                'nomor_tank',
                'kategori',
                'kelas',
                'nama_peserta',
                'detail_anggota',
                'jenis_keanggotaan',
                'is_locked',
            ])
            ->whereNotNull('nomor_tank')
            ->whereIn('id', $approvedIkanIds)
            ->orderByRaw('CAST(nomor_tank AS UNSIGNED) ASC')
            ->get();

        $myScores = Scoring::where('juri_id', $juriId)
            ->whereIn('ikan_id', $approvedIkanIds)
            ->with('ikan', 'ikan.peserta')
            ->orderByDesc('created_at')
            ->get();

        $firstScoringIds = Scoring::select('ikan_id', \DB::raw('MIN(id) as first_id'))
            ->groupBy('ikan_id')
            ->pluck('first_id', 'ikan_id')
            ->toArray();

        $myScores->transform(function ($s) use ($firstScoringIds) {
            $s->is_first_scorer = isset($firstScoringIds[$s->ikan_id]) && $firstScoringIds[$s->ikan_id] == $s->id;
            return $s;
        });

        $myScoredTankIds = Scoring::where('juri_id', $juriId)
            ->whereIn('ikan_id', $approvedIkanIds)
            ->pluck('ikan_id')
            ->toArray();

        $allScored = [];
        foreach ($myScoredTankIds as $tankId) {
            $allScored[$tankId] = ['is_mine' => true];
        }

        $scoredCounts = Scoring::select('ikan_id', \DB::raw('COUNT(*) as total_juri'))
            ->whereIn('ikan_id', $approvedIkanIds)
            ->groupBy('ikan_id')
            ->pluck('total_juri', 'ikan_id')
            ->toArray();

        // Gabungkan defect dari SEMUA nominasi approved untuk ikan yang sama.
        // Jadi defect admin + juri lain tetap ikut pre-fill ke halaman juri.
        $nominationDefects = Nominasi::where('status', 'approved')
            ->whereIn('ikan_id', $approvedIkanIds)
            ->get(['ikan_id', 'raw_head_penalty', 'raw_face_penalty', 'raw_body_penalty', 'raw_finnage_penalty'])
            ->groupBy('ikan_id')
            ->map(function ($rows) use ($mergeDefectRows) {
                return $mergeDefectRows($rows);
            });

        return response()->json([
            'available_tanks'    => $availableTanks,
            'my_scores'          => $myScores,
            'all_scored'         => $allScored,
            'scored_counts'      => $scoredCounts,
            'nomination_defects' => $nominationDefects,
            'approved_ikan_ids'  => $approvedIkanIds,
        ]);
    }

    public function simpanNilai(Request $request)
    {
        // ★ CEGAH PENYIMPANAN JIKA PENILAIAN MASIH TERKUNCI OLEH ADMIN
        $scoringUnlocked = (bool) (\DB::table('settings')->where('key', 'scoring_unlocked')->value('value') ?? false);
        if (!$scoringUnlocked) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi penjurian masih TERKUNCI. Admin belum membuka akses penilaian.',
            ], 403);
        }

        $data       = $request->json()->all();
        $ikanId     = $data['ikan_id'] ?? null;
        $kelas      = $data['kelas'] ?? null;
        $allScores  = $data['all_scores'] ?? null;
        $defectData = $data['defect_data'] ?? null;
        
        if (!$ikanId || !$allScores) {
            return response()->json(['success' => false, 'message' => 'Data tidak lengkap.'], 422);
        }

        $ikan = Ikan::find($ikanId);
        if (!$ikan) {
            return response()->json(['success' => false, 'message' => 'Data ikan tidak ditemukan.'], 422);
        }

        $hasApprovedNominasi = Nominasi::where('ikan_id', $ikanId)
            ->where('status', 'approved')
            ->exists();

        if (!$hasApprovedNominasi) {
            return response()->json([
                'success' => false,
                'message' => 'Nominasi untuk tank ini sudah tidak aktif atau telah dihapus admin. Halaman akan diperbarui.',
                'nomination_removed' => true,
            ], 409);
        }

        $noKelasKategori = ['Bonsai', 'Jumbo'];
        $needKelas = !in_array($ikan->kategori, $noKelasKategori);

        if ($needKelas && !$kelas) {
            return response()->json(['success' => false, 'message' => 'Data tidak lengkap (Kelas wajib dipilih).'], 422);
        }

        if (is_string($allScores)) {
            $allScores = json_decode($allScores, true);
        }

        if (!is_array($allScores)) {
            return response()->json(['success' => false, 'message' => 'Format all_scores tidak valid.'], 422);
        }

        // ★ Paksa nilai 0 untuk komponen yang terkunci berdasarkan kategori (Keamanan Backend)
        $noMarking = in_array($ikan->kategori, ['Freemarking', 'Goldenbase']);
        $noPearl   = $ikan->kategori === 'Klasik';

        if ($noMarking && isset($allScores['marking'])) {
            foreach ($allScores['marking'] as $k => $v) {
                if ($k !== 'defect') $allScores['marking'][$k] = 0;
            }
        }
        if ($noPearl && isset($allScores['pearl'])) {
            foreach ($allScores['pearl'] as $k => $v) {
                if ($k !== 'defect') $allScores['pearl'][$k] = 0;
            }
        }

        $totalNilai = 0;
        foreach ($allScores as $detailNilai) {
            if (is_array($detailNilai)) {
                foreach ($detailNilai as $key => $nilai) {
                    // ★ Skip field defect
                    if ($key === 'defect') continue;
                    $totalNilai += (int)$nilai;
                }
            }
        }

        $myExisting = Scoring::where('ikan_id', $ikanId)
            ->where('juri_id', auth()->id())
            ->first();

        if ($myExisting) {
            return response()->json([
                'success' => false,
                'message' => 'ANDA SUDAH MENILAI peserta ini. Nilai Anda sudah tersimpan dan tidak dapat diubah.'
            ]);
        }
        
        $totalPoint = PointCalculator::hitungPoint($ikan->kategori, $allScores, $defectData);

        $scoringData = [
            'ikan_id'      => $ikanId,
            'juri_id'      => auth()->id(),
            'kelas'        => $kelas,
            'nilai_detail' => $allScores,
            'total_nilai'  => $totalNilai,
            'total_point'  => $totalPoint,
            'status'       => 'submitted'
        ];

        // ★ SIMPAN DEFECT DATA JIKA ADA
        if ($defectData) {
            // Validasi dan gunakan hasil evaluasi dari backend
            $evaluated = PointCalculator::evaluateDefects($defectData);
            
            $scoringData['raw_head_penalty']    = $defectData['raw_head_penalty'] ?? ['0'];
            $scoringData['raw_face_penalty']    = $defectData['raw_face_penalty'] ?? ['0'];
            $scoringData['raw_body_penalty']    = $defectData['raw_body_penalty'] ?? ['0'];
            $scoringData['raw_finnage_penalty'] = $defectData['raw_finnage_penalty'] ?? ['0'];
            $scoringData['keterangan']          = $evaluated['keterangan'] ?? '';
        }

        Scoring::create($scoringData);

                // ★ AUTO-SYNC dijalankan SETELAH response dikirim ke browser.
        //   Outer try/catch melindungi bila terminating() sendiri error.
        //   Catch \Throwable agar Error/TypeError tidak bocor ke response.
        try {
            app()->terminating(function () {
                try {
                    $sync = app(\App\Services\SheetsSyncService::class);
                    if (!$sync->isReady()) return;
                    try { $sync->syncCnt();       } catch (\Throwable $e) { \Log::error('Async-sync CNT (simpan): '       . $e->getMessage()); }
                    try { $sync->syncHasilJuri(); } catch (\Throwable $e) { \Log::error('Async-sync HasilJuri (simpan): ' . $e->getMessage()); }
                    try { $sync->syncNilaiJuri(); } catch (\Throwable $e) { \Log::error('Async-sync NilaiJuri (simpan): ' . $e->getMessage()); }
                } catch (\Throwable $e) {
                    \Log::error('Async-sync outer (simpan): ' . $e->getMessage());
                }
            });
        } catch (\Throwable $e) {
            \Log::error('Gagal register terminating (simpan): ' . $e->getMessage());
        }

        return response()->json(['success' => true, 'message' => 'Nilai berhasil disimpan!']);
    }

    public function kirimKeGrandJuri(Request $request)
    {
        $scoringId = $request->json('scoring_id');

        if (!$scoringId) {
            return response()->json(['success' => false, 'message' => 'Scoring ID wajib dikirim.'], 422);
        }

        $scoring = Scoring::where('id', $scoringId)
            ->where('juri_id', auth()->id())
            ->first();

        if (!$scoring) {
            return response()->json(['success' => false, 'message' => 'Data penilaian tidak ditemukan.'], 404);
        }

        if ($scoring->submitted_to_grand) {
            return response()->json(['success' => false, 'message' => 'Nilai ini sudah pernah dikirim ke Grand Juri.']);
        }

        $hasApprovedNominasi = Nominasi::where('ikan_id', $scoring->ikan_id)
            ->where('status', 'approved')
            ->exists();

        if (!$hasApprovedNominasi) {
            return response()->json([
                'success' => false,
                'message' => 'Nominasi untuk tank ini sudah tidak aktif atau telah dihapus admin. Nilai tidak dapat dikirim ke Grand Juri.',
                'nomination_removed' => true,
            ], 409);
        }

        $scoring->update(['submitted_to_grand' => true]);

        // ★ AUTO-SYNC dijalankan SETELAH response dikirim ke browser
        try {
            app()->terminating(function () {
                try {
                    $sync = app(\App\Services\SheetsSyncService::class);
                    if (!$sync->isReady()) return;
                    try { $sync->syncCnt();       } catch (\Throwable $e) { \Log::error('Async-sync CNT (kirim): '       . $e->getMessage()); }
                    try { $sync->syncHasilJuri(); } catch (\Throwable $e) { \Log::error('Async-sync HasilJuri (kirim): ' . $e->getMessage()); }
                    try { $sync->syncNilaiJuri(); } catch (\Throwable $e) { \Log::error('Async-sync NilaiJuri (kirim): ' . $e->getMessage()); }
                } catch (\Throwable $e) {
                    \Log::error('Async-sync outer (kirim): ' . $e->getMessage());
                }
            });
        } catch (\Throwable $e) {
            \Log::error('Gagal register terminating (kirim): ' . $e->getMessage());
        }

        return response()->json(['success' => true, 'message' => 'Nilai berhasil dikirim ke Grand Juri.']);
    }

        /* ═══════════════════════════════════════════
       NOMINASI — CEK STATUS
       ═══════════════════════════════════════════ */
    public function getNominasiStatus()
    {
        $nominations = Nominasi::where('juri_id', auth()->id())
            ->with('ikan.peserta')
            ->orderByDesc('created_at')
            ->get();

        $globalApprovedIds = Nominasi::where('status', 'approved')
            ->pluck('ikan_id')
            ->unique()
            ->values()
            ->toArray();

        $scoringUnlocked = (bool) (\DB::table('settings')->where('key', 'scoring_unlocked')->value('value') ?? false);

        if ($nominations->isEmpty()) {
            return response()->json([
                'status'            => count($globalApprovedIds) > 0 ? 'approved' : 'none',
                'scoring_unlocked'  => $scoringUnlocked,
                'nominations'       => [],
                'approved_ikan_ids' => $globalApprovedIds,
            ]);
        }

        $hasPending  = $nominations->contains('status', 'pending');
        $hasApproved = $nominations->contains('status', 'approved');
        $hasRejected = $nominations->contains('status', 'rejected');

        // Prioritas status milik juri login:
        // pending → approved → rejected.
        // Approved global hanya dipakai kalau juri login belum punya status sendiri.
        if ($hasPending) {
            $status = 'pending';
        } elseif ($hasApproved) {
            $status = 'approved';
        } elseif ($hasRejected) {
            $status = 'none';
        } elseif (count($globalApprovedIds) > 0) {
            $status = 'approved';
        } else {
            $status = 'none';
        }

        $approvedIds = $globalApprovedIds;

        // ★ Cek apakah admin sudah membuka kunci penjurian
        $scoringUnlocked = (bool) (\DB::table('settings')->where('key', 'scoring_unlocked')->value('value') ?? false);

        return response()->json([
            'status'            => $status,
            'scoring_unlocked'  => $scoringUnlocked,
            'nominations'       => $nominations->map(function ($n) {
                return [
                    'id'            => $n->id,
                    'ikan_id'       => $n->ikan_id,
                    'nomor_tank'    => $n->ikan->nomor_tank ?? null,
                    'kategori'      => $n->ikan->kategori ?? null,
                    'kelas'         => $n->ikan->kelas ?? null,
                    'nama_peserta'  => $n->ikan->nama_peserta ?? 'Unknown',
                    'status'        => $n->status,
                    'catatan'       => $n->catatan,
                    'reviewed_at'   => $n->reviewed_at?->toISOString(),
                    // ★ Defect data untuk pre-fill saat kembali ke nominasi
                    'raw_head_penalty'    => $n->raw_head_penalty ?? ['0'],
                    'raw_face_penalty'    => $n->raw_face_penalty ?? ['0'],
                    'raw_body_penalty'    => $n->raw_body_penalty ?? ['0'],
                    'raw_finnage_penalty' => $n->raw_finnage_penalty ?? ['0'],
                ];
            }),
            'approved_ikan_ids' => $approvedIds,
        ]);
    }

    /* ═══════════════════════════════════════════
       NOMINASI — KIRIM
       ═══════════════════════════════════════════ */
    public function submitNominasi(Request $request)
    {
        $ikanIds = $request->json('ikan_ids');

        if (!is_array($ikanIds) || count($ikanIds) === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Pilih minimal 1 tank untuk dinominasikan.',
            ], 422);
        }

        $ikanIds = collect($ikanIds)
            ->map(fn($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $ikans = Ikan::whereIn('id', $ikanIds)
            ->whereNotNull('nomor_tank')
            ->pluck('id')
            ->toArray();

        if (count($ikans) !== count($ikanIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Beberapa tank tidak ditemukan atau belum memiliki nomor tank.',
            ], 422);
        }

        $juriId  = auth()->id();
        $defects = $request->json('defects') ?? [];

        $createdOrUpdated = 0;
        $skippedApproved  = 0;

        \DB::transaction(function () use ($ikanIds, $juriId, $defects, &$createdOrUpdated, &$skippedApproved) {
            // Tank pending yang tidak ikut submission baru dianggap dibatalkan.
            Nominasi::where('juri_id', $juriId)
                ->where('status', 'pending')
                ->whereNotIn('ikan_id', $ikanIds)
                ->delete();

            foreach ($ikanIds as $ikanId) {

            // Approved milik admin/juri lain tetap boleh dikirim sebagai pending oleh juri ini.
            $alreadyApproved = Nominasi::where('juri_id', $juriId)
                ->where('ikan_id', $ikanId)
                ->where('status', 'approved')
                ->exists();

                if ($alreadyApproved) {
                    $skippedApproved++;
                    continue;
                }

                $payload = [
                    'juri_id' => $juriId,
                    'ikan_id' => $ikanId,
                    'status'  => 'pending',
                ];

                $d = $defects[$ikanId] ?? $defects[(string) $ikanId] ?? null;

                if (is_array($d)) {
                    $payload['raw_head_penalty']    = $d['raw_head_penalty']    ?? ['0'];
                    $payload['raw_face_penalty']    = $d['raw_face_penalty']    ?? ['0'];
                    $payload['raw_body_penalty']    = $d['raw_body_penalty']    ?? ['0'];
                    $payload['raw_finnage_penalty'] = $d['raw_finnage_penalty'] ?? ['0'];
                }

                // Hapus pending/rejected lama milik juri ini untuk ikan yang sama,
                // lalu buat pending baru. Approved tidak disentuh.
                Nominasi::where('juri_id', $juriId)
                    ->where('ikan_id', $ikanId)
                    ->whereIn('status', ['pending', 'rejected'])
                    ->delete();

                Nominasi::create($payload);
                $createdOrUpdated++;
            }
        });

        // Sync spreadsheet setelah response agar tombol kirim tidak lama.
        try {
            app()->terminating(function () {
                try {
                    $sync = app(\App\Services\SheetsSyncService::class);
                    if (!$sync->isReady()) return;

                    try { $sync->syncSemuaNominasi(); } catch (\Throwable $e) { \Log::error('Async-sync SemuaNominasi submit nominasi: ' . $e->getMessage()); }
                    try { $sync->syncSemuaPilNom(); } catch (\Throwable $e) { \Log::error('Async-sync SemuaPilNom submit nominasi: ' . $e->getMessage()); }
                    try { $sync->syncHasilNominasi(); } catch (\Throwable $e) { \Log::error('Async-sync HasilNominasi submit nominasi: ' . $e->getMessage()); }
                } catch (\Throwable $e) {
                    \Log::error('Async-sync outer submit nominasi: ' . $e->getMessage());
                }
            });
        } catch (\Throwable $e) {
            \Log::error('Gagal register async submit nominasi: ' . $e->getMessage());
        }

        $msg = $createdOrUpdated . ' nominasi berhasil dikirim dan menunggu review Grand Juri.';
        if ($skippedApproved > 0) {
            $msg .= ' ' . $skippedApproved . ' tank yang sudah approved dilewati.';
        }

        return response()->json([
            'success' => true,
            'message' => $msg,
            'count'   => $createdOrUpdated,
            'skipped_approved' => $skippedApproved,
        ]);
    }

    public function getTanksForNominasi()
    {
        $juriId = auth()->id();

        // Pending milik juri ini harus tetap muncul dan otomatis terpilih di frontend.
        $pendingNominations = Nominasi::where('juri_id', $juriId)
            ->where('status', 'pending')
            ->get();

        $pendingIkanIds = $pendingNominations
            ->pluck('ikan_id')
            ->unique()
            ->values()
            ->toArray();

        // Approved/rejected milik juri ini tidak perlu muncul lagi di pilihan nominasi.
        // Pending jangan di-exclude, karena harus tampil paling atas.
        $excludedIkanIds = Nominasi::where('juri_id', $juriId)
            ->whereIn('status', ['approved', 'rejected'])
            ->pluck('ikan_id')
            ->unique()
            ->values()
            ->toArray();

        $pendingSet = array_flip($pendingIkanIds);

        $query = Ikan::query()
            ->select([
                'id',
                'nomor_tank',
                'kategori',
                'kelas',
                'nama_peserta',
                'detail_anggota',
            ])
            ->whereNotNull('nomor_tank');

        if (!empty($excludedIkanIds)) {
            $query->whereNotIn('id', $excludedIkanIds);
        }

        // PENTING:
        // Jangan pakai orderByRaw('0') saat tidak ada pending.
        // MySQL akan membaca "0" sebagai nama kolom dan menyebabkan error.
        if (!empty($pendingIkanIds)) {
            $safePendingIds = implode(',', array_map('intval', $pendingIkanIds));

            $query->orderByRaw("CASE WHEN id IN ($safePendingIds) THEN 0 ELSE 1 END");
        }

        $tanks = $query
            ->orderByRaw('CAST(nomor_tank AS UNSIGNED) ASC')
            ->get()
            ->map(function ($ikan) use ($pendingSet) {
                return [
                    'id'             => $ikan->id,
                    'nomor_tank'     => $ikan->nomor_tank,
                    'kategori'       => $ikan->kategori,
                    'kelas'          => $ikan->kelas,
                    'nama_peserta'   => $ikan->nama_peserta ?? 'Unknown',
                    'detail_anggota' => $ikan->detail_anggota ?? '—',
                    'is_pending'     => isset($pendingSet[$ikan->id]),
                ];
            })
            ->values();

        $pendingDefects = [];

        foreach ($pendingNominations as $n) {
            $pendingDefects[$n->ikan_id] = [
                'raw_head_penalty'    => $n->raw_head_penalty    ?? ['0'],
                'raw_face_penalty'    => $n->raw_face_penalty    ?? ['0'],
                'raw_body_penalty'    => $n->raw_body_penalty    ?? ['0'],
                'raw_finnage_penalty' => $n->raw_finnage_penalty ?? ['0'],
            ];
        }

        $kategoris = $tanks
            ->pluck('kategori')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $kelass = $tanks
            ->pluck('kelas')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        return response()->json([
            'tanks'            => $tanks,
            'kategoris'        => $kategoris,
            'kelass'           => $kelass,
            'pending_ikan_ids' => $pendingIkanIds,
            'pending_defects'  => $pendingDefects,
            'server_time'      => now()->toDateTimeString(),
        ]);
    }

    /* ═══════════════════════════════════════════
       NOMINASI — CANCEL (juri batalkan nominasi pending)
       ═══════════════════════════════════════════ */
    public function cancelNominasi(Request $request)
    {
        $nominasiId = $request->json('nominasi_id');

        if (!$nominasiId) {
            return response()->json(['success' => false, 'message' => 'Nominasi ID wajib dikirim.'], 422);
        }

        $nominasi = Nominasi::where('id', $nominasiId)
            ->where('juri_id', auth()->id())
            ->first();

        if (!$nominasi) {
            return response()->json([
                'success' => false,
                'message' => 'Nominasi tidak ditemukan atau bukan milik Anda.',
            ], 404);
        }

        if ($nominasi->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya nominasi yang masih PENDING yang dapat dibatalkan. Status saat ini: ' . strtoupper($nominasi->status),
            ], 422);
        }

        $nomorTank = $nominasi->ikan->nomor_tank ?? '?';
        $nominasi->delete();

        // ★ Sync ke Google Sheets di background (kalau sync service tersedia)
        try {
            dispatch(function () {
                try {
                    $sync = app(\App\Services\SheetsSyncService::class);
                    if (!$sync->isReady()) return;
                    try { $sync->syncSemuaNominasi(); } catch (\Throwable $e) { \Log::error('Async-sync SemuaNominasi (cancel): ' . $e->getMessage()); }
                    try { $sync->syncSemuaPilNom();   } catch (\Throwable $e) { \Log::error('Async-sync SemuaPilNom (cancel): '   . $e->getMessage()); }
                } catch (\Throwable $e) {
                    \Log::error('Async-sync outer (cancel nominasi): ' . $e->getMessage());
                }
            })->afterResponse();
        } catch (\Throwable $e) {
            \Log::error('Gagal register dispatch (cancel nominasi): ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Nominasi Tank ' . $nomorTank . ' berhasil dibatalkan.',
        ]);
    }
}