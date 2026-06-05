<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Scoring;
use App\Models\Peserta;
use App\Models\Ikan;
use App\Models\User;
use App\Helpers\PointCalculator;
use App\Models\ScoringPointConfig;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AdminExport;
use App\Services\SheetsSyncService;

class AdminDashboardController extends Controller
{
    protected $sheetsSync;

    public function __construct(SheetsSyncService $sheetsSync)
    {
        $this->sheetsSync = $sheetsSync;
    }

    public function index()
    {
        return view('dashboard.admin', ['user' => auth()->user()->fresh()]);
    }

    // LIST PESERTA UNTUK DROPDOWN ADMIN
    public function getListPesertas()
    {
        $pesertas = Peserta::orderBy('nama_peserta')->get()->map(function ($p) {
            return [
                'id' => $p->id,
                'text' => $p->nama_peserta . ' (' . $p->detail_anggota . ')'
            ];
        });
        return response()->json($pesertas);
    }

    // TAMBAH IKAN DARI ADMIN
    public function storeIkanAdmin(Request $request)
    {
        $request->validate([
            'peserta_id' => 'required|exists:pesertas,id',
            'kategori'   => 'required|string|max:255',
            'kelas'      => 'required|string|max:10',
        ]);

        $peserta = Peserta::find($request->peserta_id);

        Ikan::create([
            'peserta_id' => $request->peserta_id,
            'nama_peserta' => $peserta ? $peserta->nama_peserta : '-',
            'detail_anggota' => $peserta ? $peserta->detail_anggota : '-',
            'jenis_keanggotaan' => $peserta ? $peserta->jenis_keanggotaan : 'perorangan', // ★ SNAPSHOT
            'kategori'   => $request->kategori,
            'kelas'      => $request->kelas,
        ]);

        return response()->json(['success' => true, 'message' => 'Ikan berhasil didaftarkan ke peserta.']);
    }

    public function registerPesertaIkan(Request $request)
    {
        $noKelasKategori = ['Bonsai', 'Jumbo'];

        $rules = [
            'user_id'           => 'required|exists:users,id',
            'nama_peserta'      => 'required|string|max:255',
            'kategori'          => 'required|string|max:255',
            'jenis_keanggotaan' => 'required|in:perorangan,team',
            'detail_anggota'    => 'required|string|max:255',
        ];

        if (in_array($request->kategori, $noKelasKategori)) {
            $rules['kelas'] = 'nullable';
        } else {
            $rules['kelas'] = 'required|string|max:10';
        }

        $request->validate($rules);

        $user = User::find($request->user_id);

        $peserta = Peserta::firstOrCreate(
            ['user_id' => $request->user_id],
            [
                'nama_peserta'      => $user->name,
                'jenis_keanggotaan' => 'perorangan',
                'detail_anggota'    => '-',
            ]
        );

        // Admin bisa mengedit jenis keanggotaan dan detail
        $peserta->jenis_keanggotaan = $request->jenis_keanggotaan;
        $peserta->detail_anggota = $request->detail_anggota;
        $peserta->save();

        $kelas = in_array($request->kategori, $noKelasKategori) ? null : $request->kelas;

        $ikan = Ikan::create([
            'peserta_id'   => $peserta->id,
            'nama_peserta' => $peserta->nama_peserta,
            'detail_anggota' => $peserta->detail_anggota,
            'jenis_keanggotaan' => $peserta->jenis_keanggotaan, // ★ SNAPSHOT
            'kategori'     => $request->kategori,
            'kelas'        => $kelas,
            'dibuat_oleh'  => 'admin',
        ]);

                // ★ AUTO-SYNC KE GOOGLE SHEETS
        try {
            if ($this->sheetsSync->isReady()) {
                $this->sheetsSync->syncSemuaPeserta();
            }
        } catch (\Exception $e) {
            \Log::warning('Sheets sync gagal: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Ikan berhasil didaftarkan untuk <strong>' . $user->name . '</strong>.',
        ]);
    }

    public function getDashboardStats()
    {
        $totalIkan = Ikan::whereNotNull('nomor_tank')->count();

        $juriAktif = Scoring::whereNotNull('juri_id')->distinct('juri_id')->count('juri_id');

        // Hitung status & kategori dari data yang sudah ada
        $allIkanIds = Ikan::whereNotNull('nomor_tank')->pluck('id');
        $latestScorings = Scoring::whereIn('ikan_id', $allIkanIds)
            ->selectRaw('ikan_id, MAX(id) as latest_id')
            ->groupBy('ikan_id')
            ->pluck('latest_id');
        $latestScoringList = Scoring::whereIn('id', $latestScorings)->get();

        $sudahDinilai = $latestScoringList->where('edited_by_grand_juri', false)->count();
        $grandEdited  = $latestScoringList->where('edited_by_grand_juri', true)->count();
        $belumDinilai = max(0, $totalIkan - $latestScoringList->count());

        $perKategori = Ikan::whereNotNull('nomor_tank')
            ->selectRaw('kategori, COUNT(*) as total')
            ->groupBy('kategori')
            ->orderByDesc('total')
            ->pluck('total', 'kategori')
            ->toArray();

        // Hitung total & rata-rata dari SEMUA juri per ikan
        $ikanTotalMap = Ikan::whereNotNull('nomor_tank')
            ->whereHas('scorings', function ($q) {
                $q->whereNotNull('total_nilai');
            })
            ->with('scorings')
            ->get()
            ->mapWithKeys(function ($ikan) {
                $total = 0;
                foreach ($ikan->scorings as $s) {
                    $total += $s->total_nilai ?? 0;
                }
                return [$ikan->id => $total];
            })
            ->filter();

        $avgScore = $ikanTotalMap->count() > 0 ? round($ikanTotalMap->avg()) : 0;

        $top10 = Ikan::whereIn('id', $ikanTotalMap->keys())
            ->whereNotNull('nomor_tank')
            ->with(['peserta', 'scorings' => function ($q) {
                $q->whereNotNull('total_nilai');
            }])
            ->get()
            ->map(function ($ikan) use ($ikanTotalMap) {
                $avgDetail = [];
                foreach ($ikan->scorings as $s) {
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
                foreach ($avgDetail as $kat => $fields) {
                    $finalAvgDetail[$kat] = [];
                    foreach ($fields as $fid => $d) {
                        $finalAvgDetail[$kat][$fid] = $d['count'] > 0
                            ? $d['sum'] / $d['count']
                            : 0;
                    }
                }
                $point = PointCalculator::hitungPoint($ikan->kategori, $finalAvgDetail);

                return [
                    'nama'       => $ikan->nama_peserta ?? 'Unknown',
                    'total'      => $ikanTotalMap[$ikan->id],
                    'point'      => (float) $point,
                    'kategori'   => $ikan->kategori ?? '—',
                    'kelas'      => $ikan->kelas ?? '—',
                    'nomor_tank' => $ikan->nomor_tank ?? '—',
                ];
            })
            ->sortByDesc('point')
            ->take(10)
            ->values()
            ->toArray();

        // SELALU gunakan global range untuk card sisa tank
        $minGlobal = (int) (\DB::table('settings')->where('key', 'tank_range_min')->value('value') ?? 1);
        $maxGlobal = (int) (\DB::table('settings')->where('key', 'tank_range_max')->value('value') ?? 1000);
        $maxTankTotal = $maxGlobal - $minGlobal + 1;

        $totalPesertaUnik = Ikan::whereNotNull('nomor_tank')
            ->distinct('peserta_id')
            ->count('peserta_id');

        $sisaTank = max(0, $maxTankTotal - $totalIkan);

        return response()->json([
            'total_peserta'  => $totalIkan,
            'sudah_dinilai'  => $sudahDinilai,
            'grand_edited'   => $grandEdited,
            'belum_dinilai'  => $belumDinilai,
            'juri_aktif'     => $juriAktif,
            'rata_rata'      => $avgScore,
            'total_peserta_unik' => $totalPesertaUnik,
            'sisa_tank'          => $sisaTank,
            'max_tank'       => $maxTankTotal,
            'global_range_min' => $minGlobal,
            'global_range_max' => $maxGlobal,
            'per_kategori'   => $perKategori,
            'top_10'         => $top10,
        ]);
    }

    public function getScoringData(Request $request)
    {
        $query = Ikan::where(function($q) {
            $q->whereNotNull('nomor_tank')
            ->orWhereHas('scorings');
        })
            /* ★ FIX: Hapus ->latest()->limit(1), load SEMUA scorings */
        ->with(['peserta', 'scorings' => function ($q) {
            $q->orderBy('created_at', 'desc');
        }, 'scorings.juri', 'scorings.grandJuri', 'bonusPoints']);

        if ($request->filled('search')) {
            $query->whereHas('peserta', function ($q) use ($request) {
                $q->where('nama_peserta', 'LIKE', '%' . $request->search . '%');
            });
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        $data = $query->orderBy('nomor_tank')->get()->map(function ($ikan) {
            $peserta = $ikan->peserta;
            $scorings = $ikan->scorings;
            $latestScoring = $scorings->first();

            /* ★ Build juri list (semua juri) */
            $juriList = [];
            $grandJuriEditors = [];
            $grandJuriName = null;
            $latestNilai = null;
            $latestKelas = null;

            foreach ($scorings as $s) {
                if ($s->juri) {
                    $juriList[] = [
                        'name'     => $s->juri->name,
                        'is_grand' => false,
                    ];
                }
                if ($s->edited_by_grand_juri && $s->grandJuri) {
                    $gjName = $s->grandJuri->name;
                    if (!in_array($gjName, $grandJuriEditors)) {
                        $grandJuriEditors[] = $gjName;
                    }
                    $grandJuriName = $gjName;
                }
            }

            foreach ($grandJuriEditors as $gjName) {
                $juriList[] = [
                    'name'      => $gjName,
                    'is_grand'  => true,
                    'is_editor' => true,
                ];
            }

            if ($latestScoring) {
                $latestNilai = $latestScoring->nilai_detail;
                $latestKelas = $latestScoring->kelas;
            }

            /* ★ Hitung dari SEMUA juri (sama persis logika Grand Jury) */
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

            $pointConfig = ScoringPointConfig::where('kategori', $ikan->kategori)->first();

            $allScoringsData = $scorings->map(function ($s) {
                $defectInput = [
                    'raw_head_penalty'    => $s->raw_head_penalty ?? ['0'],
                    'raw_face_penalty'    => $s->raw_face_penalty ?? ['0'],
                    'raw_body_penalty'    => $s->raw_body_penalty ?? ['0'],
                    'raw_finnage_penalty' => $s->raw_finnage_penalty ?? ['0'],
                ];
                $defectEval = \App\Helpers\PointCalculator::evaluateDefects($defectInput);

                return [
                    'juri_name'         => $s->juri ? $s->juri->name : '—',
                    'is_grand'          => false,
                    'edited_by_grand'   => (bool) $s->edited_by_grand_juri,
                    'grand_juri_name'   => ($s->edited_by_grand_juri && $s->grandJuri) ? $s->grandJuri->name : null,
                    'nilai_detail'      => $s->nilai_detail,
                    'total_nilai'       => $s->total_nilai ?? 0,
                    'raw_head_penalty'  => $s->raw_head_penalty,
                    'raw_face_penalty'  => $s->raw_face_penalty,
                    'raw_body_penalty'  => $s->raw_body_penalty,
                    'raw_finnage_penalty'=> $s->raw_finnage_penalty,
                    'defect_eval'       => $defectEval,
                ];
            })->values()->toArray();

            $detailListPerJuri = $scorings->map(function ($s) {
                return [
                    'juri_name'   => $s->juri ? $s->juri->name : '—',
                    'is_grand'    => false,
                    'total_nilai' => $s->total_nilai ?? 0,
                ];
            })->values()->toArray();

            return [
                'id'                 => $ikan->id,
                'peserta_id'         => $ikan->peserta_id,
                'nama_peserta'       => $ikan->nama_peserta ?? 'Unknown',
                'kategori'           => $ikan->kategori,
                'kelas'              => $latestKelas ?? $ikan->kelas ?? '-',
                'nomor_tank'         => $ikan->nomor_tank,
                'detail_anggota' => $ikan->detail_anggota ?? '-',
                'juri_list'          => $juriList,
                'grand_juri_nama'    => $grandJuriName,
                'total_nilai'        => $latestScoring?->total_nilai ?? 0,
                'total_nilai_semua'  => $totalNilaiSemua,
                'jumlah_juri'        => $jumlahJuriYangNilai,
                'nilai_detail'       => $latestNilai,
                'status'             => $latestScoring ? ($latestScoring->edited_by_grand_juri ? 'Grand Juri Edit' : 'Sudah Dinilai') : 'Belum Dinilai',
                'total_point'        => (float) $totalPoint,
                'bonus_list'         => $ikan->bonusPoints->pluck('bonus_type')->toArray(),
                'total_bonus'        => (int) $ikan->bonusPoints->sum('points'),
                'final_point'        => (float) $totalPoint + (int) $ikan->bonusPoints->sum('points'),
                'point_config'       => $pointConfig ? [
                    'overall' => (float)$pointConfig->overall_bobot,
                    'head'    => (float)$pointConfig->head_bobot,
                    'face'    => (float)$pointConfig->face_bobot,
                    'body'    => (float)$pointConfig->body_bobot,
                    'marking' => (float)$pointConfig->marking_bobot,
                    'pearl'   => (float)$pointConfig->pearl_bobot,
                    'color'   => (float)$pointConfig->color_bobot,
                    'finnage' => (float)$pointConfig->finnage_bobot,
                ] : null,
                'point_breakdown'    => $finalAvgDetail ? PointCalculator::hitungBreakdown($ikan->kategori, $finalAvgDetail) : null,
                'all_scorings'       => $allScoringsData,
                'detail_list_per_juri' => $detailListPerJuri,
            ];
        })->toArray();

        if ($request->filled('status')) {
            $filter = $request->status;
            $data = array_filter($data, function ($item) use ($filter) {
                if ($filter === 'dinilai') return $item['status'] === 'Sudah Dinilai';
                if ($filter === 'grand')   return $item['status'] === 'Grand Juri Edit';
                if ($filter === 'belum')   return $item['status'] === 'Belum Dinilai';
                return true;
            });
            $data = array_values($data);
        }

        return response()->json($data);
    }

    /* ═══════════════════════════════════════════
       CREATE USER, CHANGE ROLE, DELETE
       ═══════════════════════════════════════════ */
    public function createUser(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role'     => 'required|in:admin,juri,grand_juri,user',
        ]);

        $userId = \DB::table('users')->insertGetId([
            'name'           => $request->name,
            'email'          => $request->email,
            'password'       => bcrypt($request->password),
            'plain_password' => $request->password,
            'role'           => $request->role,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        \App\Models\PasswordHistory::create([
            'user_id'     => $userId,
            'old_password' => null,
            'new_password' => $request->password,
            'changed_by'   => auth()->user()->name,
        ]);

        // ★ AUTO-SYNC NAMA JURI
        try { $this->sheetsSync->syncNamaJuri(); } catch (\Exception $e) { \Log::warning('Sheets sync nama juri gagal (create user): ' . $e->getMessage()); }

        return response()->json([
            'success' => true, 
            'message' => 'User "' . $request->name . '" berhasil ditambahkan.'
        ]);
    }

    public function changeRole(Request $request)
    {
        $request->validate(['user_id' => 'required|exists:users,id', 'new_role' => 'required|in:admin,juri,grand_juri,user']);
        $user = User::find($request->user_id);
        if ($user->id === auth()->id()) return response()->json(['success' => false, 'message' => 'Tidak bisa mengubah role sendiri.'], 403);
        $user->update(['role' => $request->new_role]);

        // ★ AUTO-SYNC NAMA JURI
        try { $this->sheetsSync->syncNamaJuri(); } catch (\Exception $e) { \Log::warning('Sheets sync nama juri gagal (change role): ' . $e->getMessage()); }

        return response()->json(['success' => true, 'message' => 'Role berhasil diubah.']);
    }

    public function deleteUser(Request $request)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);
        $user = User::find($request->user_id);
        if ($user->id === auth()->id()) return response()->json(['success' => false, 'message' => 'Tidak bisa menghapus akun sendiri.'], 403);
        $name = $user->name;

        // 1. Hapus data scoring jika user pernah menjadi Juri
        \App\Models\Scoring::where('juri_id', $user->id)->delete();

        // 2. Hapus data scoring jika user pernah menjadi Grand Juri
        \App\Models\Scoring::where('grand_juri_id', $user->id)->delete();

        // 3. Hapus data peserta & ikan jika user adalah peserta biasa
        $peserta = \App\Models\Peserta::where('user_id', $user->id)->first();
        if ($peserta) {
            $ikanIds = \App\Models\Ikan::where('peserta_id', $peserta->id)->pluck('id');
            if ($ikanIds->isNotEmpty()) {
                \App\Models\Scoring::whereIn('ikan_id', $ikanIds)->delete();
                \App\Models\Ikan::whereIn('id', $ikanIds)->delete();
            }
            $peserta->delete();
        }

        // 4. Baru hapus user-nya
        $user->delete();

        // ★ AUTO-SYNC KE GOOGLE SHEETS
        try {
            if ($this->sheetsSync->isReady()) {
                $this->sheetsSync->syncSemuaPeserta();
                $this->sheetsSync->syncNamaJuri();
                $this->sheetsSync->syncHasilJuri();
            }
        } catch (\Exception $e) {
            \Log::warning('Sheets sync gagal saat hapus user: ' . $e->getMessage());
        }

        return response()->json(['success' => true, 'message' => 'User "' . $name . '" berhasil dihapus.']);
    }

        public function getStatDetail(Request $request)
    {
        $type = $request->query('type');
        if (!$type) return response()->json(['error' => 'Tipe wajib'], 422);

        $allIkanIds = Ikan::whereNotNull('nomor_tank')->pluck('id');
        $latestRows = Scoring::whereIn('ikan_id', $allIkanIds)
            ->selectRaw('ikan_id, MAX(id) as lid, MAX(CASE WHEN edited_by_grand_juri = 1 THEN 1 ELSE 0 END) as edited_by_grand_juri')
            ->groupBy('ikan_id')
            ->get()
            ->keyBy('ikan_id');

        switch ($type) {
            case 'total_ikan':
                $rows = Ikan::whereNotNull('nomor_tank')->with('peserta')->orderBy('nomor_tank')->get()->map(function ($i, $idx) {
                    return [$idx + 1, $i->nama_peserta ?? 'Unknown', $i->nomor_tank, $i->kategori ?? '—', $i->kelas ?? '—'];
                })->toArray();
                return response()->json(['title' => 'Total Ikan Terdaftar', 'columns' => ['#', 'PESERTA', 'TANK', 'KATEGORI', 'KELAS'], 'rows' => $rows]);

            case 'total_peserta':
                $rows = Ikan::whereNotNull('nomor_tank')
                    ->selectRaw('peserta_id, COUNT(*) as jml')
                    ->groupBy('peserta_id')
                    ->with('peserta')
                    ->orderByDesc('jml')
                    ->get()
                    ->map(function ($i, $idx) {
                        return [$idx + 1, $i->nama_peserta ?? 'Unknown', $i->jml];
                    })->toArray();
                return response()->json(['title' => 'Total Peserta', 'columns' => ['#', 'PESERTA', 'JUMLAH IKAN'], 'rows' => $rows]);

            case 'sudah_dinilai':
                $sudahIds = $latestRows->filter(fn($r) => !$r->edited_by_grand_juri)->keys()->toArray();
                $stats = Scoring::whereIn('ikan_id', $sudahIds)
                    ->where('submitted_to_grand', true)
                    ->selectRaw('ikan_id, COUNT(DISTINCT juri_id) as jml, COALESCE(SUM(total_nilai),0) as total')
                    ->groupBy('ikan_id')->get()->keyBy('ikan_id');
                $rows = Ikan::whereIn('id', $sudahIds)->with('peserta')->orderBy('nomor_tank')->get()->map(function ($i, $idx) use ($stats) {
                    $s = $stats[$i->id];
                    return [$idx + 1, $i->nama_peserta ?? 'Unknown', $i->nomor_tank, $i->kategori ?? '—', $i->kelas ?? '—', $s ? $s->jml : 0, $s ? $s->total : 0];
                })->toArray();
                return response()->json(['title' => 'Sudah Dinilai Juri', 'columns' => ['#', 'PESERTA', 'TANK', 'KATEGORI', 'KELAS', 'JURI', 'TOTAL NILAI'], 'rows' => $rows]);

            case 'grand_edit':
                $grandIds = $latestRows->filter(fn($r) => $r->edited_by_grand_juri)->keys()->toArray();
                $grandLatestIds = Scoring::whereIn('ikan_id', $grandIds)
                    ->where('edited_by_grand_juri', true)
                    ->selectRaw('ikan_id, MAX(id) as lid')
                    ->groupBy('ikan_id')
                    ->pluck('lid');
                $rows = Scoring::whereIn('id', $grandLatestIds)
                    ->with(['ikan.peserta', 'grandJuri'])
                    ->orderByDesc('total_nilai')
                    ->get()
                    ->map(function ($s, $idx) {
                        return [$idx + 1, $s->ikan->peserta->nama_peserta ?? 'Unknown', $s->ikan->nomor_tank, $s->ikan->kategori ?? '—', $s->kelas ?? $s->ikan->kelas ?? '—', $s->grandJuri ? $s->grandJuri->name : '—', $s->total_nilai ?? 0];
                    })->toArray();
                return response()->json(['title' => 'Grand Juri Edit', 'columns' => ['#', 'PESERTA', 'TANK', 'KATEGORI', 'KELAS', 'GRAND JURI EDITOR', 'TOTAL NILAI'], 'rows' => $rows]);

            case 'belum_dinilai':
                $scoredIds = $latestRows->keys()->toArray();
                $rows = Ikan::whereNotNull('nomor_tank')
                    ->whereNotIn('id', $scoredIds)
                    ->with('peserta')
                    ->orderBy('nomor_tank')
                    ->get()
                    ->map(function ($i, $idx) {
                        return [$idx + 1, $i->nama_peserta ?? 'Unknown', $i->nomor_tank, $i->kategori ?? '—', $i->kelas ?? '—'];
                    })->toArray();
                return response()->json(['title' => 'Belum Dinilai', 'columns' => ['#', 'PESERTA', 'TANK', 'KATEGORI', 'KELAS'], 'rows' => $rows]);

            case 'juri_aktif':
                $rows = \DB::table('scorings')
                    ->join('users', 'scorings.juri_id', '=', 'users.id')
                    ->whereNotNull('scorings.juri_id')
                    ->where('scorings.submitted_to_grand', true)
                    ->selectRaw('scorings.juri_id, users.name, users.role, COUNT(DISTINCT scorings.ikan_id) as total')
                    ->groupBy('scorings.juri_id', 'users.name', 'users.role')
                    ->orderByDesc('total')
                    ->get()
                    ->map(function ($r, $idx) {
                        $rl = ['juri' => 'Juri', 'grand_juri' => 'Grand Juri', 'admin' => 'Admin'];
                        return [$idx + 1, $r->name, $rl[$r->role] ?? $r->role, $r->total];
                    })->toArray();
                return response()->json(['title' => 'Juri Aktif', 'columns' => ['#', 'NAMA JURI', 'ROLE', 'PESERTA DINILAI'], 'rows' => $rows]);

            default:
                return response()->json(['error' => 'Tipe tidak valid'], 422);
        }
    }

    public function deleteIkan(Request $request)
    {
        $request->validate([
            'ikan_id' => 'required|exists:ikans,id',
        ]);

        $ikan = Ikan::find($request->ikan_id);

        // Hapus semua nilai scoring terkait ikan ini
        Scoring::where('ikan_id', $ikan->id)->delete();

        // Hapus data ikan
        $ikan->delete();

        // ★ AUTO-SYNC KE GOOGLE SHEETS
        try {
            if ($this->sheetsSync->isReady()) {
                $this->sheetsSync->syncSemuaPeserta();
                $this->sheetsSync->syncHasilJuri();
            }
        } catch (\Exception $e) {
            \Log::warning('Sheets sync gagal saat hapus ikan: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true, 
            'message' => 'Data ikan beserta nilai penilaiannya berhasil dihapus.'
        ]);
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

    public function bulkDeleteIkan(Request $request)
    {
        $request->validate([
            'ikan_ids'   => 'required|array|min:1',
            'ikan_ids.*' => 'integer|exists:ikans,id',
        ]);

        $ikanIds = $request->ikan_ids;

        // 1. Hapus semua scoring terkait
        Scoring::whereIn('ikan_id', $ikanIds)->delete();

        // 2. Hapus semua bonus points terkait
        \App\Models\IkanBonusPoint::whereIn('ikan_id', $ikanIds)->delete();

        // 3. Hapus data ikan
        $deleted = Ikan::whereIn('id', $ikanIds)->delete();

        // ★ AUTO-SYNC KE GOOGLE SHEETS
        try {
            if ($this->sheetsSync->isReady()) {
                $this->sheetsSync->syncSemuaPeserta();
                $this->sheetsSync->syncHasilJuri();
            }
        } catch (\Exception $e) {
            \Log::warning('Sheets sync gagal saat bulk delete ikan: ' . $e->getMessage());
        }

        return response()->json([
            'success'       => true,
            'message'       => $deleted . ' data ikan beserta nilai penilaiannya berhasil dihapus.',
            'deleted_count' => $deleted,
        ]);
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

        // ★ AUTO-SYNC NILAI JURI & MVP
        try { $this->sheetsSync->syncNilaiJuri(); } catch (\Exception $e) { \Log::error('Auto-sync NILAI JURI gagal (add bonus): ' . $e->getMessage()); }
        try { $this->sheetsSync->syncMvp(); } catch (\Exception $e) { \Log::error('Auto-sync MVP gagal (add bonus): ' . $e->getMessage()); }

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

        // ★ AUTO-SYNC NILAI JURI & MVP
        try { $this->sheetsSync->syncNilaiJuri(); } catch (\Exception $e) { \Log::error('Auto-sync NILAI JURI gagal (remove bonus): ' . $e->getMessage()); }
        try { $this->sheetsSync->syncMvp(); } catch (\Exception $e) { \Log::error('Auto-sync MVP gagal (remove bonus): ' . $e->getMessage()); }

        return response()->json([
            'success' => true,
            'message' => 'Bonus "' . self::BONUS_TYPES[$request->bonus_type] . '" berhasil dihapus.',
        ]);
    }

    public function getPesertaByUser(Request $request)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);
        
        $peserta = Peserta::where('user_id', $request->user_id)->first();

        if ($peserta) {
            return response()->json([
                'found' => true,
                'jenis_keanggotaan' => $peserta->jenis_keanggotaan,
                'detail_anggota' => $peserta->detail_anggota,
            ]);
        }

        return response()->json(['found' => false]);
    }

    /* ═══════════════════════════════════════════
    DETAIL RIWAYAT PESERTA PER USER
    ═══════════════════════════════════════════ */
    public function getUserPesertaDetail(Request $request)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);

        $user    = User::find($request->user_id);
        $peserta = Peserta::where('user_id', $request->user_id)->first();

        $currentProfile = null;
        $ikansList      = [];
        $uniqueCombos   = [];

        if ($peserta) {
            $currentProfile = [
                'nama_peserta'      => $peserta->nama_peserta,
                'jenis_keanggotaan' => $peserta->jenis_keanggotaan,
                'detail_anggota'    => $peserta->detail_anggota,
                'is_mvp_submitted'  => (bool) $peserta->is_mvp_submitted,
                'updated_at'        => $peserta->updated_at
                    ? $peserta->updated_at->format('d M Y H:i')
                    : null,
            ];

            $rawIkans = $peserta->ikans()->orderBy('created_at', 'desc')->get();
            $comboMap = [];

            foreach ($rawIkans as $ikan) {
                $ikansList[] = [
                    'id'                => $ikan->id,
                    'nama_peserta'      => $ikan->nama_peserta ?? '-',
                    'jenis_keanggotaan' => $ikan->jenis_keanggotaan ?? '-',
                    'detail_anggota'    => $ikan->detail_anggota ?? '-',
                    'kategori'          => $ikan->kategori ?? '-',
                    'kelas'             => $ikan->kelas ?? '-',
                    'nomor_tank'        => $ikan->nomor_tank,
                    'dibuat_oleh'       => $ikan->dibuat_oleh ?? 'user',
                    'is_mvp'            => (bool) $ikan->is_mvp,
                    'created_at'        => $ikan->created_at
                        ? $ikan->created_at->format('d M Y H:i')
                        : '-',
                ];

                // Hitung kombinasi unik (nama + jenis + asal)
                $key = ($ikan->nama_peserta ?? '') . '|'
                    . ($ikan->jenis_keanggotaan ?? '') . '|'
                    . ($ikan->detail_anggota ?? '');

                if (!isset($comboMap[$key])) {
                    $comboMap[$key] = [
                        'nama_peserta'      => $ikan->nama_peserta ?? '-',
                        'jenis_keanggotaan' => $ikan->jenis_keanggotaan ?? '-',
                        'detail_anggota'    => $ikan->detail_anggota ?? '-',
                        'count'             => 0,
                    ];
                }
                $comboMap[$key]['count']++;
            }

            $uniqueCombos = array_values($comboMap);
        }

        return response()->json([
            'user' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role ?? 'user',
            ],
            'has_peserta'         => $peserta !== null,
            'current_profile'     => $currentProfile,
            'unique_combinations' => $uniqueCombos,
            'ikans'               => $ikansList,
            'total_ikan'          => count($ikansList),
        ]);
    }

    public function updatePesertaData(Request $request)
    {
        $request->validate([
            'user_id'           => 'required|exists:users,id',
            'nama_peserta'      => 'required|string|max:255',
            'jenis_keanggotaan' => 'required|in:perorangan,team',
            'detail_anggota'    => 'required|string|max:255',
        ]);

        $peserta = Peserta::firstOrCreate(
            ['user_id' => $request->user_id],
            [
                'nama_peserta'      => $request->nama_peserta,
                'jenis_keanggotaan' => 'perorangan',
                'detail_anggota'    => '-',
            ]
        );

        $peserta->nama_peserta      = $request->nama_peserta;
        $peserta->jenis_keanggotaan = $request->jenis_keanggotaan;
        $peserta->detail_anggota    = $request->detail_anggota;
        $peserta->save();

        return response()->json([
            'success' => true,
            'message' => 'Data peserta <strong>' . e($request->nama_peserta) . '</strong> berhasil disimpan.',
        ]);
    }

        /* ═══════════════════════════════════════════
       PENGATURAN RANGE NOMOR UNDIAN
       ═══════════════════════════════════════════ */
    public function getTankRange()
    {
        $ranges = json_decode(\DB::table('settings')->where('key', 'tank_class_ranges')->value('value'), true);
        
        // Format baru: hanya berisi sub-rentang per kategori, tanpa min/max level kelas
        // Contoh: {"A": {"kategori": {"Cencu": {"min":1,"max":30}}}, "B": {"kategori": {"Chginwa": {"min":5,"max":15}}}}
        if (!$ranges) {
            $ranges = [];
        }
        
        return response()->json($ranges);
    }

    public function setTankRange(Request $request)
    {

    $ranges = json_decode($request->ranges, true);

    // ★ Jika array kosong = reset semua, langsung simpan tanpa validasi
    if (is_array($ranges) && empty($ranges)) {
        \DB::table('settings')->updateOrInsert(
            ['key' => 'tank_reset_info'],
            [
                'value' => json_encode([
                    'reason'   => $request->reason,
                    'reset_at' => now()->toDateTimeString(),
                ]),
                'updated_at' => now(),
            ]
        );

        // ★ AUTO-SYNC PESERTA (karena nomor tank dihapus)
        try { $this->sheetsSync->syncSemuaPeserta(); } catch (\Exception $e) { \Log::warning('Sheets sync peserta gagal (reset tank): ' . $e->getMessage()); }

        return response()->json(['success' => true, 'message' => 'Semua nomor tank berhasil direset. Data penilaian tetap aman.']);
    }

    if (!$ranges || !is_array($ranges)) {
            return response()->json([
                'success' => false,
                'message' => 'Format data tidak valid.'
            ], 422);
        }

        $globalMin = (int) (\DB::table('settings')->where('key', 'tank_range_min')->value('value') ?? 1);
        $globalMax = (int) (\DB::table('settings')->where('key', 'tank_range_max')->value('value') ?? 1000);

        $allRanges = [];
        foreach ($ranges as $kelas => $data) {
            if (isset($data['min'])) unset($ranges[$kelas]['min']);
            if (isset($data['max'])) unset($ranges[$kelas]['max']);

            $kategori = isset($data['kategori']) && is_array($data['kategori']) ? $data['kategori'] : [];

            foreach ($kategori as $katName => $katData) {
                $katMin = (int) ($katData['min'] ?? 0);
                $katMax = (int) ($katData['max'] ?? 0);

                if ($katMin < 1 || $katMax < 1 || $katMax < $katMin) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Sub-rentang "' . $katName . '" di Kelas ' . $kelas . ' tidak valid (min harus ≥ 1 dan max ≥ min).'
                    ], 422);
                }

                if ($katMin < $globalMin || $katMax > $globalMax) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Sub-rentang "' . $katName . '" (' . $katMin . '–' . $katMax . ') harus berada dalam Rentang Global (' . $globalMin . '–' . $globalMax . ').'
                    ], 422);
                }

                $allRanges[] = [
                    'kelas'   => $kelas,
                    'kategori' => $katName,
                    'min'     => $katMin,
                    'max'     => $katMax,
                ];
            }
        }

        // ── Validasi: Semua rentang tidak boleh menyentuh batas rentang lain ──
        $count = count($allRanges);
        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                $a = $allRanges[$i];
                $b = $allRanges[$j];

                // Skip hanya jika kelas DAN kategori sama persis (diri sendiri)
                if ($a['kelas'] === $b['kelas'] && $a['kategori'] === $b['kategori']) continue;

                $strictlyInside = ($a['min'] > $b['min'] && $a['max'] < $b['max'])
                              || ($b['min'] > $a['min'] && $b['max'] < $a['max']);
                $strictlyOutside = ($a['max'] < $b['min']) || ($a['min'] > $b['max']);

                if (!$strictlyInside && !$strictlyOutside) {
                    $labelA = in_array($a['kelas'], ['Bonsai', 'Jumbo']) ? $a['kelas'] : 'Kelas ' . $a['kelas'];
                    $labelB = in_array($b['kelas'], ['Bonsai', 'Jumbo']) ? $b['kelas'] : 'Kelas ' . $b['kelas'];
                    $msg = "Rentang <b>{$a['kategori']}</b> di {$labelA} ({$a['min']}–{$a['max']}) menyentuh/melewati batas rentang <b>{$b['kategori']}</b> di {$labelB} ({$b['min']}–{$b['max']}). Pastikan rentang ketat di dalam atau sepenuhnya di luar rentang yang sudah ada.";
                    return response()->json(['success' => false, 'message' => $msg], 422);
                }
            }
        }

        \DB::table('settings')->updateOrInsert(
            ['key' => 'tank_class_ranges'],
            ['value' => json_encode($ranges), 'updated_at' => now()]
        );

        // ★ AUTO-SYNC PLOTING TANK
        try {
            if ($this->sheetsSync->isReady()) {
                $this->sheetsSync->syncPlotingTank();
            }
        } catch (\Exception $e) {
            \Log::warning('Sheets sync ploting gagal: ' . $e->getMessage());
        }

        return response()->json(['success' => true, 'message' => 'Pengaturan sub-rentang nomor berhasil disimpan.']);
    }

    public function resetTankNumbers(Request $request)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        // HANYA kosongkan nomor tank, data penilaian (scorings) TIDAK DIHAPUS
        \App\Models\Ikan::query()->update(['nomor_tank' => null]);

        // Simpan info reset ke tabel settings untuk dibaca user
        \DB::table('settings')->updateOrInsert(
            ['key' => 'tank_reset_info'],
            [
                'value' => json_encode([
                    'reason'   => $request->reason,
                    'reset_at' => now()->toDateTimeString(),
                ]),
                'updated_at' => now(),
            ]
        );

        return response()->json(['success' => true, 'message' => 'Semua nomor tank berhasil direset. Data penilaian tetap aman.']);
    }

    /* ═══════════════════════════════════════════
    RENTANG GLOBAL (FALLBACK)
    ═══════════════════════════════════════════ */
    public function getTankRangeGlobal()
    {
        $min = (int) (\DB::table('settings')->where('key', 'tank_range_min')->value('value') ?? 1);
        $max = (int) (\DB::table('settings')->where('key', 'tank_range_max')->value('value') ?? 1000);
        
        return response()->json(['min' => $min, 'max' => $max]);
    }

    public function setTankRangeGlobal(Request $request)
    {
        $request->validate([
            'min' => 'required|integer|min:1',
            'max' => 'required|integer|min:1|gte:min',
        ]);

        $newMin = (int) $request->min;
        $newMax = (int) $request->max;

        // ═══════════════════════════════════════════════
        // ★ VALIDASI BARU: Cek apakah rentang global baru
        // masih bisa menampung SEMUA sub-rentang per kategori
        // yang sudah dikonfigurasi sebelumnya.
        // ═══════════════════════════════════════════════
        $classRanges = json_decode(
            \DB::table('settings')->where('key', 'tank_class_ranges')->value('value'),
            true
        );

        if (is_array($classRanges) && !empty($classRanges)) {
            $conflicts = [];
            $noKelasKategori = ['Bonsai', 'Jumbo'];

            foreach ($classRanges as $kelas => $data) {
                if (!isset($data['kategori']) || !is_array($data['kategori'])) continue;

                foreach ($data['kategori'] as $katName => $katData) {
                    $katMin = (int) ($katData['min'] ?? 0);
                    $katMax = (int) ($katData['max'] ?? 0);
                    if ($katMin < 1 || $katMax < 1) continue;

                    // Sub-rentang harus berada di DALAM rentang global baru
                    if ($katMin < $newMin || $katMax > $newMax) {
                        $label = in_array($kelas, $noKelasKategori)
                            ? $kelas
                            : 'Kelas ' . $kelas;
                        $conflicts[] = '<b>' . e($katName) . '</b> di ' . $label
                                    . ' (' . $katMin . '–' . $katMax . ')';
                    }
                }
            }

            if (!empty($conflicts)) {
                $msg  = 'Rentang global baru <b>(' . $newMin . '–' . $newMax . ')</b> ';
                $msg .= 'tidak dapat menampung sub-rentang berikut:<br><br>';
                $msg .= '<div style="text-align:left;line-height:1.8;">';
                foreach ($conflicts as $c) {
                    $msg .= '• ' . $c . '<br>';
                }
                $msg .= '</div><br>';
                $msg .= 'Silakan perbesar rentang global, atau hapus / sesuaikan sub-rentang di atas terlebih dahulu.';

                return response()->json([
                    'success' => false,
                    'message' => $msg,
                ], 422);
            }
        }
        // ═══════════════════════════════════════════════
        // ★ AKHIR VALIDASI BARU
        // ═══════════════════════════════════════════════

        \DB::table('settings')->updateOrInsert(
            ['key' => 'tank_range_min'],
            ['value' => $request->min, 'updated_at' => now()]
        );

        \DB::table('settings')->updateOrInsert(
            ['key' => 'tank_range_max'],
            ['value' => $request->max, 'updated_at' => now()]
        );

        // ★ AUTO-SYNC PLOTING TANK
        try {
            if ($this->sheetsSync->isReady()) {
                $this->sheetsSync->syncPlotingTank();
            }
        } catch (\Exception $e) {
            \Log::warning('Sheets sync ploting gagal: ' . $e->getMessage());
        }

        return response()->json(['success' => true, 'message' => 'Rentang global berhasil diperbarui.']);
    }

    public function getMvpIkan()
    {
        $ikans = Ikan::where('is_mvp', true)->with('peserta')->orderBy('kategori')->orderBy('kelas')->get()->map(function($ikan) {
            return [
                'id' => $ikan->id,
                'nama_peserta' => $ikan->nama_peserta ?? '-',
                'detail_anggota' => $ikan->peserta->detail_anggota ?? '-',
                'kategori' => $ikan->kategori,
                'kelas' => $ikan->kelas,
                'nomor_tank' => $ikan->nomor_tank ?? '-',
            ];
        });
        return response()->json($ikans);
    }

    public function toggleMvpRegistration()
    {
        $current = \DB::table('settings')->where('key', 'mvp_registration_open')->value('value');
        $newVal = ($current === '1') ? '0' : '1';
        
        \DB::table('settings')->updateOrInsert(
            ['key' => 'mvp_registration_open'],
            ['value' => $newVal, 'updated_at' => now()]
        );

        return response()->json([
            'success' => true, 
            'is_open' => (bool)$newVal,
            'message' => $newVal === '1' ? 'Pendaftaran MVP DIBUKA untuk user.' : 'Pendaftaran MVP DITUTUP untuk user.'
        ]);
    }

    public function getMvpStatus()
    {
        $isOpen = (bool)(\DB::table('settings')->where('key', 'mvp_registration_open')->value('value') ?? false);
        return response()->json(['is_open' => $isOpen]);
    }

    public function toggleUndianRegistration()
    {
        $current = \DB::table('settings')->where('key', 'undian_registration_open')->value('value');
        $newVal = ($current === '1') ? '0' : '1';
        
        \DB::table('settings')->updateOrInsert(
            ['key' => 'undian_registration_open'],
            ['value' => $newVal, 'updated_at' => now()]
        );

        return response()->json([
            'success' => true, 
            'is_open' => (bool)$newVal,
            'message' => $newVal === '1' ? 'Mesin Undian DIBUKA untuk user.' : 'Mesin Undian DITUTUP untuk user.'
        ]);
    }

    public function getUndianStatus()
    {
        $isOpen = (bool)(\DB::table('settings')->where('key', 'undian_registration_open')->value('value') ?? true);
        return response()->json(['is_open' => $isOpen]);
    }

    public function deleteMvpIkan(Request $request)
    {
        $request->validate([
            'ikan_id' => 'required|exists:ikans,id',
        ]);

        $ikan = Ikan::find($request->ikan_id);

        // Cek apakah ikan benar-benar terdaftar MVP
        if (!$ikan->is_mvp) {
            return response()->json([
                'success' => false,
                'message' => 'Ikan ini tidak terdaftar sebagai MVP.'
            ], 422);
        }

        // Hanya hapus status MVP, data ikan & penilaian tetap aman
        $ikan->is_mvp = false;
        $ikan->save();

        return response()->json([
            'success' => true,
            'message' => 'Ikan berhasil dihapus dari pendaftaran MVP. Peserta dapat mendaftarkan ulang.'
        ]);
    }

        public function getMvpSubmittedPeserta()
    {
        $pesertas = \App\Models\Peserta::where('is_mvp_submitted', true)
            ->with(['user', 'ikans'])
            ->orderBy('nama_peserta')
            ->get()
            ->map(function ($p) {
                return [
                    'peserta_id'    => $p->id,
                    'nama_peserta'  => $p->nama_peserta,
                    'detail_anggota'=> $p->detail_anggota ?? '-',
                    'email'         => $p->user->email ?? '-',
                    'jumlah_mvp'    => $p->ikans->where('is_mvp', true)->count(),
                ];
            });

        return response()->json($pesertas);
    }

    public function unlockMvpPeserta(Request $request)
    {
        $request->validate([
            'peserta_id' => 'required|exists:pesertas,id',
        ]);

        $peserta = \App\Models\Peserta::find($request->peserta_id);

        if (!$peserta->is_mvp_submitted) {
            return response()->json([
                'success' => false,
                'message' => 'Peserta ini belum mengirim data MVP, tidak perlu dibuka kunci.',
            ], 422);
        }

        // Buka kunci = set is_mvp_submitted menjadi false
        // Ikan yang sudah ditandai is_mvp tetap aman, user bisa tambah/hapus lalu kirim ulang
        $peserta->is_mvp_submitted = false;
        $peserta->save();

        return response()->json([
            'success' => true,
            'message' => 'Peserta "' . $peserta->nama_peserta . '" dapat kembali mendaftarkan ikan MVP.',
        ]);
    }

    public function exportExcel(Request $request)
    {
        $sheets = $request->query('sheets', 'all');
        $valid  = ['all', 'daftar', 'mvp', 'ranking_kk', 'ranking_k', 'ranking_global', 'users'];

        if (!in_array($sheets, $valid)) {
            $sheets = 'all';
        }

        $label = match ($sheets) {
            'daftar'         => 'Daftar_Ikan',
            'mvp'            => 'Data_MVP',
            'ranking_kk'     => 'Ranking_Per_Kat_Kelas',
            'ranking_k'      => 'Ranking_Per_Kategori',
            'ranking_global' => 'Rank_Global',
            'users'          => 'Detail_Pengguna',
            default          => 'Semua_Data',
        };

        $fileName = 'LCI_Admin_' . $label . '_' . now()->format('Y-m-d_His') . '.xlsx';

        return Excel::download(new AdminExport($sheets), $fileName);
    }
}