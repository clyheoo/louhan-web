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
use App\Models\Nominasi;
use App\Services\SheetsSyncService;

class GrandJuriController extends Controller
{
    protected $sheetsSync;

    public function __construct(SheetsSyncService $sheetsSync)
    {
        $this->sheetsSync = $sheetsSync;
    }
    
    private const BONUS_TYPES = [
        'best_of_the_best' => 'BEST OF THE BEST',
        'best_of_show'     => 'BEST OF SHOW',
        'grand_champion'   => 'GRAND CHAMPION',
        'young_champion'   => 'YOUNG CHAMPION',
        'junior'           => 'JUNIOR',
        'baby_champion'    => 'BABY CHAMPION',
        'mini_champion'    => 'MINI CHAMPION',
    ];

    private function normalizeDefectArray($value): array
    {
        if (is_string($value)) {
            $value = [$value];
        }

        if (!is_array($value)) {
            return ['0'];
        }

        $items = collect($value)
            ->map(function ($v) {
                $v = trim((string) $v);
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
    }

    private function nominasiDefectPayload(Nominasi $n): array
    {
        return [
            'raw_head_penalty'    => $this->normalizeDefectArray($n->raw_head_penalty ?? ['0']),
            'raw_face_penalty'    => $this->normalizeDefectArray($n->raw_face_penalty ?? ['0']),
            'raw_body_penalty'    => $this->normalizeDefectArray($n->raw_body_penalty ?? ['0']),
            'raw_finnage_penalty' => $this->normalizeDefectArray($n->raw_finnage_penalty ?? ['0']),
        ];
    }

    public function index()
    {
        return view('dashboard.grand-juri', ['user' => auth()->user()->fresh()]);
    }

    /* ═══════════════════════════════════════════
       STATS (UPDATE KE TABEL IKANS)
       ═══════════════════════════════════════════ */
    public function getStats()
    {
        try {
            $totalTank    = Ikan::whereNotNull('nomor_tank')->count();
            $totalPeserta = Peserta::count();

            $sudahPlot = Ikan::whereNotNull('nomor_tank')
                ->whereHas('scorings')
                ->count();

            $belumPlot = Ikan::whereNotNull('nomor_tank')
                ->whereDoesntHave('scorings')
                ->count();

            // Cari max tank secara aman tanpa model Setting
            $maxTank = 0;
            $sisaTank = 0;
            try {
                $setting = \DB::table('settings')->where('key', 'tank_range_max')->first();
                if ($setting) {
                    $maxTank = (int) $setting->value;
                    $sisaTank = max(0, $maxTank - $totalTank);
                }
            } catch (\Exception $e) {
                // Jika tabel settings tidak ada, biarkan maxTank = 0
            }

            // Rincian per kategori
            $rincian = Ikan::whereNotNull('nomor_tank')
                ->selectRaw('kategori, COUNT(*) as ekor')
                ->groupBy('kategori')
                ->orderBy('kategori')
                ->get()
                ->map(function ($row) {
                    return [
                        'kategori' => $row->kategori,
                        'ekor'     => (int) $row->ekor,
                    ];
                })
                ->toArray();

            return response()->json([
                'total_tank'    => $totalTank,
                'total_peserta' => $totalPeserta,
                'sudah_plot'    => $sudahPlot,
                'belum_plot'    => $belumPlot,
                'sisa_tank'     => $sisaTank,
                'max_tank'      => $maxTank,
                'rincian'       => $rincian,
            ]);

        } catch (\Exception $e) {
            // Jika masih gagal juga, kirim pesan error agar bisa dilihat di Network tab
            return response()->json([
                'error'   => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }

        /* ═══════════════════════════════════════════
       NOMINASI — INDEX (HALAMAN TERPISAH)
       ═══════════════════════════════════════════ */
    public function nominasiIndex()
    {
        return view('dashboard.grand-juri-nominasi', ['user' => auth()->user()->fresh()]);
    }

    /* ═══════════════════════════════════════════
       NOMINASI — AMBIL DATA PENDING
       ═══════════════════════════════════════════ */
    public function getNominasi()
    {
        try {
            $nominations = Nominasi::where('status', 'pending')
                ->where('is_late_addition', false)
                ->with(['juri', 'ikan.peserta'])
                ->orderByDesc('created_at')
                ->get();

            $grouped = $nominations->groupBy('juri_id')->map(function ($items) {
                $juri = $items->first()->juri;
                if (!$juri) return null;
                return [
                    'juri_id'   => $juri->id,
                    'juri_name' => $juri->name,
                    'tanks'     => $items->map(function ($n) {
                        $ikan = $n->ikan;
                        if (!$ikan) return null;
                        $defects = $this->nominasiDefectPayload($n);
                        return [
                            'nominasi_id'   => $n->id,
                            'ikan_id'       => $n->ikan_id,
                            'nomor_tank'    => $ikan->nomor_tank ?? null,
                            'kategori'      => $ikan->kategori ?? null,
                            'kelas'         => $ikan->kelas ?? null,
                            'nama_peserta'  => $ikan->nama_peserta ?? 'Unknown',
                            'detail_anggota'=> $ikan->detail_anggota ?? '—',
                            'submitted_at'  => $n->created_at ? $n->created_at->toISOString() : null,

                            // defect nominasi dari juri
                            'raw_head_penalty'    => $defects['raw_head_penalty'],
                            'raw_face_penalty'    => $defects['raw_face_penalty'],
                            'raw_body_penalty'    => $defects['raw_body_penalty'],
                            'raw_finnage_penalty' => $defects['raw_finnage_penalty'],
                            
                        ];
                    })->filter()->values()->toArray(),
                ];
            })->filter()->values()->toArray();

            return response()->json([
                'grouped'       => $grouped,
                'total_pending' => $nominations->count(),
                'total_juri'    => count($grouped),
            ]);
        } catch (\Throwable $e) {
            \Log::error('getNominasi error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'grouped'       => [],
                'total_pending' => 0,
                'total_juri'    => 0,
                'error'         => true,
                'message'       => 'Gagal memuat nominasi pending.',
            ], 500);
        }
    }

    /* ═══════════════════════════════════════════
       NOMINASI — REVIEW (ACC / TOLAK)
       ═══════════════════════════════════════════ */
    public function reviewNominasi(Request $request)
    {
        $nominasiId = $request->json('nominasi_id');
        $action     = $request->json('action');
        $catatan    = $request->json('catatan', '');

        if (!$nominasiId || !in_array($action, ['approve', 'reject'])) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid.',
            ], 422);
        }

        $nominasi = Nominasi::where('id', $nominasiId)
            ->where('status', 'pending')
            ->first();

        if (!$nominasi) {
            return response()->json([
                'success' => false,
                'message' => 'Nominasi tidak ditemukan atau sudah ditinjau.',
            ], 404);
        }

        $nomorTank = $nominasi->ikan->nomor_tank ?? '?';

        $nominasi->update([
            'status'      => $action === 'approve' ? 'approved' : 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'catatan'     => $catatan,
        ]);

                // ★ CASCADE REJECT: Jika satu nominasi ditolak, tolak semua nominasi lain untuk ikan_id yang sama
        if ($action === 'reject') {
            Nominasi::where('ikan_id', $nominasi->ikan_id)
                ->where('id', '!=', $nominasi->id)
                ->where('status', '!=', 'rejected') // Update yang pending atau approved
                ->update([
                    'status'      => 'rejected',
                    'reviewed_by' => auth()->id(),
                    'reviewed_at' => now(),
                    'catatan'     => 'Otomatis ditolak karena nominasi lain untuk tank ini ditolak.',
                ]);
        }

        // ★ Sync untuk APPROVE & REJECT (termasuk cascade reject):
        //   - Sheet NOMINASI / PIL NOM / NOMINASI FIX hanya menampilkan status=approved,
        //     jadi pada reject yang membatalkan record yang sebelumnya approved,
        //     ketiga sheet itu WAJIB di-refresh agar record hantu hilang.
        //   - HASIL NOMINASI menampilkan approved+rejected → wajib sync di kedua action.
        $sync = $this->sheetsSync;
        $actionLog = $action;
        app()->terminating(function () use ($sync, $actionLog) {
            if (!$sync->isReady()) return;
            try { $sync->syncSemuaNominasi();  } catch (\Exception $e) { \Log::error("Async-sync SemuaNominasi ($actionLog): "  . $e->getMessage()); }
            try { $sync->syncSemuaPilNom();    } catch (\Exception $e) { \Log::error("Async-sync SemuaPilNom ($actionLog): "    . $e->getMessage()); }
            try { $sync->syncHasilNominasi();  } catch (\Exception $e) { \Log::error("Async-sync HasilNominasi ($actionLog): "  . $e->getMessage()); }
            try { $sync->syncNominasiFix();    } catch (\Exception $e) { \Log::error("Async-sync NominasiFix ($actionLog): "    . $e->getMessage()); }
        });

        return response()->json([
            'success' => true,
            'message' => $action === 'approve'
                ? 'Tank ' . $nomorTank . ' DISETUJUI.'
                : 'Tank ' . $nomorTank . ' DITOLAK.',
        ]);
    }

    /* ═══════════════════════════════════════════
    NOMINASI — RIWAYAT REVIEW (DITERIMA/DITOLAK)
    ═══════════════════════════════════════════ */
    public function getNominasiHistory()
    {
        try {
            $nominations = Nominasi::whereIn('status', ['approved', 'rejected'])
                ->with(['juri', 'ikan.peserta'])
                ->orderByDesc('reviewed_at')
                ->orderByDesc('updated_at')
                ->get();

            $mapTank = function (Nominasi $n, bool $withCatatan = false) {
                $ikan = $n->ikan;
                $defects = $this->nominasiDefectPayload($n);

                $row = [
                    'nominasi_id'  => $n->id,
                    'ikan_id'      => $n->ikan_id,
                    'nomor_tank'   => $ikan ? $ikan->nomor_tank : null,
                    'kategori'     => $ikan ? $ikan->kategori : null,
                    'kelas'        => $ikan ? $ikan->kelas : null,
                    'nama_peserta' => $ikan ? ($ikan->nama_peserta ?? 'Unknown') : 'Unknown',
                    'reviewed_at'  => $n->reviewed_at ? $n->reviewed_at->format('d M Y, H:i') : '-',

                    'raw_head_penalty'    => $defects['raw_head_penalty'],
                    'raw_face_penalty'    => $defects['raw_face_penalty'],
                    'raw_body_penalty'    => $defects['raw_body_penalty'],
                    'raw_finnage_penalty' => $defects['raw_finnage_penalty'],
                ];

                if ($withCatatan) {
                    $row['catatan'] = $n->catatan ?: null;
                }

                return $row;
            };

            $approved = $nominations->where('status', 'approved')
                ->groupBy('juri_id')
                ->map(function ($items) use ($mapTank) {
                    $juri = $items->first()->juri;

                    return [
                        'juri_id'   => $juri ? $juri->id : null,
                        'juri_name' => $juri ? $juri->name : 'Unknown',
                        'tanks'     => $items->map(function ($n) use ($mapTank) {
                            return $mapTank($n, false);
                        })->values()->toArray(),
                    ];
                })
                ->values()
                ->toArray();

            $rejected = $nominations->where('status', 'rejected')
                ->groupBy('juri_id')
                ->map(function ($items) use ($mapTank) {
                    $juri = $items->first()->juri;

                    return [
                        'juri_id'   => $juri ? $juri->id : null,
                        'juri_name' => $juri ? $juri->name : 'Unknown',
                        'tanks'     => $items->map(function ($n) use ($mapTank) {
                            return $mapTank($n, true);
                        })->values()->toArray(),
                    ];
                })
                ->values()
                ->toArray();

            return response()->json([
                'approved'       => $approved,
                'rejected'       => $rejected,
                'total_approved' => $nominations->where('status', 'approved')->count(),
                'total_rejected' => $nominations->where('status', 'rejected')->count(),
            ]);

        } catch (\Throwable $e) {
            \Log::error('getNominasiHistory error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'approved'       => [],
                'rejected'       => [],
                'total_approved' => 0,
                'total_rejected' => 0,
                'error'          => true,
                'message'        => 'Gagal memuat riwayat nominasi.',
            ], 500);
        }
    }

    /* ═══════════════════════════════════════════
       LATE IKAN — DAFTAR IKAN YANG TERLAMBAT DAFTAR
       Hanya aktif setelah SEMUA juri sudah punya ≥1 nominasi approved.
       ═══════════════════════════════════════════ */
    public function getLateIkan()
    {
        try {
            $totalJuri = \App\Models\User::where('role', 'juri')->count();

            // Juri yang sudah punya minimal 1 nominasi approved (regular, bukan late_addition)
            $juriDoneIds = Nominasi::where('status', 'approved')
                ->where('is_late_addition', false)
                ->whereHas('juri', function ($q) {
                    $q->where('role', 'juri');
                })
                ->distinct('juri_id')
                ->pluck('juri_id')
                ->toArray();

            $allDone = $totalJuri > 0 && count($juriDoneIds) >= $totalJuri;

            if (!$allDone) {
                return response()->json([
                    'enabled'       => false,
                    'total_juri'    => $totalJuri,
                    'juri_done'     => count($juriDoneIds),
                    'ikans'         => [],
                    'message'       => 'Modul aktif setelah semua juri selesai nominasi (' 
                                       . count($juriDoneIds) . '/' . $totalJuri . ').',
                ]);
            }

            // Ikan yang belum pernah masuk Nominasi APAPUN (pending/approved/rejected)
            $ikanIdsInNominasi = Nominasi::distinct('ikan_id')->pluck('ikan_id')->toArray();

            $lateIkans = Ikan::whereNotNull('nomor_tank')
                ->whereNotIn('id', $ikanIdsInNominasi)
                ->with('peserta')
                ->orderBy('nomor_tank')
                ->get()
                ->map(function ($ikan) {
                    return [
                        'ikan_id'        => $ikan->id,
                        'nomor_tank'     => $ikan->nomor_tank,
                        'kategori'       => $ikan->kategori,
                        'kelas'          => $ikan->kelas,
                        'nama_peserta'   => $ikan->nama_peserta ?? 'Unknown',
                        'detail_anggota' => $ikan->detail_anggota ?? '—',
                        'created_at'     => $ikan->created_at ? $ikan->created_at->toISOString() : null,
                    ];
                });

            return response()->json([
                'enabled'    => true,
                'total_juri' => $totalJuri,
                'juri_done'  => count($juriDoneIds),
                'ikans'      => $lateIkans,
                'total'      => $lateIkans->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'enabled' => false,
                'ikans'   => [],
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /* ═══════════════════════════════════════════
       LATE IKAN — REVIEW (ACC / TOLAK)
       ═══════════════════════════════════════════ */
    public function reviewLateIkan(Request $request)
    {
        $ikanId  = $request->json('ikan_id');
        $action  = $request->json('action');
        $catatan = $request->json('catatan', '');

        if (!$ikanId || !in_array($action, ['approve', 'reject'])) {
            return response()->json(['success' => false, 'message' => 'Data tidak valid.'], 422);
        }

        $ikan = Ikan::find($ikanId);
        if (!$ikan || !$ikan->nomor_tank) {
            return response()->json(['success' => false, 'message' => 'Ikan tidak ditemukan.'], 404);
        }

        // Cegah duplikasi: jika sudah ada Nominasi apapun untuk ikan ini, bukan late lagi
        $alreadyExists = Nominasi::where('ikan_id', $ikanId)->exists();
        if ($alreadyExists) {
            return response()->json([
                'success' => false,
                'message' => 'Ikan ini sudah pernah masuk Nominasi.',
            ], 409);
        }

        $nominasi = Nominasi::create([
            'juri_id'          => auth()->id(), // Grand Juri sebagai juri_id penanda
            'ikan_id'          => $ikanId,
            'status'           => $action === 'approve' ? 'approved' : 'rejected',
            'reviewed_by'      => auth()->id(),
            'reviewed_at'      => now(),
            'catatan'          => $catatan ?: 'Ditambahkan langsung oleh Grand Juri (peserta terlambat daftar)',
            'is_late_addition' => true,
        ]);

        $nomorTank = $ikan->nomor_tank;

        // Auto-sync ke 4 sheet kalau approve
        $sync = $this->sheetsSync;
        $act  = $action;
        app()->terminating(function () use ($sync, $act) {
            if (!$sync->isReady()) return;
            try {
                if ($act === 'approve') {
                    $sync->syncSemuaNominasi();
                    $sync->syncSemuaPilNom();
                    $sync->syncHasilNominasi();
                    $sync->syncNominasiFix();
                } else {
                    $sync->syncHasilNominasi();
                }
            } catch (\Exception $e) {
                \Log::error("Async-sync late-ikan ($act): " . $e->getMessage());
            }
        });

        return response()->json([
            'success' => true,
            'message' => $action === 'approve'
                ? 'Tank ' . $nomorTank . ' (terlambat) DISETUJUI.'
                : 'Tank ' . $nomorTank . ' (terlambat) DITOLAK.',
        ]);
    }

    public function getPeserta(Request $request)
    {
        $query = Ikan::whereHas('scorings', function ($q) {
        })
        ->with(['peserta', 'scorings' => function ($q) {
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

            // ★ Variabel tracking
            $grandJuriEditors = [];
            $grandJuriName = null;
            $latestNilai = null;
            $latestTotal = 0;
            $latestKelas = null;
            $juriList = [];

            // ★ Loop semua scoring
            foreach ($scorings as $s) {
                // ★ Daftar juri ASLI untuk tampilan
                $juriName = $s->juri ? $s->juri->name : '—';

                $juriList[] = [
                    'name'     => $juriName,
                    'is_grand' => false,
                ];

                // ★ Track SEMUA grand juri yang pernah edit
                if ($s->edited_by_grand_juri && $s->grandJuri) {
                    $gjName = $s->grandJuri->name;
                    if (!in_array($gjName, $grandJuriEditors)) {
                        $grandJuriEditors[] = $gjName;
                    }
                    $grandJuriName = $gjName;
                }

                // Ambil nilai terakhir
                $latestNilai = $s->nilai_detail;
                $latestTotal = $s->total_nilai ?? 0;
                $latestKelas = $s->kelas;
            }

            // ★ Tambahkan semua grand juri editors ke juriList
            foreach ($grandJuriEditors as $gjName) {
                $juriList[] = [
                    'name'      => $gjName,
                    'is_grand'  => true,
                    'is_editor' => true,
                ];
            }

            $totalJuriAll = \App\Models\User::where('role', 'juri')->count();
            $submittedCount = $scorings->count();

            // ★ Siapkan allScoringsData untuk modal detail
            $allScoringsData = $scorings->map(function ($s) {
                $defectRaw = [
                    'raw_head_penalty'    => $s->raw_head_penalty ?: ['0'],
                    'raw_face_penalty'    => $s->raw_face_penalty ?: ['0'],
                    'raw_body_penalty'    => $s->raw_body_penalty ?: ['0'],
                    'raw_finnage_penalty' => $s->raw_finnage_penalty ?: ['0'],
                ];
                $defectEval = PointCalculator::evaluateDefects($defectRaw);

                return [
                    'juri_name'          => $s->juri ? $s->juri->name : '—',
                    'is_grand'           => false,
                    'edited_by_grand'    => (bool) $s->edited_by_grand_juri,
                    'grand_juri_name'    => ($s->edited_by_grand_juri && $s->grandJuri) ? $s->grandJuri->name : null,
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

            // ★ Hitung total dari SEMUA scoring
            $totalNilaiSemua = 0;
            $jumlahJuriYangNilai = 0;
            $detailListPerJuri = [];
            $avgDetail = [];

            foreach ($scorings as $s) {
                if ($s->total_nilai) {
                    $totalNilaiSemua += $s->total_nilai;
                    $jumlahJuriYangNilai++;
                }

                $detailListPerJuri[] = [
                    'juri_name'    => $s->juri ? $s->juri->name : '—',
                    'is_grand'     => false,  // ← FIX: Ini data juri asli, selalu false
                    'total_nilai'  => $s->total_nilai ?? 0,
                    'nilai_detail' => $s->nilai_detail,
                ];

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
                        $finalAvgDetail[$kat][$fid] = $d['count'] > 0
                            ? $d['sum'] / $d['count']
                            : 0;
                    }
                }
            }

            // ★ Ambil defect data: prioritas Grand Juri edit, fallback ke scoring terbaru
            $mergedDefect = [
                'raw_head_penalty'    => ['0'],
                'raw_face_penalty'    => ['0'],
                'raw_body_penalty'    => ['0'],
                'raw_finnage_penalty' => ['0'],
            ];
            $grandEdited = $scorings->first(function ($s) { return $s->edited_by_grand_juri; });
            $defectSource = $grandEdited ?: $scorings->sortByDesc('updated_at')->first();
            if ($defectSource) {
                $mergedDefect['raw_head_penalty']    = $defectSource->raw_head_penalty    ?: ['0'];
                $mergedDefect['raw_face_penalty']    = $defectSource->raw_face_penalty    ?: ['0'];
                $mergedDefect['raw_body_penalty']    = $defectSource->raw_body_penalty    ?: ['0'];
                $mergedDefect['raw_finnage_penalty'] = $defectSource->raw_finnage_penalty ?: ['0'];
            }

            $totalPointSemua = PointCalculator::hitungPoint($ikan->kategori, $finalAvgDetail, $mergedDefect);

            return [
                'id'                    => $ikan->id,
                'nama_peserta'          => $ikan->nama_peserta ?? 'Unknown',
                'kategori'              => $ikan->kategori,
                'kelas'                 => $latestKelas ?? $ikan->kelas ?? '-',
                'nomor_tank'            => $ikan->nomor_tank,
                'detail_anggota'        => $ikan->detail_anggota ?? '—',
                'juri_list'             => $juriList,
                'grand_juri_nama'       => $grandJuriName,
                'nilai_detail'          => $latestNilai,
                'total_nilai'           => $latestTotal,
                'total_nilai_semua'     => $totalNilaiSemua,
                'jumlah_juri_yang_nilai'=> $jumlahJuriYangNilai,
                'detail_list_per_juri'  => $detailListPerJuri,
                'is_locked'             => (bool) ($ikan->is_locked ?? false),
                'status'                => $ikan->is_locked
                                        ? 'NILAI FINAL (TERKUNCI)'
                                        : ($grandJuriName ? 'Diubah Grand Juri' : 'Sudah Dinilai'),
                'status_class'          => $ikan->is_locked
                                        ? 'badge-success'
                                        : ($grandJuriName ? 'badge-warning' : 'badge-success'),
                'total_juri_all'        => $totalJuriAll,
                'submitted_juri_count'  => $submittedCount,
                'all_scorings'          => $allScoringsData,
                'total_point'           => (float) $totalPointSemua,
                'bonus_list'            => $ikan->bonusPoints->pluck('bonus_type')->toArray(),
                'total_bonus'           => (int) $ikan->bonusPoints->sum('points'),
                'final_point'           => (float) $totalPointSemua + (int) $ikan->bonusPoints->sum('points'),
                'point_breakdown'       => !empty($finalAvgDetail) ? PointCalculator::hitungBreakdown($ikan->kategori, $finalAvgDetail, $mergedDefect) : null,
                'point_config'          => $pointConfig ? [
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
        $defectFromReq   = $data['defect_data'] ?? null;

        $ikan = Ikan::find($ikanId);
        if (!$ikan) {
            return response()->json(['success' => false, 'message' => 'Data ikan tidak ditemukan.'], 422);
        }

        // ★★★ TIDAK ADA DELETE DI SINI! ★★★

        // ★ 1. CARI SCORING JURI ASLI TERAKHIR
        $targetScoring = Scoring::where('ikan_id', $ikanId)
            ->latest()
            ->first();

        if (!$targetScoring) {
            return response()->json(['success' => false, 'message' => 'Tidak ada nilai juri yang bisa diedit.'], 422);
        }

        // ★ 2. SIMPAN NILAI SEBELUM UNTUK LOG
        $nilaiSebelum = $targetScoring->nilai_detail;
        $totalSebelum = $targetScoring->total_nilai ?? 0;

        // ★ 3. AMBIL NILAI DETAIL LENGKAP DARI SCORING INI
        $fullNilaiDetail = $targetScoring->nilai_detail ?: [];
        if (is_string($fullNilaiDetail)) {
            $fullNilaiDetail = json_decode($fullNilaiDetail, true) ?? [];
        }

        // ★ 4. OVERRIDE DENGAN NILAI YANG DIUBAH GRAND JURI
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

        // ★ 5. HITUNG TOTAL NILAI
        $totalNilai = 0;
        foreach ($fullNilaiDetail as $kat => $fields) {
            if (is_array($fields)) {
                foreach ($fields as $key => $val) {
                    if ($key === 'defect') continue;
                    $totalNilai += (int) $val;
                }
            }
        }

        // ★ 6. DEFECT DATA
        $defectDataForCalc = [];
        if ($defectFromReq && is_array($defectFromReq)) {
            $defectDataForCalc = $defectFromReq;
        } else {
            $defectDataForCalc = [
                'raw_head_penalty'    => $targetScoring->raw_head_penalty ?: ['0'],
                'raw_face_penalty'    => $targetScoring->raw_face_penalty ?: ['0'],
                'raw_body_penalty'    => $targetScoring->raw_body_penalty ?: ['0'],
                'raw_finnage_penalty' => $targetScoring->raw_finnage_penalty ?: ['0'],
            ];
        }

        // ★ 7. HITUNG POINT
        $totalPoint = PointCalculator::hitungPoint($ikan->kategori, $fullNilaiDetail, $defectDataForCalc);

        // ★ 8. SIAPKAN DATA UPDATE
        $updateData = [
            'nilai_detail'         => $fullNilaiDetail,
            'total_nilai'          => $totalNilai,
            'total_point'          => $totalPoint,
            'edited_by_grand_juri' => true,
            'grand_juri_id'        => auth()->id(),
        ];

        // ★ 9. UPDATE DEFECT DATA
        if ($defectDataForCalc) {
            $evaluated = PointCalculator::evaluateDefects($defectDataForCalc);
            $updateData['raw_head_penalty']    = $defectDataForCalc['raw_head_penalty'] ?? ['0'];
            $updateData['raw_face_penalty']    = $defectDataForCalc['raw_face_penalty'] ?? ['0'];
            $updateData['raw_body_penalty']    = $defectDataForCalc['raw_body_penalty'] ?? ['0'];
            $updateData['raw_finnage_penalty'] = $defectDataForCalc['raw_finnage_penalty'] ?? ['0'];
            $updateData['keterangan']          = $evaluated['keterangan'] ?? '';
        }

        // ★ 10. UPDATE SCORING (BUKAN DELETE, BUKAN CREATE!)
        $targetScoring->update($updateData);

        // ★ 11. SIMPAN LOG EDIT
        GrandJuriEdit::create([
            'scoring_id'     => $targetScoring->id,
            'peserta_id'     => $ikan->peserta_id,
            'grand_juri_id'  => auth()->id(),
            'nilai_sebelum'  => $nilaiSebelum,
            'nilai_sesudah'  => $fullNilaiDetail,
            'changed_fields' => $changed,
            'total_sebelum'  => $totalSebelum,
            'total_sesudah'  => $totalNilai,
        ]);

        // ★ AUTO-SYNC: dijalankan SETELAH response dikirim ke browser.
        //   User tidak perlu menunggu Google Sheets API selesai.
        $sync = $this->sheetsSync;
        app()->terminating(function () use ($sync) {
            if (!$sync->isReady()) return;
            try { $sync->syncCnt();       } catch (\Exception $e) { \Log::error('Async-sync CNT (edit): '       . $e->getMessage()); }
            try { $sync->syncHasilJuri(); } catch (\Exception $e) { \Log::error('Async-sync HasilJuri (edit): ' . $e->getMessage()); }
            try { $sync->syncNilaiJuri(); } catch (\Exception $e) { \Log::error('Async-sync NilaiJuri (edit): ' . $e->getMessage()); }
            try { $sync->syncMvp();       } catch (\Exception $e) { \Log::error('Async-sync MVP (edit): '       . $e->getMessage()); }
        });

        return response()->json([
            'success'     => true,
            'message'     => 'Nilai berhasil diperbarui oleh Grand Juri!',
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

        // ★ Gate dihapus: Grand Juri boleh kunci kapan saja, minimal 1 juri saja yang sudah nilai.
        $adaNilai = Scoring::where('ikan_id', $ikanId)->exists();
        if (!$adaNilai) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak bisa mengunci. Belum ada satupun juri yang menilai tank ini.',
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

            $juriRows = \DB::table('scorings')
                ->join('users', 'scorings.juri_id', '=', 'users.id')
                ->whereNotNull('scorings.juri_id')
                ->where('users.role', 'juri')
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
                'nama_peserta' => $scoring->ikan->nama_peserta ?? 'Unknown',
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
            })
            ->where('kategori', $kategori)
            ->with(['scorings.juri'])
            ->orderBy('nomor_tank')
            ->get();

        $data = [];
        foreach ($ikans as $ikan) {
            $juriNames = $ikan->scorings
                ->map(function ($s) { return $s->juri ? $s->juri->name : null; })
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            $data[] = [
                'nama_peserta' => $ikan->nama_peserta ?? 'Unknown',
                'nomor_tank'   => $ikan->nomor_tank,
                'juri_nama'    => count($juriNames) > 0 ? implode(', ', $juriNames) : '—',
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
            });
        } else {
            $query->whereDoesntHave('scorings');
        }
        $data = $query->orderBy('nomor_tank')->get()->map(function ($ikan) {
            $scoring = $ikan->scorings->first();
            return [
                'nama_peserta'  => $ikan->nama_peserta ?? 'Unknown',
                'nomor_tank'    => $ikan->nomor_tank,
                'kategori'      => $ikan->kategori ?? '—',
                'kelas'         => $scoring ? ($scoring->kelas ?? '—') : ($ikan->kelas ?? '—'),
                'detail_anggota' => $ikan->detail_anggota ?? '—',
                'total_nilai'   => $scoring?->total_nilai ?? 0,
                'juri_nama'     => $scoring?->juri?->name ?? '—',
            ];
        });

        return response()->json($data);
    }

public function getMvpIkan()
    {
        $mvpIkans = Ikan::where('is_mvp', true)
            ->whereHas('peserta', function ($q) {
                $q->where('is_mvp_submitted', true);
            })
            ->with(['peserta', 'bonusPoints'])
            ->get();

        // ★ BUILD RANKINGS CACHE per (kategori + kelas)
        $combos = $mvpIkans->map(function ($i) {
            return $i->kategori . '|' . ($i->kelas ?? '-');
        })->unique()->values();

        $rankCache = []; // [combo => [ikan_id => ['rank_point' => X, 'position' => N]]]
        foreach ($combos as $combo) {
            [$kat, $kls] = explode('|', $combo, 2);
            $kls = ($kls === '-') ? null : $kls;

            $q = Ikan::where('is_locked', true)
                ->whereNotNull('nomor_tank')
                ->where('kategori', $kat)
                ->whereHas('scorings')
                ->with(['scorings', 'bonusPoints']);
            if ($kls !== null) $q->where('kelas', $kls);
            else                $q->whereNull('kelas');
            $pool = $q->get();

            $items = [];
            foreach ($pool as $pi) {
                $avgDetail = [];
                foreach ($pi->scorings as $s) {
                    if ($s->nilai_detail && is_array($s->nilai_detail)) {
                        foreach ($s->nilai_detail as $kt => $fields) {
                            if (!is_array($fields)) continue;
                            foreach ($fields as $fid => $val) {
                                if (!isset($avgDetail[$kt][$fid])) {
                                    $avgDetail[$kt][$fid] = ['sum' => 0, 'count' => 0];
                                }
                                $avgDetail[$kt][$fid]['sum']   += (float)($val ?? 0);
                                $avgDetail[$kt][$fid]['count']++;
                            }
                        }
                    }
                }
                $finalAvg = [];
                foreach ($avgDetail as $kt => $f) {
                    foreach ($f as $fid => $d) {
                        $finalAvg[$kt][$fid] = $d['count'] > 0 ? $d['sum'] / $d['count'] : 0;
                    }
                }
                $grandEdited  = $pi->scorings->first(function ($s) { return $s->edited_by_grand_juri; });
                $defectSource = $grandEdited ?: $pi->scorings->sortByDesc('updated_at')->first();
                $merged = [
                    'raw_head_penalty'    => ['0'],
                    'raw_face_penalty'    => ['0'],
                    'raw_body_penalty'    => ['0'],
                    'raw_finnage_penalty' => ['0'],
                ];
                if ($defectSource) {
                    $merged['raw_head_penalty']    = $defectSource->raw_head_penalty    ?: ['0'];
                    $merged['raw_face_penalty']    = $defectSource->raw_face_penalty    ?: ['0'];
                    $merged['raw_body_penalty']    = $defectSource->raw_body_penalty    ?: ['0'];
                    $merged['raw_finnage_penalty'] = $defectSource->raw_finnage_penalty ?: ['0'];
                }
                $items[] = [
                    'ikan_id'     => $pi->id,
                    'total_point' => (float) PointCalculator::hitungPoint($pi->kategori, $finalAvg, $merged),
                    'total_bonus' => (int) $pi->bonusPoints->sum('points'),
                ];
            }

            $ranked = PointCalculator::hitungRankPoints($items, 'total_point');
            $cache  = [];
            foreach ($ranked as $idx => $r) {
                $cache[$r['ikan_id']] = [
                    'rank_point' => $r['rank_point'],
                    'position'   => $idx + 1,
                ];
            }
            $rankCache[$combo] = $cache;
        }

        // ★ GROUP BY detail_anggota
        $grouped = $mvpIkans->groupBy(function ($ikan) {
            $key = trim($ikan->detail_anggota ?? '');
            return $key === '' ? '(Tanpa Kota/Team)' : $key;
        });

        $data = [];
        foreach ($grouped as $detailAnggota => $ikanList) {
            $totalTeamRankPoint = 0;
            $totalRankOnly      = 0;

            $ikanDetails = $ikanList->map(function ($ikan) use ($rankCache, &$totalTeamRankPoint, &$totalRankOnly) {
                $combo    = $ikan->kategori . '|' . ($ikan->kelas ?? '-');
                $rankInfo = $rankCache[$combo][$ikan->id] ?? ['rank_point' => 0, 'position' => 0];
                $rankPt   = (int) $rankInfo['rank_point'];
                $position = (int) $rankInfo['position'];
                $bonus    = (int) $ikan->bonusPoints->sum('points');
                $final    = $rankPt + $bonus;

                $totalRankOnly      += $rankPt;
                $totalTeamRankPoint += $final;

                return [
                    'ikan_id'          => $ikan->id,
                    'nama_peserta'     => $ikan->nama_peserta ?? '—',
                    'kategori'         => $ikan->kategori,
                    'kelas'            => $ikan->kelas,
                    'nomor_tank'       => $ikan->nomor_tank ?? '-',
                    'bonus_list'       => $ikan->bonusPoints->pluck('bonus_type')->toArray(),
                    'total_bonus'      => $bonus,
                    'rank_point'       => $rankPt,            // ★ dari posisi (tanpa bonus)
                    'final_rank_point' => $final,             // ★ rank + bonus
                    'position'         => $position,          // ★ 1..10 atau 0 (tidak masuk)
                ];
            })->values()->toArray();

            $jumlahPeserta = $ikanList->pluck('nama_peserta')->unique()->count();

            $data[] = [
                'detail_anggota'        => $detailAnggota,
                'total_mvp'             => $ikanList->count(),
                'jumlah_peserta'        => $jumlahPeserta,
                'total_rank_only'       => $totalRankOnly,
                'total_team_rank_point' => $totalTeamRankPoint,
                'ikans'                 => $ikanDetails,
            ];
        }

        usort($data, function ($a, $b) {
            return strcmp($a['detail_anggota'], $b['detail_anggota']);
        });

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
            $limit = 10; // ★ FIXED: Top 10 only sesuai sistem rank point baru

            $ikans = Ikan::where('is_locked', true)
                ->whereNotNull('nomor_tank')
                ->whereHas('scorings', function ($q) {
                })
                ->with(['peserta', 'scorings' => function ($q) {
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

                // ★ Ambil defect terbaru
                $defectSource = $scorings->first(function ($s) { return $s->edited_by_grand_juri; }) 
                            ?? $scorings->sortByDesc('updated_at')->first();
                $mergedDefect = [
                    'raw_head_penalty'    => $defectSource ? ($defectSource->raw_head_penalty    ?: ['0']) : ['0'],
                    'raw_face_penalty'    => $defectSource ? ($defectSource->raw_face_penalty    ?: ['0']) : ['0'],
                    'raw_body_penalty'    => $defectSource ? ($defectSource->raw_body_penalty    ?: ['0']) : ['0'],
                    'raw_finnage_penalty' => $defectSource ? ($defectSource->raw_finnage_penalty ?: ['0']) : ['0'],
                ];
                $totalPoint = PointCalculator::hitungPoint($ikan->kategori, $finalAvgDetail, $mergedDefect);
                $totalBonus = (int) $ikan->bonusPoints->sum('points');
                $finalPoint = $totalPoint + $totalBonus;

                $allItems[] = [
                    'ikan_id'           => $ikan->id,
                    'nama_peserta'      => $ikan->nama_peserta ?? 'Unknown',
                    'detail_anggota'    => $ikan->detail_anggota ?? '—',
                    'kategori'          => $ikan->kategori,
                    'kelas'             => $ikan->kelas ?? '-',
                    'nomor_tank'        => $ikan->nomor_tank,
                    'total_nilai_semua' => $totalNilaiSemua,
                    'total_point'       => (float) $totalPoint,
                    'total_bonus'       => $totalBonus,
                    'final_point'       => (float) $finalPoint,
                    'jumlah_juri'       => $jumlahJuriYangNilai,
                ];
            }

            // ★ Sort DESC by total_point (raw); bonus dipindah ke rank_point
            $ranked = PointCalculator::hitungRankPoints($allItems, 'total_point');

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
            })
            ->with(['peserta', 'scorings' => function ($q) {
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

            // ★ Ambil defect data: prioritas Grand Juri edit, fallback ke scoring terbaru
            $defectSource = $scorings->first(function ($s) { return $s->edited_by_grand_juri; })
                         ?? $scorings->sortByDesc('updated_at')->first();
            $mergedDefect = [
                'raw_head_penalty'    => $defectSource ? ($defectSource->raw_head_penalty    ?: ['0']) : ['0'],
                'raw_face_penalty'    => $defectSource ? ($defectSource->raw_face_penalty    ?: ['0']) : ['0'],
                'raw_body_penalty'    => $defectSource ? ($defectSource->raw_body_penalty    ?: ['0']) : ['0'],
                'raw_finnage_penalty' => $defectSource ? ($defectSource->raw_finnage_penalty ?: ['0']) : ['0'],
            ];

            $totalPoint = PointCalculator::hitungPoint($ikan->kategori, $finalAvgDetail, $mergedDefect);
            $totalBonus = (int) $ikan->bonusPoints->sum('points');
            $finalPoint = $totalPoint + $totalBonus;
            $noKelasKategori = ['Bonsai', 'Jumbo'];

            if ($scope === 'per_kategori' || in_array($ikan->kategori, $noKelasKategori) || !$ikan->kelas) {
                $key = $ikan->kategori;
            } else {
                $key = $ikan->kategori . ' - Kelas ' . $ikan->kelas;
            }

            $groups[$key][] = [
                'ikan_id'           => $ikan->id,
                'nama_peserta'      => $ikan->nama_peserta ?? 'Unknown',
                'detail_anggota'    => $ikan->detail_anggota ?? '—',
                'kategori'          => $ikan->kategori,
                'kelas'             => $ikan->kelas ?? '-',
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
            // ★ Sort DESC by total_point (raw); bonus pindah ke rank
            $ranked   = PointCalculator::hitungRankPoints($items, 'total_point');
            $totalAll = count($ranked);
            // ★ LIMIT TOP 10 per group
            $top10    = array_slice($ranked, 0, 10);
            $result[] = [
                'group_name' => $name . ' — Top 10 dari ' . $totalAll,
                'total'      => $totalAll,
                'data'       => $top10,
            ];
        }
        usort($result, function ($a, $b) { return strcmp($a['group_name'], $b['group_name']); });

        return response()->json($result);
    }

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

        $sync = $this->sheetsSync;
        app()->terminating(function () use ($sync) {
            if (!$sync->isReady()) return;
            try { $sync->syncMvp(); } catch (\Exception $e) { \Log::error('Async-sync MVP (add bonus): ' . $e->getMessage()); }
        });

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

        // ★ AUTO-SYNC MVP
        try { $this->sheetsSync->syncMvp(); } catch (\Exception $e) { \Log::error('Auto-sync MVP gagal (remove bonus): ' . $e->getMessage()); }

        return response()->json([
            'success' => true,
            'message' => 'Bonus "' . self::BONUS_TYPES[$request->bonus_type] . '" berhasil dihapus.',
        ]);
    }

    public function exportExcel(Request $request)
    {
        $sheets = $request->query('sheets', 'all');
        $valid  = ['all', 'daftar', 'mvp', 'ranking_kk', 'ranking_k', 'ranking_global', 'nominasi', 'nilai_murni'];

        if (!in_array($sheets, $valid)) {
            $sheets = 'all';
        }

        $label = match ($sheets) {
            'daftar'         => 'Daftar_Ikan',
            'mvp'            => 'Data_MVP',
            'ranking_kk'     => 'Ranking_Per_Kat_Kelas',
            'ranking_k'      => 'Ranking_Per_Kategori',
            'ranking_global' => 'Rank_Global',
            'nominasi'       => 'Hasil_Nominasi',
            'nilai_murni'    => 'Nilai_Murni_Juri',
            default          => 'Semua_Data',
        };

        $fileName = 'LCI_GrandJuri_' . $label . '_' . now()->format('Y-m-d_His') . '.xlsx';

        return Excel::download(new GrandJuriExport($sheets), $fileName);
    }

    /* ═══════════════════════════════════════════
       KUNCI SEMUA — Lock semua ikan yang sudah dinilai sekaligus
       ═══════════════════════════════════════════ */
    public function kunciSemua()
    {
        // Cari semua ikan yang sudah ada scoring tapi belum dikunci
        $candidates = Ikan::whereNotNull('nomor_tank')
            ->where('is_locked', false)
            ->whereHas('scorings')
            ->pluck('id');

        if ($candidates->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada peserta yang bisa dikunci. Semua sudah terkunci atau belum ada nilai.',
                'count'   => 0,
            ]);
        }

        $count = Ikan::whereIn('id', $candidates)->update(['is_locked' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengunci ' . $count . ' peserta.',
            'count'   => $count,
        ]);
    }
}