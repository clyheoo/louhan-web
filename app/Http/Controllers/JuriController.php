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
        $approvedIkanIds = Nominasi::where('status', 'approved')
            ->pluck('ikan_id')
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

        return response()->json([
            'available_tanks' => $availableTanks,
            'my_scores'       => $myScores,
            'all_scored'      => $allScored,
            'scored_counts'   => $scoredCounts,
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

        // ★ AUTO-SYNC HASIL JURI
        try { 
            app(\App\Services\SheetsSyncService::class)->syncHasilJuri(); 
        } catch (\Exception $e) { 
            \Log::error('Auto-sync hasil juri gagal (kirim): ' . $e->getMessage()); 
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
                    'nama_peserta'  => $n->ikan->peserta->nama_peserta ?? 'Unknown',
                    'status'        => $n->status,
                    'catatan'       => $n->catatan,
                    'reviewed_at'   => $n->reviewed_at?->toISOString(),
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

        $hasPending = Nominasi::where('juri_id', auth()->id())
            ->where('status', 'pending')
            ->exists();

        if ($hasPending) {
            return response()->json([
                'success' => false,
                'message' => 'Anda masih memiliki nominasi yang sedang ditinjau Grand Juri. Tunggu hingga selesai.',
            ], 422);
        }

        $hasApproved = Nominasi::where('juri_id', auth()->id())
            ->where('status', 'approved')
            ->exists();

        if ($hasApproved) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah memiliki nominasi yang disetujui.',
            ], 422);
        }

        foreach ($ikanIds as $ikanId) {
            Nominasi::where('juri_id', auth()->id())
                ->where('ikan_id', $ikanId)
                ->delete();

            Nominasi::create([
                'juri_id' => auth()->id(),
                'ikan_id' => $ikanId,
                'status'  => 'pending',
            ]);
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
                    'nama_peserta'   => $ikan->peserta->nama_peserta ?? 'Unknown',
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
}