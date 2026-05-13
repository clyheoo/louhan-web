<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Scoring;
use App\Models\Peserta;
use App\Models\Ikan;
use App\Models\GrandJuriEdit;

class GrandJuriController extends Controller
{
    public function index()
    {
        return view('dashboard.grand-juri', ['user' => auth()->user()->fresh()]);
    }

    /* ═══════════════════════════════════════════
       STATS (UPDATE KE TABEL IKANS)
       ═══════════════════════════════════════════ */
    public function getStats()
    {
        $totalTank = Ikan::whereNotNull('nomor_tank')->count();
        $totalPeserta = Peserta::count();

        $sudahPlot = Scoring::where('submitted_to_grand', true)
            ->distinct('ikan_id')
            ->count('ikan_id');
        $belumPlot = max(0, $totalTank - $sudahPlot);
        $maxTank = (int) (\DB::table('settings')->where('key', 'tank_range_max')->value('value') ?? 1000);
        $sisaTank = max(0, $maxTank - $totalTank);

        // ★ FIX: Hanya hitung ikan yang sudah submitted_to_grand, tanpa belum_tank
        $rincian = Ikan::whereNotNull('nomor_tank')
            ->whereHas('scorings', function ($q) {
                $q->where('submitted_to_grand', true);
            })
            ->selectRaw('ikans.kategori, COUNT(ikans.id) as ekor')
            ->groupBy('ikans.kategori')
            ->orderByDesc('ekor')
            ->get();

        return response()->json([
            'total_tank'    => $totalTank,
            'total_peserta' => $totalPeserta,
            'sudah_plot'    => $sudahPlot,
            'belum_plot'    => $belumPlot,
            'sisa_tank'     => $sisaTank,
            'max_tank'      => $maxTank,
            'rincian'       => $rincian,
        ]);
    }

    public function getPeserta(Request $request)
    {
        $query = Ikan::whereHas('scorings', function ($q) {
            $q->where('submitted_to_grand', true);
        })
            ->with(['peserta', 'scorings' => function ($q) {
                $q->where('submitted_to_grand', true);
            }, 'scorings.juri', 'scorings.grandJuri']);

        if ($request->filled('search')) {
            $query->whereHas('peserta', function ($q) use ($request) {
                $q->where('nama_peserta', 'LIKE', '%' . $request->search . '%');
            });
        }

        if ($request->filled('id')) {
            $query->where('id', $request->id);
        }

        $data = $query->orderBy('nomor_tank')->get()->map(function ($ikan) {
            $peserta = $ikan->peserta;
            $scorings = $ikan->scorings;

            $juriList = [];
            $grandJuriName = null;
            $latestNilai = null;
            $latestTotal = 0;
            $latestKelas = null;

            foreach ($scorings as $s) {
                if ($s->juri) {
                    // ★ FIX: is_grand cek role user, bukan flag edited_by_grand_juri
                    $juriList[] = [
                        'name'     => $s->juri->name,
                        'is_grand' => ($s->juri->role === 'grand_juri'),
                    ];
                }
                if ($s->edited_by_grand_juri && $s->grandJuri) {
                    $grandJuriName = $s->grandJuri->name;
                    $latestNilai = $s->nilai_detail;
                    $latestTotal = $s->total_nilai ?? 0;
                    $latestKelas = $s->kelas;
                }
            }

            if (!$latestNilai && $scorings->isNotEmpty()) {
                $latest = $scorings->last();
                $latestNilai = $latest->nilai_detail;
                $latestTotal = $latest->total_nilai ?? 0;
                $latestKelas = $latest->kelas;
            }

            // ★ Hitung total juri aktif dan yang sudah kirim
            $totalJuriAll = \App\Models\User::where('role', 'juri')->count();
            $submittedCount = $scorings->count();

            // ★ Kumpulkan semua scoring untuk detail modal (per-juri)
            $allScoringsData = $scorings->map(function ($s) {
                return [
                    'juri_name'    => $s->juri ? $s->juri->name : '—',
                    'is_grand'     => ($s->juri && $s->juri->role === 'grand_juri'),
                    'nilai_detail' => $s->nilai_detail,
                    'total_nilai'  => $s->total_nilai ?? 0,
                ];
            })->values()->toArray();

            return [
                'id'               => $ikan->id,
                'nama_peserta'     => $peserta->nama_peserta ?? 'Unknown',
                'kategori'         => $ikan->kategori,
                'kelas'            => $latestKelas ?? $ikan->kelas,
                'nomor_tank'       => $ikan->nomor_tank,
                'detail_anggota'   => $peserta->detail_anggota ?? '—',
                'juri_list'        => $juriList,
                'grand_juri_nama'  => $grandJuriName,
                'nilai_detail'     => $latestNilai,
                'total_nilai'      => $latestTotal,
                'total_juri'       => $scorings->count(),
                'is_locked'        => (bool) ($ikan->is_locked ?? false),
                'status'           => $ikan->is_locked
                                    ? 'NILAI FINAL (TERKUNCI)'
                                    : ($grandJuriName ? 'Diubah Grand Juri' : 'Sudah Dinilai'),
                'status_class'     => $ikan->is_locked
                                    ? 'badge-success'
                                    : ($grandJuriName ? 'badge-warning' : 'badge-success'),
                'total_juri_all'       => $totalJuriAll,
                'submitted_juri_count' => $submittedCount,
                'all_scorings'         => $allScoringsData,
            ];
        });

        return response()->json($data);
    }

    /* ═══════════════════════════════════════════
       EDIT NILAI (UPDATE: BERDASARKAN IKAN_ID)
       ═══════════════════════════════════════════ */
    public function editNilai(Request $request)
    {
        $data      = $request->json()->all();
        $ikanId    = $data['ikan_id'] ?? null;
        $changed   = $data['changed_fields'] ?? null;

        $ikan = Ikan::find($ikanId);

        if (!$ikanId || !$ikan) {
            return response()->json(['success' => false, 'message' => 'Data ikan tidak ditemukan.'], 422);
        }

        if ($ikan->is_locked) {
            return response()->json(['success' => false, 'message' => 'Nilai sudah TERKUNCI (FINAL) dan tidak dapat diubah.']);
        }

        /* ── KASUS 1: Sudah ada scoring dari juri ── */
        $existing = Scoring::with('ikan')->where('ikan_id', $ikanId)->latest()->first();

        if ($existing && $existing->nilai_detail) {
            $finalScores = $existing->nilai_detail;
            foreach ($changed as $kat => $fields) {
                if (!is_array($fields)) continue;
                foreach ($fields as $fieldId => $value) {
                    if (is_numeric($value)) {
                        if (!isset($finalScores[$kat])) $finalScores[$kat] = [];
                        $finalScores[$kat][$fieldId] = (int) $value;
                    }
                }
            }

            $totalNilai = 0;
            foreach ($finalScores as $katDetail) {
                if (is_array($katDetail)) {
                    foreach ($katDetail as $val) $totalNilai += (int) $val;
                }
            }

            $totalSebelum = $existing->total_nilai ?? 0;

            $existing->update([
                'grand_juri_id'        => auth()->id(),
                'nilai_detail'         => $finalScores,
                'total_nilai'          => $totalNilai,
                'edited_by_grand_juri' => true,
                'status'               => 'submitted',
            ]);

            GrandJuriEdit::create([
                'scoring_id'     => $existing->id,
                'peserta_id'     => $existing->ikan ? $existing->ikan->peserta_id : null,
                'grand_juri_id'  => auth()->id(),
                'nilai_sebelum'  => $existing->getRawOriginal('nilai_detail'),
                'nilai_sesudah'  => $finalScores,
                'changed_fields' => $changed,
                'total_sebelum'  => $totalSebelum,
                'total_sesudah'  => $totalNilai,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Nilai berhasil diperbarui oleh Grand Juri!',
                'total'   => $totalNilai,
            ]);
        }

        /* ── KASUS 2: Belum ada scoring sama sekali ── */
        $ikan = Ikan::find($ikanId);
        $totalNilai = 0;
        foreach ($changed as $fields) {
            if (is_array($fields)) {
                foreach ($fields as $val) $totalNilai += (int) $val;
            }
        }

        $newScoring = Scoring::create([
            'ikan_id'              => $ikanId,
            'juri_id'              => auth()->id(),
            'grand_juri_id'        => auth()->id(),
            'nilai_detail'         => $changed,
            'total_nilai'          => $totalNilai,
            'status'               => 'submitted',
            'edited_by_grand_juri' => true,
        ]);

        GrandJuriEdit::create([
            'scoring_id'     => $newScoring->id,
            'peserta_id'     => $ikan->peserta_id,
            'grand_juri_id'  => auth()->id(),
            'nilai_sebelum'  => null,
            'nilai_sesudah'  => $changed,
            'changed_fields' => $changed,
            'total_sebelum'  => 0,
            'total_sesudah'  => $totalNilai,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Nilai baru berhasil disimpan oleh Grand Juri!',
            'total'   => $totalNilai,
        ]);
    }

    public function kunciNilai(Request $request)
    {
        $ikanId = $request->json('ikan_id');

        if (!$ikanId) {
            return response()->json(['success' => false, 'message' => 'Ikan ID wajib dikirim.'], 422);
        }

        $ikan = Ikan::find($ikanId);
        if (!$ikan) {
            return response()->json(['success' => false, 'message' => 'Data ikan tidak ditemukan.'], 422);
        }

        $totalJuriAll = Scoring::where('ikan_id', $ikanId)
            ->where('submitted_to_grand', true)
            ->distinct('juri_id')
            ->count('juri_id');

        $submittedCount = Scoring::where('ikan_id', $ikanId)
            ->where('submitted_to_grand', true)
            ->count('ikan_id');

        if ($submittedCount < $totalJuriAll) {
            $sisa = $totalJuriAll - $submittedCount;
            return response()->json([
                'success' => false,
                'message' => 'Tidak bisa mengunci. Masih ada ' . $sisa . ' juri yang belum mengirim nilai.',
            ]);
        }


        $ikan->is_locked = !$ikan->is_locked;
        $ikan->save();

        return response()->json([
            'success'   => true,
            'message'   => $ikan->is_locked ? 'Nilai berhasil dikunci (FINAL).' : 'Nilai berhasil dibuka kembali.',
            'is_locked' => $ikan->is_locked,
        ]);
    }

    /* ═══════════════════════════════════════════
    JURI SUMMARY (DEFENSIF — TANPA EAGER LOADING)
    ═══════════════════════════════════════════ */
    public function getJuriSummary()
    {
        try {
            $merged = [];

            // --- Juri biasa (hanya yang sudah kirim ke Grand Juri) ---
            $juriRows = \DB::table('scorings')
                ->join('users', 'scorings.juri_id', '=', 'users.id')
                ->whereNotNull('scorings.juri_id')
                ->where('scorings.submitted_to_grand', true)
                ->selectRaw('scorings.juri_id, users.name, COUNT(DISTINCT scorings.ikan_id) as total_ikan')
                ->groupBy('scorings.juri_id', 'users.name')
                ->orderByDesc('total_ikan')
                ->get();

            foreach ($juriRows as $row) {
                $key = $row->name . '_juri';
                if (!isset($merged[$key])) {
                    $merged[$key] = [
                        'juri_id'       => $row->juri_id,
                        'name'          => $row->name,
                        'role'          => 'juri',
                        'total_peserta' => $row->total_ikan,
                    ];
                }
            }

            // --- Grand Juri yang pernah edit ---
            $hasEditedColumn = \Schema::hasColumn('scorings', 'edited_by_grand_juri');

            $grandQuery = \DB::table('scorings')
                ->join('users', 'scorings.grand_juri_id', '=', 'users.id')
                ->whereNotNull('scorings.grand_juri_id');

            if ($hasEditedColumn) {
                $grandQuery->where('scorings.edited_by_grand_juri', true);
            }

            $grandRows = $grandQuery
                ->selectRaw('scorings.grand_juri_id, users.name, COUNT(DISTINCT scorings.ikan_id) as total_ikan')
                ->groupBy('scorings.grand_juri_id', 'users.name')
                ->get();

            foreach ($grandRows as $row) {
                $key = $row->name . '_grand_juri';
                if (!isset($merged[$key])) {
                    $merged[$key] = [
                        'juri_id'       => $row->grand_juri_id,
                        'name'          => $row->name,
                        'role'          => 'grand_juri',
                        'total_peserta' => $row->total_ikan,
                    ];
                }
            }

            return response()->json(array_values($merged));

        } catch (\Exception $e) {
            return response()->json([
                'error'   => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /* ═══════════════════════════════════════════
    JURI PESERTA (Klik chip juri → lihat siapa saja yang dinilai)
    ═══════════════════════════════════════════ */
    public function getJuriPeserta(Request $request)
    {
        $juriId = $request->query('juri_id');
        $role   = $request->query('role', 'juri');

        if (!$juriId) {
            return response()->json(['error' => 'juri_id wajib diisi'], 422);
        }

        $query = Scoring::with('ikan.peserta');

        if ($role === 'grand_juri') {
            $query->where('grand_juri_id', $juriId)->where('edited_by_grand_juri', true);
        } else {
            $query->where('juri_id', $juriId);
        }

        $data = $query->orderBy('created_at', 'desc')->get()->map(function ($scoring) {
            return [
                'nama_peserta' => $scoring->ikan->peserta->nama_peserta ?? 'Unknown',
                'nomor_tank'   => $scoring->ikan->nomor_tank ?? '—',
                'kategori'     => $scoring->ikan->kategori ?? '—',
                'total_nilai'  => $scoring->total_nilai ?? 0,
            ];
        });

        return response()->json($data);
    }

    /* ═══════════════════════════════════════════
    RINCIAN DETAIL (Klik kartu kategori → sudah/belum dinilai)
    ═══════════════════════════════════════════ */
    public function getRincianDetail(Request $request)
    {
        $kategori = $request->query('kategori');

        if (!$kategori) {
            return response()->json(['error' => 'kategori wajib diisi'], 422);
        }

        // ★ FIX: Satu query saja, hanya ikan yang sudah submitted_to_grand
        $ikans = Ikan::whereNotNull('nomor_tank')
            ->whereHas('scorings', function ($q) {
                $q->where('submitted_to_grand', true);
            })
            ->where('kategori', $kategori)
            ->with(['peserta', 'scorings' => function ($q) {
                $q->where('submitted_to_grand', true)->latest()->limit(1);
            }, 'scorings.juri'])
            ->orderBy('nomor_tank')
            ->get();

        $data = [];
        foreach ($ikans as $ikan) {
            $data[] = [
                'nama_peserta' => $ikan->peserta->nama_peserta ?? 'Unknown',
                'nomor_tank'   => $ikan->nomor_tank,
                'juri_nama'    => $ikan->scorings->first()?->juri?->name ?? '—',
                'total_nilai'  => $ikan->scorings->first()?->total_nilai ?? 0,
            ];
        }

        return response()->json([
            'kategori'   => $kategori,
            'total_ekor' => $ikans->count(),
            'data'       => $data,
        ]);
    }

    /* ═══════════════════════════════════════════
    PLOT STATUS (Klik stat Sudah/Belum Plot → daftar peserta)
    ═══════════════════════════════════════════ */
    public function getPlotStatus(Request $request)
    {
        $status = $request->query('status');

        if (!in_array($status, ['sudah_plot', 'belum_plot'])) {
            return response()->json(['error' => 'status tidak valid'], 422);
        }

        $query = Ikan::where(function($q) {
            $q->whereNotNull('nomor_tank')
              ->orWhereHas('scorings');
        })
            ->with(['peserta', 'scorings' => function ($q) {
                $q->latest()->limit(1);
            }, 'scorings.juri']);

        if ($status === 'sudah_plot') {
            $query->whereHas('scorings', function ($q) {
                $q->where('submitted_to_grand', true);
            });
        } else {
            $query->whereDoesntHave('scorings');
        }
        $data = $query->orderBy('nomor_tank')->get()->map(function ($ikan) {
            $scoring = $ikan->scorings->first();
            return [
                'nama_peserta'  => $ikan->peserta->nama_peserta ?? 'Unknown',
                'nomor_tank'    => $ikan->nomor_tank,
                'kategori'      => $ikan->kategori ?? '—',
                'kelas'         => $scoring ? ($scoring->kelas ?? '—') : ($ikan->kelas ?? '—'),
                'detail_anggota' => $ikan->peserta->detail_anggota ?? '—',
                'total_nilai'   => $scoring?->total_nilai ?? 0,
                'juri_nama'     => $scoring?->juri?->name ?? '—',
            ];
        });

        return response()->json($data);
    }
}