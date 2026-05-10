<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Scoring;
use App\Models\Peserta;
use App\Models\User;

class AdminDashboardController extends Controller
{
    public function index()
    {
        return view('dashboard.admin', ['user' => auth()->user()->fresh()]);
    }

    /* ═══════════════════════════════════════════
       DASHBOARD STATS + CHART DATA
       ═══════════════════════════════════════════ */
    public function getDashboardStats()
    {
        $totalPeserta = Peserta::whereNotNull('nomor_tank')->count();

        /* Hitung per peserta: ambil scoring terbaru tiap peserta */
        $latestScores = Scoring::selectRaw('peserta_id, MAX(id) as latest_id')
            ->groupBy('peserta_id')
            ->pluck('latest_id');

        $scorings = Scoring::whereIn('id', $latestScores)->get();

        $sudahDinilai = $scorings->where('edited_by_grand_juri', false)->count();
        $grandEdited  = $scorings->where('edited_by_grand_juri', true)->count();
        $belumDinilai = max(0, $totalPeserta - $scorings->count());

        $juriAktif = Scoring::whereNotNull('juri_id')
            ->distinct('juri_id')
            ->count('juri_id');

        $avgScore = $scorings->count() > 0
            ? round($scorings->avg('total_nilai'))
            : 0;

        /* Per kategori */
        $perKategori = Peserta::whereNotNull('nomor_tank')
            ->selectRaw('kategori, COUNT(*) as total')
            ->groupBy('kategori')
            ->orderByDesc('total')
            ->pluck('total', 'kategori')
            ->toArray();

        /* Top 10 */
        $top10 = Scoring::with('peserta')
            ->whereIn('id', $latestScores)
            ->whereNotNull('total_nilai')
            ->orderByDesc('total_nilai')
            ->limit(10)
            ->get()
            ->map(function ($s) {
                return [
                    'nama'  => $s->peserta?->nama_peserta ?? 'Unknown',
                    'total' => $s->total_nilai,
                ];
            })
            ->toArray();

        return response()->json([
            'total_peserta'  => $totalPeserta,
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
        $query = Peserta::whereNotNull('nomor_tank')
            ->with(['scorings' => function ($q) {
                $q->latest()->limit(1);
            }, 'scorings.juri', 'scorings.grandJuri']);

        if ($request->filled('search')) {
            $query->where('nama_peserta', 'LIKE', '%' . $request->search . '%');
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        $data = $query->orderBy('nomor_tank')->get()->map(function ($p) {
            $scoring = $p->scorings->first();

        return [
            'id'              => $p->id,
            'nama_peserta'    => $p->nama_peserta,
            'kategori'        => $p->kategori,
            'kelas'           => $scoring?->kelas ?? '—',
            'nomor_tank'      => $p->nomor_tank,
            'detail_anggota'  => $p->detail_anggota ?? '—',
            'juri_nama'       => $scoring?->juri?->name ?? '—',
            'grand_juri_nama' => $scoring?->grandJuri?->name ?? null,
            'total_nilai'     => $scoring?->total_nilai ?? 0,
            'nilai_detail'    => $scoring?->nilai_detail ?? null,
            'status'          => $scoring
            ? ($scoring->edited_by_grand_juri ? 'Grand Juri Edit' : 'Sudah Dinilai')
            : 'Belum Dinilai',
        ];
        })->toArray();

        /* Filter status di PHP (karena status dihitung dari relasi) */
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
       CREATE USER
       ═══════════════════════════════════════════ */
    public function createUser(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role'     => 'required|in:admin,juri,grand_juri,user',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => bcrypt($request->password),
            'role'     => $request->role,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User "' . $user->name . '" berhasil ditambahkan sebagai ' . strtoupper(str_replace('_', ' ', $user->role)) . '.',
        ]);
    }

    /* ═══════════════════════════════════════════
       CHANGE ROLE (INLINE)
       ═══════════════════════════════════════════ */
    public function changeRole(Request $request)
    {
        $request->validate([
            'user_id'  => 'required|exists:users,id',
            'new_role' => 'required|in:admin,juri,grand_juri,user',
        ]);

        $user = User::find($request->user_id);

        if ($user->id === auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Tidak bisa mengubah role sendiri.'], 403);
        }

        $oldRole = $user->role;
        $user->update(['role' => $request->new_role]);

        return response()->json([
            'success' => true,
            'message' => 'Role "' . $user->name . '" diubah dari ' . strtoupper($oldRole) . ' menjadi ' . strtoupper(str_replace('_', ' ', $request->new_role)) . '.',
        ]);
    }
    public function deleteUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::find($request->user_id);

        if ($user->id === auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Tidak bisa menghapus akun sendiri.'], 403);
        }

        $name = $user->name;
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User "' . $name . '" berhasil dihapus.',
        ]);
    }
}