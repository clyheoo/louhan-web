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
        // Total Ikan yang sudah mendapat nomor tank
        $totalTank = Ikan::whereNotNull('nomor_tank')->count();
        $totalPeserta = Peserta::count();
        
        $sudahPlot = Scoring::distinct('ikan_id')->count('ikan_id');
        $belumPlot = max(0, $totalTank - $sudahPlot);
        $maxTank = (int) (\DB::table('settings')->where('key', 'tank_range_max')->value('value') ?? 1000);
        $sisaTank = max(0, $maxTank - $totalTank);

        // Rincian Per Kategori (Ikan yang sudah dapat tank)
        $rincian = Ikan::whereNotNull('nomor_tank')
            ->selectRaw('ikans.kategori, COUNT(ikans.id) as ekor,
                SUM(CASE WHEN scorings.id IS NULL THEN 1 ELSE 0 END) as belum_tank')
            ->leftJoin('scorings', 'ikans.id', '=', 'scorings.ikan_id') // DIUBAH: Join ke ikans.id
            ->groupBy('ikans.kategori')
            ->orderByDesc('ekor')
            ->get();

        return response()->json([
            'total_tank'    => $totalTank,
            'total_peserta' => $totalPeserta,
            'sudah_plot'    => $sudahPlot,
            'belum_plot'    => $belumPlot,
            'sisa_tank'     => $sisaTank,
            'max_tank'      => $maxTank, // DITAMBAHKAN
            'rincian'       => $rincian,
        ]);
    }

    /* ═══════════════════════════════════════════
       PESERTA LIST (QUERY DARI TABEL IKANS)
       ═══════════════════════════════════════════ */
    public function getPeserta(Request $request)
    {
        // DIPERBAIKI: Tampilkan ikan JIKA sudah dapat nomor tank ATAU sudah pernah dinilai
        $query = Ikan::where(function($q) {
            $q->whereNotNull('nomor_tank')
              ->orWhereHas('scorings');
        })
            ->with(['peserta', 'scorings' => function ($q) {
                $q->latest()->limit(1);
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
            $scoring = $ikan->scorings->first();

            return [
                'id'               => $ikan->id, // Sekarang ini adalah IKAN_ID
                'nama_peserta'     => $peserta->nama_peserta ?? 'Unknown',
                'kategori'         => $ikan->kategori,     // Diambil dari tabel Ikan
                'kelas'            => $scoring ? $scoring->kelas : $ikan->kelas, // Prioritas kelas dari Juri, jika belum ada ambil dari Ikan
                'nomor_tank'       => $ikan->nomor_tank,   // Diambil dari tabel Ikan
                'detail_anggota'   => $peserta->detail_anggota ?? '—',
                'status'           => $scoring 
                                    ? ($scoring->edited_by_grand_juri ? 'Diubah Grand Juri' : 'Sudah Dinilai') 
                                    : 'Belum Dinilai',
                'status_class'     => $scoring 
                                    ? ($scoring->edited_by_grand_juri ? 'badge-warning' : 'badge-success') 
                                    : 'badge-warning',
                'juri_nama'        => $scoring?->juri?->name ?? '—',
                'grand_juri_nama'  => $scoring?->grandJuri?->name ?? null,
                'nilai_detail'     => $scoring?->nilai_detail ?? null,
                'total_nilai'      => $scoring?->total_nilai ?? 0,
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

        if (!$ikanId || !Ikan::find($ikanId)) {
            return response()->json(['success' => false, 'message' => 'Data ikan tidak ditemukan.'], 422);
        }

        if (!$changed || !is_array($changed)) {
            return response()->json(['success' => false, 'message' => 'Tidak ada perubahan nilai.'], 422);
        }

        /* ── KASUS 1: Sudah ada scoring dari juri ── */
        $existing = Scoring::with('ikan')->where('ikan_id', $ikanId)->latest()->first();

        if ($existing && $existing->nilai_detail) {
            $nilaiAsli = null;
            if (!$existing->edited_by_grand_juri) {
                $nilaiAsli = $existing->nilai_detail;
            } elseif ($existing->nilai_detail_asli) {
                $nilaiAsli = $existing->nilai_detail_asli;
            }

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
                'nilai_detail_asli'    => $nilaiAsli,
                'total_nilai'          => $totalNilai,
                'edited_by_grand_juri' => true,
                'status'               => 'submitted',
            ]);

            /* ★ FIX: ambil peserta_id dari relasi ikan, bukan langsung dari scoring */
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

    /* ═══════════════════════════════════════════
    JURI SUMMARY (DEFENSIF — TANPA EAGER LOADING)
    ═══════════════════════════════════════════ */
    public function getJuriSummary()
    {
        try {
            $merged = [];

            // --- Juri biasa ---
            $juriRows = \DB::table('scorings')
                ->join('users', 'scorings.juri_id', '=', 'users.id')
                ->whereNotNull('scorings.juri_id')
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

        $ikans = Ikan::where(function($q) {
                $q->whereNotNull('nomor_tank')
                  ->orWhereHas('scorings');
            })
            ->where('kategori', $kategori)
            ->with(['peserta', 'scorings' => function ($q) {
                $q->latest()->limit(1);
            }, 'scorings.juri'])
            ->orderBy('nomor_tank')
            ->get();

        $sudah = [];
        $belum = [];

        foreach ($ikans as $ikan) {
            $scoring = $ikan->scorings->first();
            $item = [
                'nama_peserta' => $ikan->peserta->nama_peserta ?? 'Unknown',
                'nomor_tank'   => $ikan->nomor_tank,
            ];

            if ($scoring) {
                $item['juri_nama']   = $scoring->juri?->name ?? '—';
                $item['total_nilai'] = $scoring->total_nilai ?? 0;
                $sudah[] = $item;
            } else {
                $belum[] = $item;
            }
        }

        return response()->json([
            'kategori'  => $kategori,
            'total_ekor' => $ikans->count(),
            'sudah'     => $sudah,
            'belum'     => $belum,
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
            $query->whereHas('scorings');
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