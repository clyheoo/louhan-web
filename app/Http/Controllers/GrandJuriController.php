<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Scoring;
use App\Models\Peserta;
use App\Models\Ikan;
use App\Models\GrandJuriEdit;
use App\Helpers\PointCalculator;
use App\Models\ScoringPointConfig;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\GrandJuriExport;

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
        }, 'scorings.juri', 'scorings.grandJuri', 'bonusPoints']);

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

            $totalJuriAll = \App\Models\User::where('role', 'juri')->count();
            $submittedCount = $scorings->count();

            $allScoringsData = $scorings->map(function ($s) {
                // ★ Evaluasi defect di backend agar JS tidak perlu hitung ulang
                $defectRaw = [
                    'raw_head_penalty'    => $s->raw_head_penalty ?: ['0'],
                    'raw_face_penalty'    => $s->raw_face_penalty ?: ['0'],
                    'raw_body_penalty'    => $s->raw_body_penalty ?: ['0'],
                    'raw_finnage_penalty' => $s->raw_finnage_penalty ?: ['0'],
                ];
                $defectEval = PointCalculator::evaluateDefects($defectRaw);

                return [
                    'juri_name'          => $s->juri ? $s->juri->name : '—',
                    'is_grand'           => ($s->juri && $s->juri->role === 'grand_juri'),
                    'nilai_detail'       => $s->nilai_detail,
                    'total_nilai'        => $s->total_nilai ?? 0,
                    'raw_head_penalty'   => $s->raw_head_penalty ?: ['0'],
                    'raw_face_penalty'   => $s->raw_face_penalty ?: ['0'],
                    'raw_body_penalty'   => $s->raw_body_penalty ?: ['0'],
                    'raw_finnage_penalty'=> $s->raw_finnage_penalty ?: ['0'],
                    'defect_eval'        => $defectEval,
                ];
            })->values()->toArray();

            $pointConfig = ScoringPointConfig::where('kategori', $ikan->kategori)->first();

            // ★ HITUNG DARI SEMUA JURI (bukan hanya 1)
            $totalNilaiSemua = 0;
            $jumlahJuriYangNilai = 0;
            $detailListPerJuri = [];

            foreach ($scorings as $s) {
                if ($s->total_nilai) {
                    $totalNilaiSemua += $s->total_nilai;
                    $jumlahJuriYangNilai++;
                }
                if ($s->juri) {
                    $detailListPerJuri[] = [
                        'juri_name'    => $s->juri->name,
                        'is_grand'     => ($s->juri->role === 'grand_juri'),
                        'total_nilai'  => $s->total_nilai ?? 0,
                        'nilai_detail' => $s->nilai_detail,
                    ];
                }
            }

            // ★ FIX: Hitung POINT REAL-TIME menggunakan PointCalculator (SAMA PERSIS DENGAN ADMIN)
            $avgDetail = [];
            foreach ($scorings as $s) {
                if ($s->nilai_detail && is_array($s->nilai_detail)) {
                    foreach ($s->nilai_detail as $kat => $fields) {
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
                        $finalAvgDetail[$kat][$fid] = $d['count'] > 0
                            ? $d['sum'] / $d['count']
                            : 0;
                    }
                }
            }

            $totalPointSemua = PointCalculator::hitungPoint($ikan->kategori, $finalAvgDetail);

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
                'total_nilai_semua' => $totalNilaiSemua,
                'jumlah_juri_yang_nilai' => $jumlahJuriYangNilai,
                'detail_list_per_juri' => $detailListPerJuri,
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
                'total_point'     => (float) $totalPointSemua,
                'bonus_list'      => $ikan->bonusPoints->pluck('bonus_type')->toArray(),
                'total_bonus'     => (int) $ikan->bonusPoints->sum('points'),
                'final_point'     => (float) $totalPointSemua + (int) $ikan->bonusPoints->sum('points'),
                'point_breakdown' => $finalAvgDetail ? PointCalculator::hitungBreakdown($ikan->kategori, $finalAvgDetail) : null,
                'point_config'    => $pointConfig ? [
                    'overall' => (float)$pointConfig->overall_bobot,
                    'head'    => (float)$pointConfig->head_bobot,
                    'face'    => (float)$pointConfig->face_bobot,
                    'body'    => (float)$pointConfig->body_bobot,
                    'marking' => (float)$pointConfig->marking_bobot,
                    'pearl'   => (float)$pointConfig->pearl_bobot,
                    'color'   => (float)$pointConfig->color_bobot,
                    'finnage' => (float)$pointConfig->finnage_bobot,
                ] : null,
            ];
        });

        return response()->json($data);
    }

    /* ═════════════════════════════════════════
       EDIT NILAI (UPDATE: BERDASARKAN IKAN_ID)
       ═══════════════════════════════════════════ */
    public function editNilai(Request $request)
    {
        $data            = $request->json()->all();
        $ikanId          = $data['ikan_id'] ?? null;
        $changed         = $data['changed_fields'] ?? null;
        $defectFromReq   = $data['defect_data'] ?? null; // ← AMBIL DEFECT DARI FRONTEND

        $ikan = Ikan::find($ikanId);
        if (!$ikan) {
            return response()->json(['success' => false, 'message' => 'Data ikan tidak ditemukan.'], 422);
        }

        // ★ 1. AMBIL NILAI DETAIL LENGKAP DARI SCORING TERAKHIR
        $lastScoring = Scoring::where('ikan_id', $ikanId)
            ->where('submitted_to_grand', true)
            ->orderByDesc('created_at')
            ->first();

        $fullNilaiDetail = [];
        if ($lastScoring && $lastScoring->nilai_detail) {
            $fullNilaiDetail = $lastScoring->nilai_detail;
            if (is_string($fullNilaiDetail)) {
                $fullNilaiDetail = json_decode($fullNilaiDetail, true) ?? [];
            }
        }

        // ★ 2. OVERRIDE DENGAN NILAI YANG DIUBAH GRAND JURI
        if ($changed && is_array($changed)) {
            foreach ($changed as $kat => $fields) {
                if (!isset($fullNilaiDetail[$kat])) {
                    $fullNilaiDetail[$kat] = [];
                }
                foreach ($fields as $fieldId => $val) {
                    $fullNilaiDetail[$kat][$fieldId] = $val;
                }
            }
        }

        // ★ 3. HITUNG TOTAL NILAI DARI NILAI DETAIL LENGKAP (bukan hanya changed_fields)
        $totalNilai = 0;
        foreach ($fullNilaiDetail as $kat => $fields) {
            if (is_array($fields)) {
                foreach ($fields as $key => $val) {
                    if ($key === 'defect') continue;
                    $totalNilai += (int) $val;
                }
            }
        }

        // ★ 4. GUNAKAN DEFECT DARI REQUEST JIKA ADA, FALLBACK KE SCORING TERAKHIR
        $defectDataForCalc = [];
        if ($defectFromReq && is_array($defectFromReq)) {
            $defectDataForCalc = $defectFromReq;
        } elseif ($lastScoring) {
            $defectDataForCalc = [
                'raw_head_penalty'    => $lastScoring->raw_head_penalty ?: ['0'],
                'raw_face_penalty'    => $lastScoring->raw_face_penalty ?: ['0'],
                'raw_body_penalty'    => $lastScoring->raw_body_penalty ?: ['0'],
                'raw_finnage_penalty' => $lastScoring->raw_finnage_penalty ?: ['0'],
            ];
        }

        // ★ 5. HITUNG POINT DARI NILAI LENGKAP + DEFECT YANG BENAR
        $totalPoint = PointCalculator::hitungPoint($ikan->kategori, $fullNilaiDetail, $defectDataForCalc);

        // ★ 6. SIAPKAN DATA SCORING BARU
        $scoringData = [
            'ikan_id'              => $ikanId,
            'juri_id'              => auth()->id(),
            'grand_juri_id'        => auth()->id(),
            'kelas'                => $ikan->kelas ?? '-',
            'nilai_detail'         => $fullNilaiDetail,
            'total_nilai'          => $totalNilai,
            'status'               => 'submitted',
            'total_point'          => $totalPoint,
            'edited_by_grand_juri' => true,
        ];

        // ★ 7. SIMPAN DEFECT DATA KE SCORING BARU (supaya next edit baca yang terbaru)
        if ($defectDataForCalc) {
            $evaluated = PointCalculator::evaluateDefects($defectDataForCalc);
            $scoringData['raw_head_penalty']    = $defectDataForCalc['raw_head_penalty'] ?? ['0'];
            $scoringData['raw_face_penalty']    = $defectDataForCalc['raw_face_penalty'] ?? ['0'];
            $scoringData['raw_body_penalty']    = $defectDataForCalc['raw_body_penalty'] ?? ['0'];
            $scoringData['raw_finnage_penalty'] = $defectDataForCalc['raw_finnage_penalty'] ?? ['0'];
            $scoringData['keterangan']          = $evaluated['keterangan'] ?? '';
        }

        $newScoring = Scoring::create($scoringData);

        // ★ 8. SIMPAN LOG EDIT
        $totalSebelum = $lastScoring ? ($lastScoring->total_nilai ?? 0) : 0;

        GrandJuriEdit::create([
            'scoring_id'     => $newScoring->id,
            'peserta_id'     => $ikan->peserta_id,
            'grand_juri_id'  => auth()->id(),
            'nilai_sebelum'  => $lastScoring ? $lastScoring->nilai_detail : null,
            'nilai_sesudah'  => $fullNilaiDetail,
            'changed_fields' => $changed,
            'total_sebelum'  => $totalSebelum,
            'total_sesudah'  => $totalNilai,
        ]);

        return response()->json([
            'success'     => true,
            'message'     => 'Nilai baru berhasil disimpan oleh Grand Juri!',
            'total'       => $totalNilai,
            'total_point' => $totalPoint,
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
    ═════════════════════════════════════════ */
    public function getRincianDetail(Request $request)
    {
        $kategori = $request->query('kategori');

        if (!$kategori) {
            return response()->json(['error' => 'kategori wajib diisi'], 422);
        }

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
    PLOT STATUS (Klik stat Sudah/Belum Plot → daftar peserta
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

    public function getMvpIkan()
    {
        $ikans = Ikan::where('is_mvp', true)
            ->whereHas('peserta', function($q) {
                $q->where('is_mvp_submitted', true);
            })
            ->with('peserta')
            ->get()
            ->groupBy('peserta_id');

        $data = [];
        foreach ($ikans as $pesertaId => $ikanList) {
            $peserta = $ikanList->first()->peserta;
            $ikanDetails = $ikanList->map(function($ikan) {
                return [
                    'kategori' => $ikan->kategori,
                    'kelas' => $ikan->kelas,
                    'nomor_tank' => $ikan->nomor_tank ?? '-',
                ];
            })->values()->toArray();

            $data[] = [
                'peserta_id' => $pesertaId,
                'nama_peserta' => $peserta->nama_peserta ?? '-',
                'detail_anggota' => $peserta->detail_anggota ?? '-',
                'total_mvp' => $ikanList->count(),
                'ikans' => $ikanDetails
            ];
        }

        return response()->json($data);
    }

    public function getPointRanking(Request $request)
    {
        $scope = $request->query('scope', 'per_kategori_kelas');
        $filterKategori = $request->query('kategori', '');
        $filterKelas = $request->query('kelas', '');

        /* ═══════════════════════════════════════════
        SCOPE: RANK GLOBAL
        ═══════════════════════════════════════════ */
        if ($scope === 'global') {
            $limit = min(100, max(1, (int)($request->query('limit', 10))));

            $ikans = Ikan::where('is_locked', true)
                ->whereNotNull('nomor_tank')
                ->whereHas('scorings', function ($q) {
                    $q->where('submitted_to_grand', true);
                })
                ->with(['peserta', 'scorings' => function ($q) {
                    $q->where('submitted_to_grand', true);
                }, 'scorings.juri', 'scorings.grandJuri', 'bonusPoints'])
                ->get();

            $allItems = [];
            foreach ($ikans as $ikan) {
                $scorings = $ikan->scorings;
                if ($scorings->isEmpty()) continue;

                $totalNilaiSemua = 0;
                $jumlahJuriYangNilai = 0;
                $avgDetail = [];

                foreach ($scorings as $s) {
                    if ($s->total_nilai) {
                        $totalNilaiSemua += $s->total_nilai;
                        $jumlahJuriYangNilai++;
                    }
                    if ($s->nilai_detail && is_array($s->nilai_detail)) {
                        foreach ($s->nilai_detail as $kat => $fields) {
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
                            $finalAvgDetail[$kat][$fid] = $d['count'] > 0
                                ? $d['sum'] / $d['count']
                                : 0;
                        }
                    }
                }

                $totalPoint = PointCalculator::hitungPoint($ikan->kategori, $finalAvgDetail);
                $totalBonus = (int) $ikan->bonusPoints->sum('points');
                $finalPoint = $totalPoint + $totalBonus;

                $allItems[] = [
                    'ikan_id'           => $ikan->id,
                    'nama_peserta'      => $ikan->peserta->nama_peserta ?? 'Unknown',
                    'detail_anggota'    => $ikan->peserta->detail_anggota ?? '—',
                    'kategori'          => $ikan->kategori,
                    'kelas'             => $ikan->kelas,
                    'nomor_tank'        => $ikan->nomor_tank,
                    'total_nilai_semua' => $totalNilaiSemua,
                    'total_point'       => (float) $totalPoint,
                    'total_bonus'       => $totalBonus,
                    'final_point'       => (float) $finalPoint,
                    'jumlah_juri'       => $jumlahJuriYangNilai,
                ];
            }

            // ★ Pakai helper: sort DESC by final_point, rank 100 menurun
            $ranked = PointCalculator::hitungRankPoints($allItems, 'final_point');

            $totalRanked = count($ranked);
            $topItems = array_slice($ranked, 0, $limit);

            return response()->json([[
                'group_name' => 'Rank Global — Top ' . $limit . ' dari ' . $totalRanked,
                'total'      => $totalRanked,
                'data'       => $topItems,
            ]]);
        }

        /* ═══════════════════════════════════════════
        SCOPE: PER KATEGORI / PER KATEGORI+KELAS
        ═══════════════════════════════════════════ */
        $query = Ikan::where('is_locked', true)
            ->whereNotNull('nomor_tank')
            ->whereHas('scorings', function ($q) {
                $q->where('submitted_to_grand', true);
            })
            ->with(['peserta', 'scorings' => function ($q) {
                $q->where('submitted_to_grand', true);
            }, 'scorings.grandJuri', 'bonusPoints']);

        if ($filterKategori) $query->where('kategori', $filterKategori);
        if ($filterKelas) $query->where('kelas', $filterKelas);

        $ikans = $query->orderBy('nomor_tank')->get();
        $groups = [];

        foreach ($ikans as $ikan) {
            $scorings = $ikan->scorings;
            if ($scorings->isEmpty()) continue;

            $totalNilaiSemua = 0;
            $jumlahJuriYangNilai = 0;
            $avgDetail = [];

            foreach ($scorings as $s) {
                if ($s->total_nilai) {
                    $totalNilaiSemua += $s->total_nilai;
                    $jumlahJuriYangNilai++;
                }
                if ($s->nilai_detail && is_array($s->nilai_detail)) {
                    foreach ($s->nilai_detail as $kat => $fields) {
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
                        $finalAvgDetail[$kat][$fid] = $d['count'] > 0
                            ? $d['sum'] / $d['count']
                            : 0;
                    }
                }
            }

            $totalPoint = PointCalculator::hitungPoint($ikan->kategori, $finalAvgDetail);
            $totalBonus = (int) $ikan->bonusPoints->sum('points');
            $finalPoint = $totalPoint + $totalBonus;

            $key = ($scope === 'per_kategori')
                ? $ikan->kategori
                : $ikan->kategori . ' - Kelas ' . $ikan->kelas;

            $groups[$key][] = [
                'ikan_id'           => $ikan->id,
                'nama_peserta'      => $ikan->peserta->nama_peserta ?? 'Unknown',
                'detail_anggota'    => $ikan->peserta->detail_anggota ?? '—',
                'kategori'          => $ikan->kategori,
                'kelas'             => $ikan->kelas,
                'nomor_tank'        => $ikan->nomor_tank,
                'total_nilai_semua' => $totalNilaiSemua,
                'total_point'       => (float) $totalPoint,
                'total_bonus'       => $totalBonus,
                'final_point'       => (float) $finalPoint,
                'jumlah_juri'       => $jumlahJuriYangNilai,
            ];
        }

        $result = [];
        foreach ($groups as $name => $items) {
            // ★ Pakai helper: sort DESC by final_point, rank 100 menurun per group
            $ranked = PointCalculator::hitungRankPoints($items, 'final_point');
            $result[] = ['group_name' => $name, 'total' => count($ranked), 'data' => $ranked];
        }
        usort($result, function ($a, $b) { return strcmp($a['group_name'], $b['group_name']); });

        return response()->json($result);
    }
        const BONUS_TYPES = [
        'best_of_the_best' => 'BEST OF THE BEST',
        'best_of_show'     => 'BEST OF SHOW',
        'grand_champion'   => 'GRAND CHAMPION',
        'young_champion'   => 'YOUNG CHAMPION',
        'junior'           => 'JUNIOR',
        'baby_champion'    => 'BABY CHAMPION',
        'mini_champion'    => 'MINI CHAMPION',
    ];

    public function addBonus(Request $request)
    {
        $request->validate([
            'ikan_id'    => 'required|exists:ikans,id',
            'bonus_type' => 'required|in:best_of_the_best,best_of_show,grand_champion,young_champion,junior,baby_champion,mini_champion',
        ]);

        $exists = \App\Models\IkanBonusPoint::where('ikan_id', $request->ikan_id)
            ->where('bonus_type', $request->bonus_type)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Bonus "' . self::BONUS_TYPES[$request->bonus_type] . '" sudah diberikan ke ikan ini.',
            ], 409);
        }

        \App\Models\IkanBonusPoint::create([
            'ikan_id'    => $request->ikan_id,
            'bonus_type' => $request->bonus_type,
            'points'     => 100,
            'added_by'   => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Bonus "' . self::BONUS_TYPES[$request->bonus_type] . '" (+100) berhasil ditambahkan.',
        ]);
    }

    public function removeBonus(Request $request)
    {
        $request->validate([
            'ikan_id'    => 'required|exists:ikans,id',
            'bonus_type' => 'required|in:best_of_the_best,best_of_show,grand_champion,young_champion,junior,baby_champion,mini_champion',
        ]);

        $bonus = \App\Models\IkanBonusPoint::where('ikan_id', $request->ikan_id)
            ->where('bonus_type', $request->bonus_type)
            ->first();

        if (!$bonus) {
            return response()->json([
                'success' => false,
                'message' => 'Bonus tidak ditemukan.',
            ], 404);
        }

        $bonus->delete();

        return response()->json([
            'success' => true,
            'message' => 'Bonus "' . self::BONUS_TYPES[$request->bonus_type] . '" berhasil dihapus.',
        ]);
    }

    public function exportExcel(Request $request)
    {
        $sheets = $request->query('sheets', 'all');
        $valid  = ['all', 'daftar', 'mvp', 'ranking_kk', 'ranking_k', 'ranking_global'];

        if (!in_array($sheets, $valid)) {
            $sheets = 'all';
        }

        $label = match ($sheets) {
            'daftar'         => 'Daftar_Ikan',
            'mvp'            => 'Data_MVP',
            'ranking_kk'     => 'Ranking_Per_Kat_Kelas',
            'ranking_k'      => 'Ranking_Per_Kategori',
            'ranking_global' => 'Rank_Global',
            default          => 'Semua_Data',
        };

        $fileName = 'LCI_GrandJuri_' . $label . '_' . now()->format('Y-m-d_His') . '.xlsx';

        return Excel::download(new GrandJuriExport($sheets), $fileName);
    }
}