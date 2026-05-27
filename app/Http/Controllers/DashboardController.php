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

    public function acakNomorTankAdmin(Request $request)
    {
        $request->validate(['ikan_id' => 'required|exists:ikans,id']);
        $ikan = Ikan::find($request->ikan_id);

        if ($ikan->nomor_tank !== null) {
            return response()->json([
                'success' => false,
                'message' => 'Ikan milik ' . $ikan->peserta->nama_peserta . ' sudah memiliki nomor tank (Tank ' . $ikan->nomor_tank . '). Admin tidak dapat mengundi ulang.',
            ], 422);
        }

        $kategori = $ikan->kategori;
        $kelas = $ikan->kelas;

        // Ambil rentang global
        $globalMin = (int) (\DB::table('settings')->where('key', 'tank_range_min')->value('value') ?? 1);
        $globalMax = (int) (\DB::table('settings')->where('key', 'tank_range_max')->value('value') ?? 1000);

        // Cari sub-rentang untuk kelas+kategori ini
        $classRanges = json_decode(\DB::table('settings')->where('key', 'tank_class_ranges')->value('value'), true);

        $myMin = $globalMin;
        $myMax = $globalMax;
        $hasSubRange = false;

        if ($classRanges && isset($classRanges[$kelas]['kategori'][$kategori])) {
            $myMin = (int) $classRanges[$kelas]['kategori'][$kategori]['min'];
            $myMax = (int) $classRanges[$kelas]['kategori'][$kategori]['max'];
            $hasSubRange = true;
        }

        if ($myMin > $myMax) {
            return response()->json(['success' => false, 'message' => 'Rentang nomor tank tidak valid.'], 400);
        }

        try {
            DB::transaction(function () use ($ikan, $myMin, $myMax, $hasSubRange, $kategori, $kelas, $classRanges) {
                // 1. Nomor yang sudah dipakai di database (siapapun)
                $usedNumbers = Ikan::whereNotNull('nomor_tank')
                    ->lockForUpdate()
                    ->pluck('nomor_tank')
                    ->map(fn($n) => (string) $n)
                    ->toArray();

                // 2. Nomor yang dialokasikan ke sub-rentang KELAS LAIN yang overlap dengan rentang kita
                $excludedByOtherRanges = [];
                if ($classRanges && $hasSubRange) {
                    foreach ($classRanges as $otherKelas => $otherData) {
                        if (!isset($otherData['kategori']) || !is_array($otherData['kategori'])) continue;

                        foreach ($otherData['kategori'] as $otherKat => $otherRange) {
                            // Skip diri sendiri
                            if ($otherKelas === $kelas) continue;

                            // ★ Hanya cek overlap untuk KATEGORI YANG SAMA di kelas lain
                            // Kategori berbeda boleh overlap — nomor tetap unik karena dicek via DB
                            if ($otherKat !== $kategori) continue;

                            $otherMin = (int) ($otherRange['min'] ?? 0);
                            $otherMax = (int) ($otherRange['max'] ?? 0);

                            // Cek apakah rentang lain overlap dengan rentang kita
                            if ($otherMin <= $myMax && $otherMax >= $myMin) {
                                // Cek apakah rentang lain BUKAN subset dari rentang kita
                                // Jika rentang lain adalah subset (sepenuhnya di dalam), MAKA kita yang mengalah
                                $otherIsSubset = ($otherMin >= $myMin && $otherMax <= $myMax);

                                if (!$otherIsSubset) {
                                    // Rentang lain lebih besar atau partially overlap di luar → exclude overlap
                                    $overlapStart = max($myMin, $otherMin);
                                    $overlapEnd = min($myMax, $otherMax);
                                    for ($n = $overlapStart; $n <= $overlapEnd; $n++) {
                                        $excludedByOtherRanges[] = (string) $n;
                                    }
                                }
                                // Jika otherIsSubset = true → jangan exclude, biarkan yang lebih kecil "miliki" nomor itu
                            }
                        }
                    }
                }

                // 3. Hitung available numbers
                $availableNumbers = [];
                for ($i = $myMin; $i <= $myMax; $i++) {
                    $numStr = (string) $i;
                    if (!in_array($numStr, $usedNumbers, false) && !in_array($numStr, $excludedByOtherRanges, false)) {
                        $availableNumbers[] = $i;
                    }
                }

                $label = $hasSubRange
                    ? "Kategori {$kategori} (Kelas {$kelas})"
                    : "Rentang Global ({$myMin}-{$myMax})";

                if (empty($availableNumbers)) {
                    $detail = '';
                    $usedInMyRange = 0;
                    for ($i = $myMin; $i <= $myMax; $i++) {
                        if (in_array((string)$i, $usedNumbers, false)) $usedInMyRange++;
                    }
                    if ($usedInMyRange > 0) {
                        $detail = ' (' . $usedInMyRange . ' nomor sudah dipakai)';
                    }
                    throw new \Exception('NOMOR TANK PENUH untuk ' . $label . ' (Rentang ' . $myMin . '-' . $myMax . ').' . $detail);
                }

                shuffle($availableNumbers);
                $ikan->nomor_tank = $availableNumbers[0];
                $ikan->save();
            });

            return response()->json([
                'success'      => true,
                'nomor_tank'   => $ikan->fresh()->nomor_tank,
                'nama_peserta' => $ikan->peserta->nama_peserta
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
    
    public function acakNomorTankUser(Request $request)
    {
        $request->validate(['ikan_id' => 'required|exists:ikans,id']);

        $ikan = Ikan::where('id', $request->ikan_id)
            ->whereHas('peserta', fn($q) => $q->where('user_id', Auth::id()))
            ->whereNull('nomor_tank')
            ->first();

        if (!$ikan) {
            return response()->json(['success' => false, 'message' => 'Ikan tidak ditemukan, bukan milik Anda, atau sudah mendapat nomor.'], 400);
        }

        $kategori = $ikan->kategori;
        $kelas = $ikan->kelas;

        // Ambil rentang global
        $globalMin = (int) (\DB::table('settings')->where('key', 'tank_range_min')->value('value') ?? 1);
        $globalMax = (int) (\DB::table('settings')->where('key', 'tank_range_max')->value('value') ?? 1000);

        // Cari sub-rentang untuk kelas+kategori ini
        $classRanges = json_decode(\DB::table('settings')->where('key', 'tank_class_ranges')->value('value'), true);

        $myMin = $globalMin;
        $myMax = $globalMax;
        $hasSubRange = false;

        if ($classRanges && isset($classRanges[$kelas]['kategori'][$kategori])) {
            $myMin = (int) $classRanges[$kelas]['kategori'][$kategori]['min'];
            $myMax = (int) $classRanges[$kelas]['kategori'][$kategori]['max'];
            $hasSubRange = true;
        }

        if ($myMin > $myMax) {
            return response()->json(['success' => false, 'message' => 'Rentang nomor tank tidak valid.'], 400);
        }

        try {
            DB::transaction(function () use ($ikan, $myMin, $myMax, $hasSubRange, $kategori, $kelas, $classRanges) {
                // 1. Nomor yang sudah dipakai di database
                $usedNumbers = Ikan::whereNotNull('nomor_tank')
                    ->lockForUpdate()
                    ->pluck('nomor_tank')
                    ->map(fn($n) => (string) $n)
                    ->toArray();

                // 2. Nomor yang dialokasikan ke sub-rentang KELAS LAIN yang overlap
                $excludedByOtherRanges = [];
                if ($classRanges && $hasSubRange) {
                    foreach ($classRanges as $otherKelas => $otherData) {
                        if (!isset($otherData['kategori']) || !is_array($otherData['kategori'])) continue;

                        foreach ($otherData['kategori'] as $otherKat => $otherRange) {
                            if ($otherKelas === $kelas) continue;

                            $otherMin = (int) ($otherRange['min'] ?? 0);
                            $otherMax = (int) ($otherRange['max'] ?? 0);

                            if ($otherMin <= $myMax && $otherMax >= $myMin) {
                                $otherIsSubset = ($otherMin >= $myMin && $otherMax <= $myMax);

                                if (!$otherIsSubset) {
                                    $overlapStart = max($myMin, $otherMin);
                                    $overlapEnd = min($myMax, $otherMax);
                                    for ($n = $overlapStart; $n <= $overlapEnd; $n++) {
                                        $excludedByOtherRanges[] = (string) $n;
                                    }
                                }
                            }
                        }
                    }
                }

                // 3. Hitung available
                $availableNumbers = [];
                for ($i = $myMin; $i <= $myMax; $i++) {
                    $numStr = (string) $i;
                    if (!in_array($numStr, $usedNumbers, false) && !in_array($numStr, $excludedByOtherRanges, false)) {
                        $availableNumbers[] = $i;
                    }
                }

                $label = $hasSubRange
                    ? "Kategori {$kategori} (Kelas {$kelas})"
                    : "Rentang Global ({$myMin}-{$myMax})";

                if (empty($availableNumbers)) {
                    $detail = '';
                    $usedInMyRange = 0;
                    for ($i = $myMin; $i <= $myMax; $i++) {
                        if (in_array((string)$i, $usedNumbers, false)) $usedInMyRange++;
                    }
                    if ($usedInMyRange > 0) {
                        $detail = ' (' . $usedInMyRange . ' nomor sudah dipakai)';
                    }
                    throw new \Exception('NOMOR TANK PENUH untuk ' . $label . ' (Rentang ' . $myMin . '-' . $myMax . ').' . $detail);
                }

                shuffle($availableNumbers);
                $ikan->nomor_tank = $availableNumbers[0];
                $ikan->save();
            });

            return response()->json([
                'success'    => true,
                'nomor_tank' => $ikan->fresh()->nomor_tank,
                'ikan_id'    => $ikan->id
            ]);
        } catch (\Throwable $e) {
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

    public function getListUsers()
    {
        $users = User::select('id', 'name', 'email', 'role', 'plain_password')
            ->orderBy('name')
            ->get()
            ->map(function ($u) {
                return [
                    'id'             => $u->id,
                    'name'           => $u->name,
                    'email'          => $u->email,
                    'role'           => $u->role ?? 'user',
                    'plain_password' => $u->plain_password ?? '-',
                ];
            });
        
        return response()->json($users);
    }

    public function updatePasswordUser(Request $request)
    {
        $request->validate(['user_id' => 'required|exists:users,id', 'new_password' => 'required|min:8']);
        $user = User::find($request->user_id);
        
        $oldPlain = $user->plain_password;
        $newPlain = $request->new_password;

        // Simpan ke Log Riwayat Password
        \App\Models\PasswordHistory::create([
            'user_id'     => $user->id,
            'old_password' => $oldPlain,
            'new_password' => $newPlain,
            'changed_by'   => auth()->user()->name,
        ]);

        // Update password user
        $user->password = $newPlain;
        $user->plain_password = $newPlain;
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

    public function toggleMvpIkan(Request $request)
    {
        $request->validate(['ikan_id' => 'required|exists:ikans,id']);

        $isOpen = \DB::table('settings')->where('key', 'mvp_registration_open')->value('value');
        if (!$isOpen || $isOpen === '0') {
            return response()->json(['success' => false, 'message' => 'Pendaftaran MVP belum dibuka oleh panitia.'], 403);
        }

        $ikan = Ikan::where('id', $request->ikan_id)
            ->whereHas('peserta', fn($q) => $q->where('user_id', Auth::id()))
            ->first();

        if (!$ikan) {
            return response()->json(['success' => false, 'message' => 'Ikan tidak ditemukan atau bukan milik Anda.'], 404);
        }

        // ★ GUARD: Cegah edit jika sudah dikirim
        if ($ikan->peserta->is_mvp_submitted) {
            return response()->json(['success' => false, 'message' => 'Data MVP sudah dikirim dan tidak dapat diubah.'], 403);
        }

        if (!$ikan->is_mvp) {
            $mvpCount = Ikan::where('peserta_id', $ikan->peserta_id)->where('is_mvp', true)->count();
            if ($mvpCount >= 30) {
                return response()->json(['success' => false, 'message' => 'Gagal! Batas maksimal 30 ikan untuk MVP sudah tercapai.'], 422);
            }
        }

        $ikan->is_mvp = !$ikan->is_mvp;
        $ikan->save();

        return response()->json([
            'success' => true, 
            'is_mvp' => $ikan->is_mvp,
            'message' => $ikan->is_mvp ? 'Ikan ditambahkan ke daftar MVP.' : 'Ikan dihapus dari daftar MVP.'
        ]);
    }

    // ★ METHOD BARU: SUBMIT MVP
    public function submitMvpIkan()
    {
        $peserta = Peserta::where('user_id', Auth::id())->first();
        if (!$peserta) return response()->json(['success' => false, 'message' => 'Profil peserta tidak ditemukan.'], 404);

        if ($peserta->is_mvp_submitted) {
            return response()->json(['success' => false, 'message' => 'Anda sudah mengirimkan data MVP sebelumnya.'], 400);
        }

        $mvpCount = Ikan::where('peserta_id', $peserta->id)->where('is_mvp', true)->count();
        if ($mvpCount === 0) {
            return response()->json(['success' => false, 'message' => 'Belum ada ikan yang dipilih sebagai MVP.'], 422);
        }

        $peserta->is_mvp_submitted = true;
        $peserta->save();

        return response()->json(['success' => true, 'message' => 'Data ikan MVP berhasil dikirim! Pilihan tidak dapat diubah lagi.']);
    }

    public function getMyIkans()
    {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json([
                'error' => 'unauthenticated',
                'message' => 'Sesi telah berakhir. Silakan login kembali.'
            ], 401);
        }
        
        $peserta = Peserta::where('user_id', $userId)->first();

        $resetSetting = \DB::table('settings')->where('key', 'tank_reset_info')->first();
        $resetInfo = null;
        if ($resetSetting) {
            $data = json_decode($resetSetting->value, true);
            $resetInfo = ['reason' => $data['reason'] ?? null, 'reset_at' => $data['reset_at'] ?? null];
        }

        $mvpOpen = (bool)(\DB::table('settings')->where('key', 'mvp_registration_open')->value('value') ?? false);
        $mvpSubmitted = $peserta ? $peserta->is_mvp_submitted : false;

        // ★ TAMBAHKAN INI (taruh di atas if (!$peserta))
        $maxTankRange = (int) (\DB::table('settings')->where('key', 'tank_range_max')->value('value') ?? 1000);

        if (!$peserta) {
            return response()->json([
                'ikans' => [], 
                'reset_info' => $resetInfo, 
                'mvp_open' => $mvpOpen, 
                'mvp_submitted' => $mvpSubmitted,
                'tank_range_max' => $maxTankRange,  // ★ TAMBAHKAN INI
            ]);
        }

        $ikans = $peserta->ikans()->orderBy('created_at', 'desc')->get()->map(function($ikan) {
            return [
                'id' => $ikan->id,
                'kategori' => $ikan->kategori,
                'kelas' => $ikan->kelas,
                'nomor_tank' => $ikan->nomor_tank,
                'is_mvp' => $ikan->is_mvp ?? false,
                'dibuat_oleh' => $ikan->dibuat_oleh ?? 'user',
            ];
        });

        return response()->json([
            'ikans' => $ikans,
            'reset_info' => $resetInfo,
            'mvp_open' => $mvpOpen,
            'mvp_submitted' => $mvpSubmitted,
            'tank_range_max' => $maxTankRange,  // ★ TAMBAHKAN INI
        ]);
    }

}