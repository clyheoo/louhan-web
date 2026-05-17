<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Scoring;
use App\Models\Peserta;
use App\Models\Ikan;
use App\Models\User;
use App\Helpers\PointCalculator;
use App\Models\ScoringPointConfig;

class AdminDashboardController extends Controller
{
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

        Ikan::create([
            'peserta_id' => $request->peserta_id,
            'kategori'   => $request->kategori,
            'kelas'      => $request->kelas,
        ]);

        return response()->json(['success' => true, 'message' => 'Ikan berhasil didaftarkan ke peserta.']);
    }

    public function registerPesertaIkan(Request $request)
    {
        $request->validate([
            'user_id'           => 'required|exists:users,id',
            'nama_peserta'      => 'required|string|max:255',
            'kategori'          => 'required|string|max:255',
            'kelas'             => 'required|string|max:10',
            'jenis_keanggotaan' => 'required|in:perorangan,team',
            'detail_anggota'    => 'required|string|max:255',
        ]);

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

        Ikan::create([
            'peserta_id'   => $peserta->id,
            'kategori'     => $request->kategori,
            'kelas'        => $request->kelas,
            'dibuat_oleh'  => 'admin',
        ]);

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
            ->with('peserta')
            ->get()
            ->map(function ($ikan) use ($ikanTotalMap) {
                return [
                    'nama'       => $ikan->peserta?->nama_peserta ?? 'Unknown',
                    'total'      => $ikanTotalMap[$ikan->id],
                    'kategori'   => $ikan->kategori ?? '—',
                    'kelas'      => $ikan->kelas ?? '—',
                    'nomor_tank' => $ikan->nomor_tank ?? '—',
                ];
            })
            ->sortByDesc('total')
            ->take(10)
            ->values()
            ->toArray();

        // SELALU gunakan global range untuk card sisa tank
        $minGlobal = (int) (\DB::table('settings')->where('key', 'tank_range_min')->value('value') ?? 1);
        $maxGlobal = (int) (\DB::table('settings')->where('key', 'tank_range_max')->value('value') ?? 1000);
        $maxTankTotal = $maxGlobal - $minGlobal + 1;

        $sisaTank = max(0, $maxTankTotal - $totalIkan);

        return response()->json([
            'total_peserta'  => $totalIkan,
            'sudah_dinilai'  => $sudahDinilai,
            'grand_edited'   => $grandEdited,
            'belum_dinilai'  => $belumDinilai,
            'juri_aktif'     => $juriAktif,
            'rata_rata'      => $avgScore,
            'sisa_tank'      => $sisaTank,
            'max_tank'       => $maxTankTotal,
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
            $grandJuriName = null;
            $latestNilai = null;
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
                }
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

            /* ★ Data untuk detail modal (accordion per juri) */
            $allScoringsData = $scorings->map(function ($s) {
                return [
                    'juri_name'    => $s->juri ? $s->juri->name : '—',
                    'is_grand'     => ($s->juri && $s->juri->role === 'grand_juri'),
                    'nilai_detail' => $s->nilai_detail,
                    'total_nilai'  => $s->total_nilai ?? 0,
                ];
            })->values()->toArray();

            $detailListPerJuri = $scorings->map(function ($s) {
                return [
                    'juri_name'   => $s->juri ? $s->juri->name : '—',
                    'is_grand'    => ($s->juri && $s->juri->role === 'grand_juri'),
                    'total_nilai' => $s->total_nilai ?? 0,
                ];
            })->values()->toArray();

            return [
                'id'                 => $ikan->id,
                'peserta_id'         => $ikan->peserta_id,
                'nama_peserta'       => $peserta->nama_peserta ?? 'Unknown',
                'kategori'           => $ikan->kategori,
                'kelas'              => $latestKelas ?? $ikan->kelas,
                'nomor_tank'         => $ikan->nomor_tank,
                'detail_anggota'     => $peserta->detail_anggota ?? '—',
                'juri_list'          => $juriList,
                'grand_juri_nama'    => $grandJuriName,
                'total_nilai'        => $latestScoring?->total_nilai ?? 0,
                'total_nilai_semua'  => $totalNilaiSemua,
                'jumlah_juri'        => $jumlahJuriYangNilai,
                'nilai_detail'       => $latestNilai,
                'status'             => $latestScoring ? ($latestScoring->edited_by_grand_juri ? 'Grand Juri Edit' : 'Sudah Dinilai') : 'Belum Dinilai',
                'total_point'        => (float) $totalPoint,
                'point_config'       => $pointConfig ? [
                    'overall' => (float)$pointConfig->overall_bobot,
                    'head'    => (float)$pointConfig->head_bobot,
                    'face'    => (float)$pointConfig->face_bobot,
                    'body'    => (float)$pointConfig->body_bobot,
                    'marking' => (float)$pointConfig->marking_bobot,
                    'pearl'   => (float)$pointConfig->pearl_bobot,
                    'color'   => (float)$pointConfig->color_bobot,
                    'finnage' => (float)$pointConfig->finnage_bobot,
                    'bonus_list'    => $ikan->bonusPoints->pluck('bonus_type')->toArray(),
                    'total_bonus'   => (int) $ikan->bonusPoints->sum('points'),
                    'final_point'  => (float) $totalPoint + (int) $ikan->bonusPoints->sum('points'),
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

        return response()->json(['success' => true, 'message' => 'User "' . $name . '" berhasil dihapus.']);
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
       PENGATURAN RANGE NOMOR UNDIAN
       ═══════════════════════════════════════════ */
    public function getTankRange()
    {
        $ranges = json_decode(\DB::table('settings')->where('key', 'tank_class_ranges')->value('value'), true);
        
        // Default values jika belum diatur
        if (!$ranges) {
            $ranges = [
                'A' => ['min' => 1, 'max' => 20],
                'B' => ['min' => 21, 'max' => 40],
                'C' => ['min' => 41, 'max' => 60],
                'D' => ['min' => 61, 'max' => 80],
                'E' => ['min' => 81, 'max' => 100],
            ];
        }
        
        return response()->json($ranges);
    }

    public function setTankRange(Request $request)
    {
        // Decode JSON string dari frontend
        $ranges = json_decode($request->ranges, true);

        if (!$ranges || !is_array($ranges)) {
            return response()->json([
                'success' => false, 
                'message' => 'Format data tidak valid.'
            ], 422);
        }

        // Validasi manual setiap kelas
        foreach ($ranges as $kelas => $data) {
            $min = isset($data['min']) ? (int)$data['min'] : null;
            $max = isset($data['max']) ? (int)$data['max'] : null;

            if ($min === null || $max === null || $min < 1 || $max < 1) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Rentang Kelas ' . $kelas . ' tidak valid.'
                ], 422);
            }

            if ($max < $min) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Rentang Kelas ' . $kelas . ': Max harus >= Min.'
                ], 422);
            }
        }

        \DB::table('settings')->updateOrInsert(
            ['key' => 'tank_class_ranges'],
            ['value' => json_encode($ranges), 'updated_at' => now()]
        );

        return response()->json(['success' => true, 'message' => 'Rentang nomor undian per kelas berhasil diperbarui.']);
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

        \DB::table('settings')->updateOrInsert(
            ['key' => 'tank_range_min'],
            ['value' => $request->min, 'updated_at' => now()]
        );

        \DB::table('settings')->updateOrInsert(
            ['key' => 'tank_range_max'],
            ['value' => $request->max, 'updated_at' => now()]
        );

        return response()->json(['success' => true, 'message' => 'Rentang global berhasil diperbarui.']);
    }

    public function getMvpIkan()
    {
        $ikans = Ikan::where('is_mvp', true)->with('peserta')->orderBy('kategori')->orderBy('kelas')->get()->map(function($ikan) {
            return [
                'id' => $ikan->id,
                'nama_peserta' => $ikan->peserta->nama_peserta ?? '-',
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
}