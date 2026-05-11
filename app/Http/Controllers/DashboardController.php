<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Peserta;
use App\Models\Ikan;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user()->fresh();
        Auth::setUser($user);

        if ($user->role === 'admin') return view('dashboard.admin', ['user' => $user]);
        if ($user->role === 'grand_juri') return view('dashboard.grand-juri', ['user' => $user]);
        if ($user->role === 'juri') return view('dashboard.juri', ['user' => $user]);

        $pesertaSaya = Peserta::where('user_id', $user->id)->first();
        // Ambil semua ikan milik peserta ini
        $ikansSaya = $pesertaSaya ? $pesertaSaya->ikans()->orderBy('created_at', 'desc')->get() : collect();

        return view('dashboard.user', [
            'user' => $user, 
            'pesertaSaya' => $pesertaSaya,
            'ikansSaya' => $ikansSaya
        ]);
    }

    // SIMPAN PROFIL PESERTA (Hanya Nama, Jenis, Kota/Team)
    public function storePeserta(Request $request)
    {
        $request->validate([
            'nama_peserta'      => 'required|string|max:255',
            'jenis_keanggotaan' => 'required|in:perorangan,team',
            'detail_anggota'    => 'required|string|max:255',
        ]);

        // Update atau Buat profil baru
        Peserta::updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'nama_peserta'      => $request->nama_peserta,
                'jenis_keanggotaan' => $request->jenis_keanggotaan,
                'detail_anggota'    => $request->detail_anggota,
            ]
        );

        return response()->json(['success' => true, 'message' => 'Profil berhasil disimpan!']);
    }

    // SIMPAN DATA IKAN (Dipanggil dari Popup)
    public function storeIkan(Request $request)
    {
        $request->validate([
            'kategori' => 'required|string|max:255',
            'kelas'    => 'required|string|max:10',
        ]);

        $peserta = Peserta::where('user_id', Auth::id())->first();
        if (!$peserta) {
            return response()->json(['success' => false, 'message' => 'Silakan isi profil terlebih dahulu.'], 400);
        }

        $ikan = Ikan::create([
            'peserta_id' => $peserta->id,
            'kategori'   => $request->kategori,
            'kelas'      => $request->kelas,
            'dibuat_oleh' => 'user',
        ]);

        return response()->json([
            'success' => true, 
            'message' => 'Ikan berhasil ditambahkan!',
            'ikan'    => $ikan
        ]);
    }

    // UNDIAN USER (Sekarang berdasarkan ID Ikan)
    public function acakNomorTankUser(Request $request)
    {
        $request->validate(['ikan_id' => 'required|exists:ikans,id']);

        // Cari ikan yang belum dapat nomor DAN milik user yang login
        $ikan = Ikan::where('id', $request->ikan_id)
            ->whereHas('peserta', fn($q) => $q->where('user_id', Auth::id()))
            ->whereNull('nomor_tank')
            ->first();

        if (!$ikan) {
            return response()->json(['success' => false, 'message' => 'Ikan tidak ditemukan, bukan milik Anda, atau sudah mendapat nomor.'], 400);
        }

        try {
            DB::transaction(function () use ($ikan) {
                $min = 1; $max = 100; 
                // Cek nomor yang sudah dipakai di seluruh tabel IKANS
                $assignedNumbers = Ikan::whereNotNull('nomor_tank')->lockForUpdate()->pluck('nomor_tank')->toArray();
                $allNumbers = range($min, $max);
                $availableNumbers = array_diff($allNumbers, $assignedNumbers);

                if (empty($availableNumbers)) throw new \Exception('Maaf, seluruh nomor tank sudah habis terisi.');

                $randomNumber = array_values($availableNumbers)[array_rand($availableNumbers)];
                $ikan->nomor_tank = $randomNumber;
                $ikan->save();
            });

            return response()->json([
                'success' => true, 
                'nomor_tank' => $ikan->fresh()->nomor_tank,
                'ikan_id' => $ikan->id
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    // --- FUNGSI ADMIN (SUDAH DISESUAIKAN DENGAN TABEL IKANS) ---
    public function getPesertaBelumDapatTank()
    {
        $ikans = Ikan::with('peserta')->whereNull('nomor_tank')->orderBy('created_at', 'desc')->get()
            ->map(function($ikan) {
                return [
                    'id' => $ikan->id,
                    'nama_peserta' => $ikan->peserta->nama_peserta,
                    'kategori' => $ikan->kategori,
                    'kelas' => $ikan->kelas,
                ];
            });
        return response()->json($ikans);
    }

    public function acakNomorTankAdmin(Request $request)
    {
        $request->validate([
            'ikan_id'   => 'required|exists:ikans,id', // Diubah dari peserta_id
            'range_min' => 'required|integer|min:1',
            'range_max' => 'required|integer|min:1',
        ]);

        if ($request->range_max < $request->range_min) {
            return response()->json(['success' => false, 'message' => 'Range maksimal harus lebih besar dari minimal.'], 400);
        }

        $ikan = Ikan::find($request->ikan_id);

        try {
            DB::transaction(function () use ($ikan, $request) {
                $assignedNumbers = Ikan::whereNotNull('nomor_tank')->lockForUpdate()->pluck('nomor_tank')->toArray();
                $allNumbers = range($request->range_min, $request->range_max);
                $availableNumbers = array_diff($allNumbers, $assignedNumbers);

                if (empty($availableNumbers)) throw new \Exception('Semua nomor dalam range tersebut sudah terisi habis!');

                $randomNumber = array_values($availableNumbers)[array_rand($availableNumbers)];
                $ikan->nomor_tank = $randomNumber;
                $ikan->save();
            });

            return response()->json([
                'success'     => true, 
                'nomor_tank'  => $ikan->fresh()->nomor_tank,
                'nama_peserta'=> $ikan->peserta->nama_peserta
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function getListUsers()
    {
        $users = User::select('id', 'name', 'email', 'role', 'plain_password')->orderBy('name')->get()->map(function ($u) {
            return ['id' => $u->id, 'name' => $u->name, 'email' => $u->email, 'role' => $u->role ?? 'user', 'plain_password' => $u->plain_password];
        });
        return response()->json($users);
    }

    public function updatePasswordUser(Request $request)
    {
        $request->validate(['user_id' => 'required|exists:users,id', 'new_password' => 'required|min:8']);
        $user = User::find($request->user_id);
        $user->password = \Illuminate\Support\Facades\Hash::make($request->new_password);
        $user->plain_password = $request->new_password;
        $user->save();
        return response()->json(['success' => true, 'message' => "Password {$user->name} berhasil diubah!"]);
    }

    public function toggleRoleUser(Request $request)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);
        $user = User::find($request->user_id);
        if ($user->id === auth()->id()) return response()->json(['success' => false, 'message' => 'Tidak bisa ubah role sendiri!'], 403);
        $user->is_admin = !$user->is_admin;
        $user->save();
        return response()->json(['success' => true, 'message' => "Role {$user->name} diubah menjadi " . ($user->is_admin ? 'Admin' : 'User Biasa') . ".", 'new_role' => $user->is_admin]);
    }

    public function getMyIkans()
    {
        $peserta = Peserta::where('user_id', Auth::id())->first();
        if (!$peserta) return response()->json([]);

        $ikans = $peserta->ikans()->orderBy('created_at', 'desc')->get();
        return response()->json($ikans);
    }
}