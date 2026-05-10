<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Peserta;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user()->fresh();
        Auth::setUser($user);

        if ($user->role === 'admin') {
            return view('dashboard.admin', ['user' => $user]);
        } elseif ($user->role === 'grand_juri') {
            return view('dashboard.grand-juri', ['user' => $user]);
        } elseif ($user->role === 'juri') {
            return view('dashboard.juri', ['user' => $user]);
        }

        $pesertaSaya = Peserta::where('user_id', $user->id)->whereNotNull('nomor_tank')->first();
        return view('dashboard.user', ['user' => $user, 'pesertaSaya' => $pesertaSaya]);
    }

    public function storePeserta(Request $request)
    {
        $request->validate([
            'nama_peserta'      => 'required|string|max:255',
            'kategori'          => 'required|string|max:255',
            'kelas'             => 'required|string|max:10',
            'jenis_keanggotaan' => 'required|in:perorangan,team',
            'detail_anggota'    => 'required|string|max:255',
        ]);

        /* Cegah duplikat: 1 user hanya boleh 1 peserta */
        $exists = Peserta::where('user_id', Auth::id())->exists();
        if ($exists) {
            return response()->json([
                'success' => false,
                'errors'  => ['nama_peserta' => ['Anda sudah terdaftar sebagai peserta.']]
            ], 422);
        }

        Peserta::create([
            'user_id'           => Auth::id(),
            'nama_peserta'      => $request->nama_peserta,
            'kategori'          => $request->kategori,
            'kelas'             => $request->kelas,
            'jenis_keanggotaan' => $request->jenis_keanggotaan,
            'detail_anggota'    => $request->detail_anggota,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mendaftar!'
        ]);
    }

    public function getPesertaBelumDapatTank()
    {
        $pesertas = Peserta::whereNull('nomor_tank')->orderBy('nama_peserta')->get();
        return response()->json($pesertas);
    }

    // UNDIAN ADMIN (Bisa atur range & pilih peserta)
    public function acakNomorTankAdmin(Request $request)
    {
        $request->validate([
            'peserta_id' => 'required|exists:pesertas,id',
            'range_min'  => 'required|integer|min:1',
            'range_max'  => 'required|integer|min:1',
        ]);

        if ($request->range_max < $request->range_min) {
            return response()->json(['success' => false, 'message' => 'Range maksimal harus lebih besar dari minimal.'], 400);
        }

        $peserta = Peserta::find($request->peserta_id);

        try {
            DB::transaction(function () use ($peserta, $request) {
                $assignedNumbers = Peserta::whereNotNull('nomor_tank')->lockForUpdate()->pluck('nomor_tank')->toArray();
                $allNumbers = range($request->range_min, $request->range_max);
                $availableNumbers = array_diff($allNumbers, $assignedNumbers);

                if (empty($availableNumbers)) throw new \Exception('Semua nomor dalam range tersebut sudah terisi habis!');

                $randomNumber = array_values($availableNumbers)[array_rand($availableNumbers)];
                $peserta->nomor_tank = $randomNumber;
                $peserta->save();
            });

            return response()->json([
                'success'     => true, 
                'nomor_tank'  => $peserta->fresh()->nomor_tank,
                'nama_peserta'=> $peserta->nama_peserta
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    // --- FUNGSI USER ---
    // UNDIAN USER (Otomatis untuk dirinya sendiri, tanpa input range)
    public function acakNomorTankUser()
    {
        // Cari peserta yang dimiliki user ini dan belum dapat nomor
        $peserta = Peserta::where('user_id', Auth::id())->whereNull('nomor_tank')->first();

        if (!$peserta) {
            return response()->json(['success' => false, 'message' => 'Anda belum terdaftar sebagai peserta atau sudah mendapat nomor undian.'], 400);
        }

        try {
            DB::transaction(function () use ($peserta) {
                // Range default untuk user adalah 1 - 100 (Bisa diubah sesuai kebutuhan)
                $min = 1; 
                $max = 100; 

                $assignedNumbers = Peserta::whereNotNull('nomor_tank')->lockForUpdate()->pluck('nomor_tank')->toArray();
                $allNumbers = range($min, $max);
                $availableNumbers = array_diff($allNumbers, $assignedNumbers);

                if (empty($availableNumbers)) throw new \Exception('Maaf, seluruh nomor tank sudah habis terisi.');

                $randomNumber = array_values($availableNumbers)[array_rand($availableNumbers)];
                $peserta->nomor_tank = $randomNumber;
                $peserta->save();
            });

            return response()->json([
                'success' => true, 
                'nomor_tank' => $peserta->fresh()->nomor_tank
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function getListUsers()
    {
        $users = User::select('id', 'name', 'email', 'role')
            ->orderBy('name')
            ->get()
            ->map(function ($u) {
                return [
                    'id'    => $u->id,
                    'name'  => $u->name,
                    'email' => $u->email,
                    'role'  => $u->role ?? ($u->is_admin ? 'admin' : 'user'),
                ];
            });

        return response()->json($users);
    }

    // PROSES GANTI PASSWORD OLEH ADMIN
    public function updatePasswordUser(Request $request)
    {
        $request->validate([
            'user_id'   => 'required|exists:users,id',
            'new_password' => 'required|min:8',
        ]);

        $user = \App\Models\User::find($request->user_id);
        $user->password = \Illuminate\Support\Facades\Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'success' => true, 
            'message' => "Password untuk user {$user->name} berhasil diubah!"
        ]);
    }
        // UBAH ROLE USER (ADMIN <-> USER BIASA)
    public function toggleRoleUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = \App\Models\User::find($request->user_id);
        
        // Cegah jika admin mencoba menurunkan dirinya sendiri (Opsional, tapi aman)
        if ($user->id === auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Anda tidak bisa mengubah role diri sendiri!'], 403);
        }

        // Toggle status: kalau sekarang true jadi false, kalau false jadi true
        $user->is_admin = !$user->is_admin;
        $user->save();

        $statusText = $user->is_admin ? 'Admin' : 'User Biasa';

        return response()->json([
            'success' => true, 
            'message' => "Role {$user->name} berhasil diubah menjadi {$statusText}.",
            'new_role' => $user->is_admin
        ]);
    }
}