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
            'jenis_keanggotaan' => 'required|in:perorangan,team',
            'detail_anggota'    => 'required|string|max:255',
            'kategori'          => 'required|string|max:255',
            'kelas'             => 'required|string|max:10',
        ]);

        $peserta = Peserta::create([
            'user_id'           => null,
            'nama_peserta'      => $request->nama_peserta,
            'jenis_keanggotaan' => $request->jenis_keanggotaan,
            'detail_anggota'    => $request->detail_anggota,
        ]);

        Ikan::create([
            'peserta_id' => $peserta->id,
            'kategori'   => $request->kategori,
            'kelas'      => $request->kelas,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Peserta dan ikan berhasil didaftarkan.',
        ]);
    }

    /* ═══════════════════════════════════════════
       DASHBOARD STATS + CHART DATA
       ═══════════════════════════════════════════ */
    public function getDashboardStats()
    {
        $totalIkan = Ikan::whereNotNull('nomor_tank')->count();

        /* DIPERBAIKI: Menggunakan ikan_id (BUKAN peserta_id) */
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

        /* DIPERBAIKI: Relasi Scoring -> Ikan -> Peserta */
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

        return response()->json([
            'total_peserta'  => $totalIkan, 
            'sudah_dinilai'  => $sudahDinilai,
            'grand_edited'   => $grandEdited,
            'belum_dinilai'  => $belumDinilai,
            'juri_aktif'     => $juriAktif,
            'rata_rata'      => $avgScore,
            'per_kategori'   => $perKategori,
            'top_10'         => $top10,
        ]);
    }

    /* ═══════════════════════════════════════════
       SCORING DATA (TABLE WITH FILTERS)
       ═══════════════════════════════════════════ */
    public function getScoringData(Request $request)
    {
        /* DIPERBAIKI: Query langsung relasi milik IKAN (BUKAN peserta.scorings) */
        $query = Ikan::whereNotNull('nomor_tank')
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

        /* BYPASS ELOQUENT — langsung insert ke database */
        \DB::table('users')->insert([
            'name'          => $request->name,
            'email'         => $request->email,
            'password'      => bcrypt($request->password),
            'plain_password'=> $request->password,
            'role'          => $request->role,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'User "' . $request->name . '" berhasil ditambahkan.']);
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
}