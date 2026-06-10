<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Peserta;
use App\Models\Ikan;
use App\Models\User;
use App\Models\Scoring;
use App\Helpers\PointCalculator;
use App\Services\SheetsSyncService;

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
        $ikansSaya = $pesertaSaya ? $pesertaSaya->ikans()->orderBy('created_at', 'desc')->get() : collect();

        $undianOpen = (bool)(\DB::table('settings')->where('key', 'undian_registration_open')->value('value') ?? true);

        // ★ DAFTAR KOTA & TEAM PER-USER (TIDAK lintas user)
        $daftarKota = collect();
        $daftarTeam = collect();

        if ($pesertaSaya) {
            $snapshots = Ikan::where('peserta_id', $pesertaSaya->id)
                ->whereNotNull('detail_anggota')
                ->where('detail_anggota', '!=', '')
                ->select('detail_anggota', 'jenis_keanggotaan')
                ->distinct()
                ->get();

            $daftarKota = $snapshots->where('jenis_keanggotaan', 'perorangan')->pluck('detail_anggota');
            $daftarTeam = $snapshots->where('jenis_keanggotaan', 'team')->pluck('detail_anggota');

            if (!empty($pesertaSaya->detail_anggota)) {
                if ($pesertaSaya->jenis_keanggotaan === 'perorangan') {
                    $daftarKota = $daftarKota->push($pesertaSaya->detail_anggota);
                } elseif ($pesertaSaya->jenis_keanggotaan === 'team') {
                    $daftarTeam = $daftarTeam->push($pesertaSaya->detail_anggota);
                }
            }

            $daftarKota = $daftarKota->unique()->sort()->values();
            $daftarTeam = $daftarTeam->unique()->sort()->values();
        }

        $teamChampionCount = 0;
        $mvpCount = 0;

        try { $teamChampionCount = $ikansSaya->where('is_team_champion', true)->count(); } catch (\Throwable $e) { $teamChampionCount = 0; }
        try { $mvpCount = $ikansSaya->where('is_mvp', true)->count(); } catch (\Throwable $e) { $mvpCount = 0; }

        $maxTeamChampion = 35;
        $maxMvp = 15;

        return view('dashboard.user', [
            'user'         => $user, 
            'pesertaSaya'  => $pesertaSaya,
            'ikansSaya'    => $ikansSaya,
            'undianOpen'   => $undianOpen,
            'daftarKota'   => $daftarKota,
            'daftarTeam'   => $daftarTeam,
            'teamChampionCount' => $teamChampionCount,
            'maxTeamChampion'   => $maxTeamChampion,
            'maxMvp'            => $maxMvp,
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
        $rules = [
            'kategori' => 'required|string|max:255',
        ];

        if (!in_array($request->kategori, ['Bonsai', 'Jumbo'])) {
            $rules['kelas'] = 'required|string|max:10';
        } else {
            $rules['kelas'] = 'nullable';
        }

        $request->validate($rules);

        $peserta = Peserta::where('user_id', Auth::id())->first();
        if (!$peserta) {
            return response()->json(['success' => false, 'message' => 'Silakan isi profil terlebih dahulu.'], 400);
        }

        $kelas = in_array($request->kategori, ['Bonsai', 'Jumbo']) ? null : $request->kelas;

        $ikan = Ikan::create([
            'peserta_id' => $peserta->id,
            'nama_peserta' => $peserta->nama_peserta, // ★ SNAPSHOT NAMA SAAT ITU
            'detail_anggota' => $peserta->detail_anggota, // ★ SNAPSHOT TEAM/CLUB SAAT ITU
            'jenis_keanggotaan' => $peserta->jenis_keanggotaan, // ★ SNAPSHOT JENIS KEANGGOTAAN SAAT ITU
            'kategori'   => $request->kategori,
            'kelas'      => $kelas,
            'dibuat_oleh' => 'user',
        ]);

        // ★ AUTO-SYNC PESERTA
        try { app(\App\Services\SheetsSyncService::class)->syncSemuaPeserta(); } catch (\Exception $e) { \Log::error('Sync peserta gagal (user storeIkan): ' . $e->getMessage()); }

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

        $globalMin = (int) (\DB::table('settings')->where('key', 'tank_range_min')->value('value') ?? 1);
        $globalMax = (int) (\DB::table('settings')->where('key', 'tank_range_max')->value('value') ?? 1000);

        $classRanges = json_decode(\DB::table('settings')->where('key', 'tank_class_ranges')->value('value'), true);

        $myMin = $globalMin;
        $myMax = $globalMax;
        $hasSubRange = false;

        if ($kelas && $classRanges && isset($classRanges[$kelas]['kategori'][$kategori])) {
            $myMin = (int) $classRanges[$kelas]['kategori'][$kategori]['min'];
            $myMax = (int) $classRanges[$kelas]['kategori'][$kategori]['max'];
            $hasSubRange = true;
        }

        // Fallback: Bonsai/Jumbo disimpan di key khusus (tanpa kelas)
        if (!$hasSubRange && in_array($kategori, ['Bonsai', 'Jumbo']) && $classRanges && isset($classRanges[$kategori]['kategori'][$kategori])) {
            $myMin = (int) $classRanges[$kategori]['kategori'][$kategori]['min'];
            $myMax = (int) $classRanges[$kategori]['kategori'][$kategori]['max'];
            $hasSubRange = true;
        }

        if ($myMin > $myMax) {
            return response()->json(['success' => false, 'message' => 'Rentang nomor tank tidak valid.'], 400);
        }

        try {
            DB::transaction(function () use ($ikan, $myMin, $myMax, $hasSubRange, $kategori, $kelas, $classRanges) {
                // Nomor yang sudah dipakai di database
                $usedSet = Ikan::whereNotNull('nomor_tank')
                    ->lockForUpdate()
                    ->pluck('nomor_tank')
                    ->map(fn($n) => (int) $n)
                    ->flip()
                    ->toArray();

                // Kumpulkan range kategori lain yang KETAT DI DALAM range saya
                $excludedRanges = [];
                if ($classRanges) {
                    foreach ($classRanges as $otherKelas => $otherData) {
                        if (!isset($otherData['kategori']) || !is_array($otherData['kategori'])) continue;
                        foreach ($otherData['kategori'] as $otherKat => $otherRange) {
                            $myLookupKey = $kelas ?: $kategori;
                            if ($otherKelas === $myLookupKey && $otherKat === $kategori) continue;
                            $oMin = (int) ($otherRange['min'] ?? 0);
                            $oMax = (int) ($otherRange['max'] ?? 0);
                            // Hanya exclude jika range lain KETAT DI DALAM range saya
                            if ($oMin > $myMin && $oMax < $myMax) {
                                $excludedRanges[] = ['min' => $oMin, 'max' => $oMax];
                            }
                        }
                    }
                }
                usort($excludedRanges, fn($a, $b) => $a['min'] <=> $b['min']);

                // Hitung sub-range tersedia (range saya minus range yang di-exclude)
                $subRanges = [];
                $cursor = $myMin;
                foreach ($excludedRanges as $ex) {
                    if ($ex['min'] > $cursor) {
                        $subRanges[] = ['min' => $cursor, 'max' => $ex['min'] - 1];
                    }
                    $cursor = $ex['max'] + 1;
                }
                if ($cursor <= $myMax) {
                    $subRanges[] = ['min' => $cursor, 'max' => $myMax];
                }

                if ($hasSubRange) {
                    $label = $kelas
                        ? "Kategori {$kategori} (Kelas {$kelas})"
                        : "Kategori {$kategori} (Tanpa Kelas)";
                } else {
                    $label = "Rentang Global ({$myMin}-{$myMax})";
                }

                if (empty($subRanges)) {
                    throw new \Exception('NOMOR TANK PENUH untuk ' . $label . '. Seluruh rentang ditempati oleh kategori lain.');
                }

                $rangeSize = $myMax - $myMin + 1;

                if ($rangeSize <= 50000) {
                    // Range kecil: langsung loop
                    $available = [];
                    foreach ($subRanges as $sub) {
                        for ($i = $sub['min']; $i <= $sub['max']; $i++) {
                            if (!isset($usedSet[$i])) $available[] = $i;
                        }
                    }
                    if (empty($available)) {
                        throw new \Exception('NOMOR TANK PENUH untuk ' . $label . ' (Rentang ' . $myMin . '-' . $myMax . ').');
                    }
                    shuffle($available);
                    $ikan->nomor_tank = $available[0];
                } else {
                    // Range besar: hitung available per sub-range, pilih weighted random
                    $subAvailCounts = [];
                    $totalAvail = 0;
                    foreach ($subRanges as $idx => $sub) {
                        $usedInSub = 0;
                        foreach ($usedSet as $num => $v) {
                            if ($num >= $sub['min'] && $num <= $sub['max']) $usedInSub++;
                        }
                        $avail = ($sub['max'] - $sub['min'] + 1) - $usedInSub;
                        $subAvailCounts[$idx] = $avail;
                        $totalAvail += $avail;
                    }
                    if ($totalAvail <= 0) {
                        throw new \Exception('NOMOR TANK PENUH untuk ' . $label . ' (Rentang ' . $myMin . '-' . $myMax . ').');
                    }

                    $rand = random_int(1, $totalAvail);
                    $cum = 0;
                    $chosenSub = null;
                    foreach ($subAvailCounts as $idx => $cnt) {
                        $cum += $cnt;
                        if ($rand <= $cum) { $chosenSub = $subRanges[$idx]; break; }
                    }

                    $maxAttempts = min(2000, $chosenSub['max'] - $chosenSub['min'] + 1);
                    $found = null;
                    for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
                        $candidate = random_int($chosenSub['min'], $chosenSub['max']);
                        if (!isset($usedSet[$candidate])) { $found = $candidate; break; }
                    }
                    if ($found === null) {
                        throw new \Exception('Gagal mendapatkan nomor. Coba lagi.');
                    }
                    $ikan->nomor_tank = $found;
                }

                $ikan->save();
            });

            // ★ AUTO-SYNC
            try { 
                app(\App\Services\SheetsSyncService::class)->syncSemuaPeserta(); 
            } catch (\Exception $e) { 
                \Log::error('Auto-sync peserta gagal (admin): ' . $e->getMessage()); 
            }

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

        // ★ GUARD: Cek apakah mesin undian dibuka
        $isOpen = \DB::table('settings')->where('key', 'undian_registration_open')->value('value');
        if (!$isOpen || $isOpen === '0') {
            return response()->json(['success' => false, 'message' => 'Mesin undian belum dibuka oleh panitia.'], 403);
        }

        $ikan = Ikan::where('id', $request->ikan_id)
            ->whereHas('peserta', fn($q) => $q->where('user_id', Auth::id()))
            ->whereNull('nomor_tank')
            ->first();

        if (!$ikan) {
            return response()->json(['success' => false, 'message' => 'Ikan tidak ditemukan, bukan milik Anda, atau sudah mendapat nomor.'], 400);
        }

        $kategori = $ikan->kategori;
        $kelas = $ikan->kelas;

        $globalMin = (int) (\DB::table('settings')->where('key', 'tank_range_min')->value('value') ?? 1);
        $globalMax = (int) (\DB::table('settings')->where('key', 'tank_range_max')->value('value') ?? 1000);

        $classRanges = json_decode(\DB::table('settings')->where('key', 'tank_class_ranges')->value('value'), true);

        $myMin = $globalMin;
        $myMax = $globalMax;
        $hasSubRange = false;

        if ($classRanges && isset($classRanges[$kelas]['kategori'][$kategori])) {
            $myMin = (int) $classRanges[$kelas]['kategori'][$kategori]['min'];
            $myMax = (int) $classRanges[$kelas]['kategori'][$kategori]['max'];
            $hasSubRange = true;
        }

        // Fallback: Bonsai/Jumbo disimpan di key khusus (tanpa kelas)
        if (!$hasSubRange && in_array($kategori, ['Bonsai', 'Jumbo']) && $classRanges && isset($classRanges[$kategori]['kategori'][$kategori])) {
            $myMin = (int) $classRanges[$kategori]['kategori'][$kategori]['min'];
            $myMax = (int) $classRanges[$kategori]['kategori'][$kategori]['max'];
            $hasSubRange = true;
        }

        if ($myMin > $myMax) {
            return response()->json(['success' => false, 'message' => 'Rentang nomor tank tidak valid.'], 400);
        }

        try {
            DB::transaction(function () use ($ikan, $myMin, $myMax, $hasSubRange, $kategori, $kelas, $classRanges) {
                $usedSet = Ikan::whereNotNull('nomor_tank')
                    ->lockForUpdate()
                    ->pluck('nomor_tank')
                    ->map(fn($n) => (int) $n)
                    ->flip()
                    ->toArray();

                $excludedRanges = [];
                if ($classRanges) {
                    foreach ($classRanges as $otherKelas => $otherData) {
                        if (!isset($otherData['kategori']) || !is_array($otherData['kategori'])) continue;
                        foreach ($otherData['kategori'] as $otherKat => $otherRange) {
                            if ($otherKelas === $kelas && $otherKat === $kategori) continue;
                            $oMin = (int) ($otherRange['min'] ?? 0);
                            $oMax = (int) ($otherRange['max'] ?? 0);
                            if ($oMin > $myMin && $oMax < $myMax) {
                                $excludedRanges[] = ['min' => $oMin, 'max' => $oMax];
                            }
                        }
                    }
                }
                usort($excludedRanges, fn($a, $b) => $a['min'] <=> $b['min']);

                $subRanges = [];
                $cursor = $myMin;
                foreach ($excludedRanges as $ex) {
                    if ($ex['min'] > $cursor) {
                        $subRanges[] = ['min' => $cursor, 'max' => $ex['min'] - 1];
                    }
                    $cursor = $ex['max'] + 1;
                }
                if ($cursor <= $myMax) {
                    $subRanges[] = ['min' => $cursor, 'max' => $myMax];
                }

                if ($hasSubRange) {
                    $label = $kelas
                        ? "Kategori {$kategori} (Kelas {$kelas})"
                        : "Kategori {$kategori} (Tanpa Kelas)";
                } else {
                    $label = "Rentang Global ({$myMin}-{$myMax})";
                }

                if (empty($subRanges)) {
                    throw new \Exception('NOMOR TANK PENUH untuk ' . $label . '. Seluruh rentang ditempati oleh kategori lain.');
                }

                $rangeSize = $myMax - $myMin + 1;

                if ($rangeSize <= 50000) {
                    $available = [];
                    foreach ($subRanges as $sub) {
                        for ($i = $sub['min']; $i <= $sub['max']; $i++) {
                            if (!isset($usedSet[$i])) $available[] = $i;
                        }
                    }
                    if (empty($available)) {
                        throw new \Exception('NOMOR TANK PENUH untuk ' . $label . ' (Rentang ' . $myMin . '-' . $myMax . ').');
                    }
                    shuffle($available);
                    $ikan->nomor_tank = $available[0];
                } else {
                    $subAvailCounts = [];
                    $totalAvail = 0;
                    foreach ($subRanges as $idx => $sub) {
                        $usedInSub = 0;
                        foreach ($usedSet as $num => $v) {
                            if ($num >= $sub['min'] && $num <= $sub['max']) $usedInSub++;
                        }
                        $avail = ($sub['max'] - $sub['min'] + 1) - $usedInSub;
                        $subAvailCounts[$idx] = $avail;
                        $totalAvail += $avail;
                    }
                    if ($totalAvail <= 0) {
                        throw new \Exception('NOMOR TANK PENUH untuk ' . $label . ' (Rentang ' . $myMin . '-' . $myMax . ').');
                    }

                    $rand = random_int(1, $totalAvail);
                    $cum = 0;
                    $chosenSub = null;
                    foreach ($subAvailCounts as $idx => $cnt) {
                        $cum += $cnt;
                        if ($rand <= $cum) { $chosenSub = $subRanges[$idx]; break; }
                    }

                    $maxAttempts = min(2000, $chosenSub['max'] - $chosenSub['min'] + 1);
                    $found = null;
                    for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
                        $candidate = random_int($chosenSub['min'], $chosenSub['max']);
                        if (!isset($usedSet[$candidate])) { $found = $candidate; break; }
                    }
                    if ($found === null) {
                        throw new \Exception('Gagal mendapatkan nomor. Coba lagi.');
                    }
                    $ikan->nomor_tank = $found;
                }

                $ikan->save();
            });

            // ★ AUTO-SYNC
            try { 
                app(\App\Services\SheetsSyncService::class)->syncSemuaPeserta(); 
            } catch (\Exception $e) { 
                \Log::error('Auto-sync peserta gagal (admin): ' . $e->getMessage()); 
            }

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

    public function toggleTeamChampionIkan(Request $request)
    {
        $request->validate([
            'ikan_id' => 'required|exists:ikans,id',
        ]);

        $isOpen = \DB::table('settings')->where('key', 'team_champion_registration_open')->value('value');
        if (!$isOpen || $isOpen === '0') {
            return response()->json([
                'success' => false,
                'message' => 'Pendaftaran Team Champion belum dibuka oleh panitia.',
            ], 403);
        }

        $ikan = Ikan::where('id', $request->ikan_id)
            ->whereHas('peserta', fn($q) => $q->where('user_id', Auth::id()))
            ->with('peserta')
            ->first();

        if (!$ikan) {
            return response()->json([
                'success' => false,
                'message' => 'Ikan tidak ditemukan atau bukan milik Anda.',
            ], 404);
        }

        if ($ikan->peserta->is_team_champion_submitted) {
            return response()->json([
                'success' => false,
                'message' => 'Data Team Champion sudah dikirim dan tidak dapat diubah.',
            ], 403);
        }

        if (!$ikan->is_team_champion) {
            $count = Ikan::where('peserta_id', $ikan->peserta_id)
                ->where('is_team_champion', true)
                ->count();

            if ($count >= 35) {
                return response()->json([
                    'success' => false,
                    'message' => 'Batas maksimal 35 ikan Team Champion sudah tercapai.',
                ], 422);
            }
        }

        $ikan->is_team_champion = !$ikan->is_team_champion;

        // Jika ikan dihapus dari Team Champion, otomatis hapus dari MVP juga
        // karena MVP hanya boleh dari ikan Team Champion.
        if (!$ikan->is_team_champion) {
            $ikan->is_mvp = false;
        }

        $ikan->save();

        return response()->json([
            'success' => true,
            'is_team_champion' => (bool) $ikan->is_team_champion,
            'is_mvp' => (bool) $ikan->is_mvp,
            'message' => $ikan->is_team_champion
                ? 'Ikan ditambahkan ke Team Champion.'
                : 'Ikan dihapus dari Team Champion.',
        ]);
    }

    public function submitTeamChampionIkan()
    {
        $peserta = Peserta::where('user_id', Auth::id())->first();

        if (!$peserta) {
            return response()->json([
                'success' => false,
                'message' => 'Profil peserta tidak ditemukan.',
            ], 404);
        }

        if ($peserta->is_team_champion_submitted) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah mengirimkan data Team Champion sebelumnya.',
            ], 400);
        }

        $count = Ikan::where('peserta_id', $peserta->id)
            ->where('is_team_champion', true)
            ->count();

        if ($count < 1) {
            return response()->json([
                'success' => false,
                'message' => 'Pilih minimal 1 ikan Team Champion sebelum mengirim.',
            ], 422);
        }

        if ($count > 35) {
            return response()->json([
                'success' => false,
                'message' => 'Team Champion maksimal 35 ikan. Saat ini terpilih ' . $count . ' ikan.',
            ], 422);
        }

        $peserta->is_team_champion_submitted = true;
        $peserta->save();

        return response()->json([
            'success' => true,
            'message' => 'Data Team Champion berhasil dikirim. Sekarang Anda dapat memilih maksimal 15 ikan untuk MVP.',
        ]);
    }

    public function toggleMvpIkan(Request $request)
    {
        $request->validate([
            'ikan_id' => 'required|exists:ikans,id',
        ]);

        $isOpen = \DB::table('settings')->where('key', 'mvp_registration_open')->value('value');
        if (!$isOpen || $isOpen === '0') {
            return response()->json([
                'success' => false,
                'message' => 'Pendaftaran MVP belum dibuka oleh panitia.',
            ], 403);
        }

        $ikan = Ikan::where('id', $request->ikan_id)
            ->whereHas('peserta', fn($q) => $q->where('user_id', Auth::id()))
            ->with('peserta')
            ->first();

        if (!$ikan) {
            return response()->json([
                'success' => false,
                'message' => 'Ikan tidak ditemukan atau bukan milik Anda.',
            ], 404);
        }

        if (!$ikan->is_team_champion) {
            return response()->json([
                'success' => false,
                'message' => 'MVP hanya bisa dipilih dari ikan yang sudah masuk Team Champion.',
            ], 422);
        }

        if ($ikan->peserta->is_mvp_submitted) {
            return response()->json([
                'success' => false,
                'message' => 'Data MVP sudah dikirim dan tidak dapat diubah.',
            ], 403);
        }

        if (!$ikan->is_mvp) {
            $mvpCount = Ikan::where('peserta_id', $ikan->peserta_id)
                ->where('is_mvp', true)
                ->count();

            if ($mvpCount >= 15) {
                return response()->json([
                    'success' => false,
                    'message' => 'Batas maksimal 15 ikan untuk MVP sudah tercapai.',
                ], 422);
            }
        }

        $ikan->is_mvp = !$ikan->is_mvp;
        $ikan->save();

        return response()->json([
            'success' => true,
            'is_mvp' => (bool) $ikan->is_mvp,
            'message' => $ikan->is_mvp
                ? 'Ikan ditambahkan ke daftar MVP.'
                : 'Ikan dihapus dari daftar MVP.',
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

        $mvpCount = Ikan::where('peserta_id', $peserta->id)
            ->where('is_mvp', true)
            ->where('is_team_champion', true)
            ->count();

        if ($mvpCount === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada ikan yang dipilih sebagai MVP.',
            ], 422);
        }

        if ($mvpCount > 15) {
            return response()->json([
                'success' => false,
                'message' => 'MVP maksimal 15 ikan.',
            ], 422);
        }

        $peserta->is_mvp_submitted = true;
        $peserta->save();

        // ★ AUTO-SYNC MVP
        try { app(\App\Services\SheetsSyncService::class)->syncMvp(); } catch (\Exception $e) { \Log::error('Auto-sync MVP gagal: ' . $e->getMessage()); }

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
        $mvpSubmitted = $peserta ? (bool) $peserta->is_mvp_submitted : false;

        $teamChampionOpen = (bool)(\DB::table('settings')->where('key', 'team_champion_registration_open')->value('value') ?? false);
        $teamChampionSubmitted = $peserta ? (bool) $peserta->is_team_champion_submitted : false;

        $undianOpen = (bool)(\DB::table('settings')->where('key', 'undian_registration_open')->value('value') ?? true);
        // ★ TAMBAHKAN INI (taruh di atas if (!$peserta))
        $maxTankRange = (int) (\DB::table('settings')->where('key', 'tank_range_max')->value('value') ?? 1000);

        if (!$peserta) {
            return response()->json([
                'ikans' => [], 
                'reset_info' => $resetInfo, 
                'mvp_open' => $mvpOpen, 
                'mvp_submitted' => $mvpSubmitted,
                'undian_open' => $undianOpen,
                'tank_range_max' => $maxTankRange,
                'max_mvp' => 15,
                'team_champion_open' => (bool)(\DB::table('settings')->where('key', 'team_champion_registration_open')->value('value') ?? false),
                'team_champion_submitted' => (bool)($peserta->is_team_champion_submitted ?? false),
                'max_team_champion' => 35,
            ]);
        }

        $ikans = $peserta->ikans()->orderBy('created_at', 'desc')->get()->map(function($ikan) {
            return [
                'id' => $ikan->id,
                'nama_peserta' => $ikan->nama_peserta,
                'detail_anggota' => $ikan->detail_anggota,
                'jenis_keanggotaan' => $ikan->jenis_keanggotaan, // ★ KIRIM JENIS KEANGGOTAAN HISTORIS
                'kategori' => $ikan->kategori,
                'kelas' => $ikan->kelas,
                'nomor_tank' => $ikan->nomor_tank,
                'is_team_champion' => (bool) ($ikan->is_team_champion ?? false),
                'is_mvp' => (bool) ($ikan->is_mvp ?? false),
                'dibuat_oleh' => $ikan->dibuat_oleh ?? 'user',
            ];
        });

        $maxMvp = 15;
        $maxTeamChampion = 35;

        $myResults = [];
        $myMvpResults = [];

        $resultUnlocked = $peserta && $peserta->result_unlocked_at ? true : false;

        $resultDebug = [
            'result_unlocked' => $resultUnlocked,
            'peserta_id' => $peserta ? $peserta->id : null,
            'total_ikan_user' => $peserta ? $peserta->ikans()->count() : 0,
            'ikan_terkunci' => $peserta ? $peserta->ikans()->where('is_locked', true)->count() : 0,
            'ikan_punya_nomor_tank' => $peserta ? $peserta->ikans()->whereNotNull('nomor_tank')->count() : 0,
            'ikan_punya_scoring' => $peserta ? $peserta->ikans()->whereHas('scorings')->count() : 0,
            'ikan_final_layak_tampil' => 0,
        ];

        if ($peserta && $resultUnlocked) {
            // Jangan cache hasil juara dulu, agar setelah admin kirim/lock hasil langsung tampil.
            \Cache::forget('user_results_' . $userId);

            $myFinalIkans = $peserta->ikans()
                ->where('is_locked', true)
                ->whereNotNull('nomor_tank')
                ->whereHas('scorings')
                ->with(['scorings', 'bonusPoints'])
                ->get();

            $resultDebug['ikan_final_layak_tampil'] = $myFinalIkans->count();

            if ($myFinalIkans->isNotEmpty()) {
                $groups = [];

                foreach ($myFinalIkans as $ikan) {
                    $key = $ikan->kategori . '|' . ($ikan->kelas ?? '-');
                    if (!isset($groups[$key])) {
                        $groups[$key] = [];
                    }

                    $groups[$key][] = $ikan;
                }

                foreach ($groups as $key => $userIkans) {
                    [$kat, $kls] = explode('|', $key, 2);
                    $kls = ($kls === '-') ? null : $kls;

                    $poolQuery = Ikan::where('is_locked', true)
                        ->whereNotNull('nomor_tank')
                        ->where('kategori', $kat)
                        ->whereHas('scorings')
                        ->with(['scorings', 'bonusPoints']);

                    if ($kls !== null && $kls !== '') {
                        $poolQuery->where('kelas', $kls);
                    } else {
                        $poolQuery->whereNull('kelas');
                    }

                    $allIkans = $poolQuery->get();

                    $allItems = [];

                    foreach ($allIkans as $pi) {
                        $scorings = $pi->scorings;

                        if ($scorings->isEmpty()) {
                            continue;
                        }

                        $avgDetail = [];
                        $jumlahJuriYangNilai = 0;

                        foreach ($scorings as $s) {
                            if ($s->total_nilai) {
                                $jumlahJuriYangNilai++;
                            }

                            if ($s->nilai_detail && is_array($s->nilai_detail)) {
                                foreach ($s->nilai_detail as $kt => $fields) {
                                    if (!is_array($fields)) {
                                        continue;
                                    }

                                    foreach ($fields as $fid => $val) {
                                        if (!isset($avgDetail[$kt][$fid])) {
                                            $avgDetail[$kt][$fid] = [
                                                'sum' => 0,
                                                'count' => 0,
                                            ];
                                        }

                                        $avgDetail[$kt][$fid]['sum'] += (float)($val ?? 0);
                                        $avgDetail[$kt][$fid]['count']++;
                                    }
                                }
                            }
                        }

                        $finalAvgDetail = [];

                        foreach ($avgDetail as $kt => $fields) {
                            foreach ($fields as $fid => $d) {
                                $finalAvgDetail[$kt][$fid] = $d['count'] > 0
                                    ? $d['sum'] / $d['count']
                                    : 0;
                            }
                        }

                        $defectSource = $scorings->first(function ($s) {
                            return $s->edited_by_grand_juri;
                        }) ?: $scorings->sortByDesc('updated_at')->first();

                        $mergedDefect = [
                            'raw_head_penalty'    => $defectSource ? ($defectSource->raw_head_penalty ?: ['0']) : ['0'],
                            'raw_face_penalty'    => $defectSource ? ($defectSource->raw_face_penalty ?: ['0']) : ['0'],
                            'raw_body_penalty'    => $defectSource ? ($defectSource->raw_body_penalty ?: ['0']) : ['0'],
                            'raw_finnage_penalty' => $defectSource ? ($defectSource->raw_finnage_penalty ?: ['0']) : ['0'],
                        ];

                        $totalPoint = PointCalculator::hitungPoint($pi->kategori, $finalAvgDetail, $mergedDefect);
                        $totalBonus = (int) $pi->bonusPoints->sum('points');

                        $allItems[] = [
                            'ikan_id' => $pi->id,
                            'total_point' => (float) $totalPoint,
                            'total_bonus' => $totalBonus,

                            // Ambil subtotal dari breakdown yang sama dengan admin/grand juri.
                            // Tidak hardcode rumus dan tidak perlu migration.
                            'component_subtotals' => $this->buildComponentSubtotalsFromBreakdown(
                                $pi->kategori,
                                $finalAvgDetail,
                                $mergedDefect
                            ),
                        ];
                    }

                    $ranked = PointCalculator::hitungRankPoints($allItems, 'total_point');

                    $userIkanIds = collect($userIkans)->pluck('id')->toArray();

                    foreach ($ranked as $idx => $r) {
                        if (!in_array($r['ikan_id'], $userIkanIds)) {
                            continue;
                        }

                        $ikan = $myFinalIkans->firstWhere('id', $r['ikan_id']);

                        if (!$ikan) {
                            continue;
                        }

                        $groupLabel = $ikan->kategori;
                        if (!in_array($ikan->kategori, ['Bonsai', 'Jumbo']) && $ikan->kelas) {
                            $groupLabel .= ' - Kelas ' . $ikan->kelas;
                        }

                        $bonusTotal = (int) $ikan->bonusPoints->sum('points');
                        $rankPoint = (int) ($r['rank_point'] ?? 0);

                        $myResults[] = [
                            'ikan_id'             => $ikan->id,
                            'nama_peserta'        => $ikan->nama_peserta ?? '-',
                            'jenis_keanggotaan'   => $ikan->jenis_keanggotaan ?? '-',
                            'asal_label'          => $ikan->detail_anggota ?? '-',
                            'kategori'            => $ikan->kategori,
                            'kelas'               => $ikan->kelas ?? '-',
                            'group_key'           => $ikan->kategori . '|' . ($ikan->kelas ?? '-'),
                            'group_label'         => $groupLabel,
                            'detail_anggota'      => $ikan->detail_anggota ?? '-',
                            'point'               => round((float)($r['total_point'] ?? 0), 2),
                            'rank_point'          => $rankPoint,
                            'position'            => $idx + 1,
                            'nomor_tank'          => $ikan->nomor_tank,
                            'total_bonus'         => $bonusTotal,
                            'final_rank_point'    => $rankPoint + $bonusTotal,
                            'bonus_list'          => $ikan->bonusPoints->pluck('bonus_type')->toArray(),
                            'component_subtotals' => $r['component_subtotals'] ?? [
                                'overall' => ['label' => 'Overall', 'value' => 0],
                                'head'    => ['label' => 'Head', 'value' => 0],
                                'face'    => ['label' => 'Face', 'value' => 0],
                                'body'    => ['label' => 'Body', 'value' => 0],
                                'marking' => ['label' => 'Marking', 'value' => 0],
                                'pearl'   => ['label' => 'Pearl', 'value' => 0],
                                'color'   => ['label' => 'Color', 'value' => 0],
                                'finnage' => ['label' => 'Finnage', 'value' => 0],
                            ],
                        ];
                    }
                }

                // Data MVP khusus ikan milik user/team login sendiri.
                $myMvpResults = $myFinalIkans
                    ->where('is_mvp', true)
                    ->map(function ($ikan) use ($myResults) {
                        $rankInfo = collect($myResults)->firstWhere('ikan_id', $ikan->id);

                        $groupLabel = $ikan->kategori;
                        if (!in_array($ikan->kategori, ['Bonsai', 'Jumbo']) && $ikan->kelas) {
                            $groupLabel .= ' - Kelas ' . $ikan->kelas;
                        }

                        return [
                            'ikan_id'             => $ikan->id,
                            'nama_peserta'        => $ikan->nama_peserta ?? '-',
                            'jenis_keanggotaan'   => $ikan->jenis_keanggotaan ?? '-',
                            'asal_label'          => $ikan->detail_anggota ?? '-',
                            'detail_anggota'      => $ikan->detail_anggota ?? '-',
                            'kategori'            => $ikan->kategori,
                            'kelas'               => $ikan->kelas ?? '-',
                            'group_key'           => $ikan->kategori . '|' . ($ikan->kelas ?? '-'),
                            'group_label'         => $groupLabel,
                            'nomor_tank'          => $ikan->nomor_tank ?? '-',
                            'position'            => $rankInfo['position'] ?? 0,
                            'rank_point'          => $rankInfo['rank_point'] ?? 0,
                            'bonus_list'          => $ikan->bonusPoints->pluck('bonus_type')->toArray(),
                            'total_bonus'         => (int) $ikan->bonusPoints->sum('points'),
                            'final_rank_point'    => (int)($rankInfo['rank_point'] ?? 0) + (int)$ikan->bonusPoints->sum('points'),
                            'component_subtotals' => $rankInfo['component_subtotals'] ?? [
                                'overall' => ['label' => 'Overall', 'value' => 0],
                                'head'    => ['label' => 'Head', 'value' => 0],
                                'face'    => ['label' => 'Face', 'value' => 0],
                                'body'    => ['label' => 'Body', 'value' => 0],
                                'marking' => ['label' => 'Marking', 'value' => 0],
                                'pearl'   => ['label' => 'Pearl', 'value' => 0],
                                'color'   => ['label' => 'Color', 'value' => 0],
                                'finnage' => ['label' => 'Finnage', 'value' => 0],
                            ],
                        ];
                    })
                    ->values()
                    ->toArray();
            }
        }

        return response()->json([
            'ikans' => $ikans,
            'reset_info' => $resetInfo,

            'mvp_open' => $mvpOpen,
            'mvp_submitted' => $mvpSubmitted,
            'max_mvp' => 15,

            'team_champion_open' => $teamChampionOpen,
            'team_champion_submitted' => $teamChampionSubmitted,
            'max_team_champion' => 35,

            'undian_open' => $undianOpen,
            'tank_range_max' => $maxTankRange,

            // Untuk halaman hasil juara
            'result_unlocked' => $resultUnlocked,
            'my_results' => $myResults,
            'my_mvp_results' => $myMvpResults,

            // Untuk debugging di browser console
            'result_debug' => $resultDebug,
        ]);
    }

    private function buildComponentSubtotalsFromBreakdown(string $kategori, array $finalAvgDetail, array $mergedDefect): array
    {
        $empty = [
            'overall' => ['label' => 'Overall', 'value' => 0],
            'head'    => ['label' => 'Head', 'value' => 0],
            'face'    => ['label' => 'Face', 'value' => 0],
            'body'    => ['label' => 'Body Shape', 'value' => 0],
            'marking' => ['label' => 'Marking', 'value' => 0],
            'pearl'   => ['label' => 'Pearl', 'value' => 0],
            'color'   => ['label' => 'Color', 'value' => 0],
            'finnage' => ['label' => 'Finnage', 'value' => 0],
        ];

        $breakdown = PointCalculator::hitungBreakdown($kategori, $finalAvgDetail, $mergedDefect);

        $aliases = [
            'overall' => ['overall'],
            'head'    => ['head'],
            'face'    => ['face'],
            'body'    => ['body', 'body_shape', 'body shape', 'bodyshape'],
            'marking' => ['marking'],
            'pearl'   => ['pearl'],
            'color'   => ['color'],
            'finnage' => ['finnage'],
        ];

        foreach ($breakdown as $key => $row) {
            $normalizedKey = strtolower(str_replace(['_', '-'], ' ', (string) $key));
            $targetKey = null;

            foreach ($aliases as $componentKey => $possibleKeys) {
                foreach ($possibleKeys as $possibleKey) {
                    if ($normalizedKey === strtolower(str_replace(['_', '-'], ' ', $possibleKey))) {
                        $targetKey = $componentKey;
                        break 2;
                    }
                }
            }

            if (!$targetKey) {
                continue;
            }

            $value = 0;

            if (is_array($row)) {
                $value =
                    $row['subtotal'] ??
                    $row['total'] ??
                    $row['point'] ??
                    $row['value'] ??
                    $row['nilai'] ??
                    0;
            } elseif (is_numeric($row)) {
                $value = $row;
            }

            $empty[$targetKey]['value'] = round((float) $value, 2);
        }

        return $empty;
    }

    public function hasilJuara()
    {
        $user = Auth::user()->fresh();
        if (!$user) return redirect()->route('login');

        $peserta = Peserta::where('user_id', $user->id)->first();
        $initial = strtoupper(mb_substr(trim($user->name), 0, 1));

        return view('dashboard.hasil-juara', [
            'user' => $user,
            'peserta' => $peserta,
            'initial' => $initial,
        ]);
    }

}