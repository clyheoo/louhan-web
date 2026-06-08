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
        // ★ Cari ikan_id yang pernah ditolak, ikan tersebut tidak berhak masuk penilaian
        $rejectedIkanIds = Nominasi::where('status', 'rejected')
            ->pluck('ikan_id')
            ->unique()
            ->toArray();

        // ★ Hanya ambil ikan_id yang approved DAN tidak ada di daftar rejected
        $approvedIkanIds = Nominasi::where('status', 'approved')
            ->whereNotIn('ikan_id', $rejectedIkanIds)
            ->pluck('ikan_id')
            ->unique()
            ->toArray();

        $hasApproved = count($approvedIkanIds) > 0;

        $availableTanks = Ikan::whereNotNull('nomor_tank')
            ->when($hasApproved, function ($q) use ($approvedIkanIds) {
                $q->whereIn('id', $approvedIkanIds);
            })
            ->with('peserta')
            ->orderBy('nomor_tank')
            ->get();

        $myScores = Scoring::where('juri_id', auth()->id())
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

        $myScoredTankIds = Scoring::where('juri_id', auth()->id())
            ->pluck('ikan_id')
            ->toArray();

        $allScored = [];
        foreach ($myScoredTankIds as $tankId) {
            $allScored[$tankId] = ['is_mine' => true];
        }

        $scoredCounts = Scoring::select('ikan_id', \DB::raw('COUNT(*) as total_juri'))
            ->groupBy('ikan_id')
            ->pluck('total_juri', 'ikan_id')
            ->toArray();

        // ★ Ambil defect yang dipilih saat nominasi (untuk pre-fill form scoring).
        //    Query langsung approved noms milik juri ini, TANPA filter $approvedIkanIds —
        //    karena filter itu bisa membuang ikan yang pernah ditolak lalu re-nominasi.
        //    Frontend hanya akan apply pre-fill untuk tank yang ada di tankScores
        //    (yaitu available_tanks), jadi data ekstra di nomination_defects aman.
        $nominationDefects = Nominasi::where('juri_id', auth()->id())
            ->where('status', 'approved')
            ->get(['ikan_id', 'raw_head_penalty', 'raw_face_penalty', 'raw_body_penalty', 'raw_finnage_penalty'])
            ->keyBy('ikan_id')
            ->map(function ($n) {
                return [
                    'raw_head_penalty'    => $n->raw_head_penalty    ?? ['0'],
                    'raw_face_penalty'    => $n->raw_face_penalty    ?? ['0'],
                    'raw_body_penalty'    => $n->raw_body_penalty    ?? ['0'],
                    'raw_finnage_penalty' => $n->raw_finnage_penalty ?? ['0'],
                ];
            });

        return response()->json([
            'available_tanks'    => $availableTanks,
            'my_scores'          => $myScores,
            'all_scored'         => $allScored,
            'scored_counts'      => $scoredCounts,
            'nomination_defects' => $nominationDefects,
        ]);
    }

    public function simpanNilai(Request $request)
    {
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

        if ($nominations->isEmpty()) {
            return response()->json([
                'status'            => 'none',
                'nominations'       => [],
                'approved_ikan_ids' => [],
            ]);
        }

        $hasPending  = $nominations->contains('status', 'pending');
        $hasApproved = $nominations->contains('status', 'approved');

        // ★ Prioritas: pending > approved > none
        //   Selama ada SATU PUN nominasi yang masih pending,
        //   juri TIDAK boleh masuk scoring page.
        //   Semua nominasi harus selesai di-review (approved/rejected) dulu.
        if ($hasPending) {
            $status = 'pending';
        } elseif ($hasApproved) {
            $status = 'approved';
        } else {
            $status = 'none';
        }

        $approvedIds = $nominations->where('status', 'approved')->pluck('ikan_id')->toArray();

        return response()->json([
            'status'            => $status,
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

        $ikans = Ikan::whereIn('id', $ikanIds)
            ->whereNotNull('nomor_tank')
            ->get();

        if ($ikans->count() !== count($ikanIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Beberapa tank tidak ditemukan atau belum memiliki nomor tank.',
            ], 422);
        }

        // ★ Multi-nominasi diperbolehkan: juri boleh kirim nominasi tambahan
        //   kapan saja, bahkan saat masih ada pending atau sudah ada approved.
        //   Setiap submission menjadi entry pending baru, di-review independen.

        // ★ Hapus nominasi PENDING yang TIDAK ada di submission baru
        //   (juri mungkin unselect tank yang sebelumnya pending)
        Nominasi::where('juri_id', auth()->id())
            ->where('status', 'pending')
            ->whereNotIn('ikan_id', $ikanIds)
            ->delete();

        // ★ Defect per ikan_id (opsional): { "12": {raw_head_penalty:[...], ...}, ... }
        $defects = $request->json('defects') ?? [];

        foreach ($ikanIds as $ikanId) {
            Nominasi::where('juri_id', auth()->id())
                ->where('ikan_id', $ikanId)
                ->delete();

            $payload = [
                'juri_id' => auth()->id(),
                'ikan_id' => $ikanId,
                'status'  => 'pending',
            ];

            // Defect dikirim bisa keyed by int atau string → cek dua-duanya
            $d = $defects[$ikanId] ?? $defects[(string) $ikanId] ?? null;
            if (is_array($d)) {
                $payload['raw_head_penalty']    = $d['raw_head_penalty']    ?? ['0'];
                $payload['raw_face_penalty']    = $d['raw_face_penalty']    ?? ['0'];
                $payload['raw_body_penalty']    = $d['raw_body_penalty']    ?? ['0'];
                $payload['raw_finnage_penalty'] = $d['raw_finnage_penalty'] ?? ['0'];
            }

            Nominasi::create($payload);
        }

        return response()->json([
            'success' => true,
            'message' => 'Nominasi berhasil dikirim! Menunggu review Grand Juri.',
            'count'   => count($ikanIds),
        ]);
    }

    /* ═══════════════════════════════════════════
       NOMINASI — AMBIL DATA TANK UNTUK GRID
       ═══════════════════════════════════════════ */
    public function getTanksForNominasi()
    {
        $tanks = Ikan::whereNotNull('nomor_tank')
            ->with('peserta')
            ->orderBy('nomor_tank')
            ->get()
            ->map(function ($ikan) {
                return [
                    'id'             => $ikan->id,
                    'nomor_tank'     => $ikan->nomor_tank,
                    'kategori'       => $ikan->kategori,
                    'kelas'          => $ikan->kelas,
                    'nama_peserta'   => $ikan->nama_peserta ?? 'Unknown',
                    'detail_anggota' => $ikan->peserta->detail_anggota ?? '—',
                ];
            });

        $kategoris = $tanks->pluck('kategori')->unique()->sort()->values();
        $kelass    = $tanks->pluck('kelas')->filter()->unique()->sort()->values();

        return response()->json([
            'tanks'     => $tanks,
            'kategoris' => $kategoris,
            'kelass'    => $kelass,
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