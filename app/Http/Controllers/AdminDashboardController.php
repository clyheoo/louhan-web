<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Scoring;
use App\Models\Peserta;
use App\Models\Ikan;
use App\Models\User;

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
            'nama_peserta'      => 'required|string|max:255',
            'kategori'          => 'required|string|max:255',
            'kelas'             => 'required|string|max:10',
        ]);

        // Gunakan firstOrCreate agar tidak error jika peserta sudah terdaftar
        $peserta = Peserta::firstOrCreate(
            ['nama_peserta' => $request->nama_peserta],
            [
                'user_id'           => null,
                'jenis_keanggotaan' => 'perorangan',
                'detail_anggota'    => '-',
            ]
        );

        Ikan::create([
            'peserta_id' => $peserta->id,
            'kategori'   => $request->kategori,
            'kelas'      => $request->kelas,
            'dibuat_oleh' => 'admin',
        ]);

        // ★ FIX: Hapus kode $sisaTank yang menggunakan $totalIkan undefined,
        // karena tidak di-return ke response juga.

        return response()->json([
            'success' => true,
            'message' => 'Peserta dan ikan berhasil didaftarkan.',
        ]);
    }

    // Update getDashboardStats untuk menghitung max tank per kelas
    public function getDashboardStats()
    {
        $totalIkan = Ikan::whereNotNull('nomor_tank')->count();

        $latestScores = Scoring::selectRaw('ikan_id, MAX(id) as latest_id')
            ->groupBy('ikan_id')
            ->pluck('latest_id');

        $scorings = Scoring::whereIn('id', $latestScores)->get();

        $sudahDinilai = $scorings->where('edited_by_grand_juri', false)->count();
        $grandEdited  = $scorings->where('edited_by_grand_juri', true)->count();
        $belumDinilai = max(0, $totalIkan - $scorings->count());

        $juriAktif = Scoring::whereNotNull('juri_id')->distinct('juri_id')->count('juri_id');
        $avgScore = $scorings->count() > 0 ? round($scorings->avg('total_nilai')) : 0;

        $perKategori = Ikan::whereNotNull('nomor_tank')
            ->selectRaw('kategori, COUNT(*) as total')
            ->groupBy('kategori')
            ->orderByDesc('total')
            ->pluck('total', 'kategori')
            ->toArray();

        $top10 = Scoring::with('ikan.peserta')
            ->whereIn('id', $latestScores)
            ->whereNotNull('total_nilai')
            ->get()
            ->groupBy(fn($s) => $s->ikan?->peserta_id)
            ->map(function ($scores) {
                $sorted = $scores->sortByDesc('total_nilai');
                $best = $sorted->first();
                $ikan = $best?->ikan;
                $peserta = $ikan?->peserta;
                return [
                    'nama'       => $peserta?->nama_peserta ?? 'Unknown',
                    'total'      => $best?->total_nilai ?? 0,
                    'kategori'   => $ikan?->kategori ?? '—',
                    'kelas'      => $best?->kelas ?? ($ikan?->kelas ?? '—'),
                    'nomor_tank' => $ikan?->nomor_tank ?? '—',
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
        /* DIPERBAIKI: Tampilkan ikan JIKA sudah dapat nomor tank ATAU sudah pernah dinilai */
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

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        $data = $query->orderBy('nomor_tank')->get()->map(function ($ikan) {
            $peserta = $ikan->peserta;
            // DIPERBAIKI: Ambil scoring langsung dari $ikan->scorings
            $scoring = $ikan->scorings->first();

            return [
                'id'              => $ikan->id,
                'peserta_id'      => $ikan->peserta_id, 
                'nama_peserta'    => $peserta->nama_peserta ?? 'Unknown',
                'kategori'        => $ikan->kategori,   
                'kelas'           => $scoring ? $scoring->kelas : $ikan->kelas, // Ambil kelas dari penilaian juri jika ada, jika tidak dari data ikan
                'nomor_tank'      => $ikan->nomor_tank, 
                'detail_anggota'  => $peserta->detail_anggota ?? '—',
                'juri_nama'       => $scoring?->juri?->name ?? '—',
                'grand_juri_nama' => $scoring?->grandJuri?->name ?? null,
                'total_nilai'     => $scoring?->total_nilai ?? 0,
                'nilai_detail'    => $scoring?->nilai_detail ?? null,
                'status'          => $scoring ? ($scoring->edited_by_grand_juri ? 'Grand Juri Edit' : 'Sudah Dinilai') : 'Belum Dinilai',
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
}