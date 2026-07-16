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
    private function cleanExcelDisplayValue($value, $emptyValue = '')
    {
        $value = trim((string) ($value ?? ''));

        if ($value === '') {
            return $emptyValue;
        }

        $looksLikeExcelFormula =
            substr($value, 0, 1) === '=' ||
            stripos($value, '__xludf') !== false ||
            stripos($value, 'DUMMYFUNCTION') !== false ||
            stripos($value, 'COMPUTED_VALUE') !== false;

        if (!$looksLikeExcelFormula) {
            return $value;
        }

        // Contoh:
        // =IFERROR(__xludf.DUMMYFUNCTION("""COMPUTED_VALUE"""),"Adi")
        // akan tampil sebagai: Adi
        //
        // Kalau fallback kosong:
        // =IFERROR(...,"")
        // akan tampil kosong.
        if (preg_match('/,\s*"((?:[^"]|"")*)"\s*\)\s*$/u', $value, $match)) {
            $fallback = trim(str_replace('""', '"', $match[1]));

            if ($fallback !== '' && stripos($fallback, 'COMPUTED_VALUE') === false) {
                return $fallback;
            }
        }

        return $emptyValue;
    }

    private function getRegistrationLimit(string $key, int $default): int
    {
        $value = DB::table('settings')->where('key', $key)->value('value');
        $limit = (int) ($value ?? $default);

        return max(1, $limit);
    }

    private function isFeatureEnabled(string $key): bool
    {
        $val = \DB::table('settings')->where('key', $key)->value('value');
        return ($val === null) ? true : ($val === '1');
    }

    private function isResultsGloballyPublished(): bool
    {
        return DB::table('settings')
            ->where('key', 'results_global_published')
            ->value('value') === '1';
    }

    public function index()
    {
        $user = Auth::user()->fresh();
        Auth::setUser($user);

        if ($user->role === 'admin') return view('dashboard.admin', ['user' => $user]);
        if ($user->role === 'grand_juri') return view('dashboard.grand-juri', ['user' => $user]);
        if ($user->role === 'juri') return view('dashboard.juri', ['user' => $user]);

        $user->name = $this->cleanExcelDisplayValue($user->name, '');

        $pesertaSaya = Peserta::where('user_id', $user->id)->first();

        if ($pesertaSaya) {
            $pesertaSaya->nama_peserta = $this->cleanExcelDisplayValue($pesertaSaya->nama_peserta, '');

            $jenisPeserta = $this->cleanExcelDisplayValue($pesertaSaya->jenis_keanggotaan, 'perorangan');
            $pesertaSaya->jenis_keanggotaan = in_array($jenisPeserta, ['perorangan', 'team'])
                ? $jenisPeserta
                : 'perorangan';

            $pesertaSaya->detail_anggota = $this->cleanExcelDisplayValue($pesertaSaya->detail_anggota, '');
        }

        $ikansSaya = $pesertaSaya
            ? $pesertaSaya->ikans()
                ->withCount('fotos')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($ikan) {
                    $ikan->nama_peserta = $this->cleanExcelDisplayValue($ikan->nama_peserta, '');
                    $ikan->detail_anggota = $this->cleanExcelDisplayValue($ikan->detail_anggota, '');
                    $ikan->jenis_keanggotaan = $this->cleanExcelDisplayValue($ikan->jenis_keanggotaan, '-');

                    return $ikan;
                })
            : collect();

        $undianOpen = (bool)(\DB::table('settings')->where('key', 'undian_registration_open')->value('value') ?? true);
        $teamChampionOpen = (bool)(\DB::table('settings')->where('key', 'team_champion_registration_open')->value('value') ?? false);
        $mvpOpen = (bool)(\DB::table('settings')->where('key', 'mvp_registration_open')->value('value') ?? false);
        $mvpFeatureEnabled = $this->isFeatureEnabled('mvp_feature_enabled');
        $teamChampionFeatureEnabled = $this->isFeatureEnabled('team_champion_feature_enabled');

        // ★ DAFTAR KOTA & TEAM PER-USER (TIDAK lintas user)
        $daftarKota = collect();
        $daftarTeam = collect();

        if ($pesertaSaya) {
            $snapshots = Ikan::where('peserta_id', $pesertaSaya->id)
                ->whereNotNull('detail_anggota')
                ->where('detail_anggota', '!=', '')
                ->select('detail_anggota', 'jenis_keanggotaan')
                ->distinct()
                ->get()
                ->map(function ($ikan) {
                    $jenis = $this->cleanExcelDisplayValue($ikan->jenis_keanggotaan, '');
                    $detail = $this->cleanExcelDisplayValue($ikan->detail_anggota, '');

                    return [
                        'jenis_keanggotaan' => $jenis,
                        'detail_anggota'    => $detail,
                    ];
                })
                ->filter(function ($row) {
                    return trim($row['detail_anggota']) !== '';
                })
                ->values();

            $daftarKota = $snapshots
                ->where('jenis_keanggotaan', 'perorangan')
                ->pluck('detail_anggota')
                ->filter()
                ->values();

            $daftarTeam = $snapshots
                ->where('jenis_keanggotaan', 'team')
                ->pluck('detail_anggota')
                ->filter()
                ->values();

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

        $maxTeamChampion = $this->getRegistrationLimit('team_champion_registration_max', 35);
        $maxMvp = $this->getRegistrationLimit('mvp_registration_max', 15);

        return view('dashboard.user', [
            'user'         => $user, 
            'pesertaSaya'  => $pesertaSaya,
            'ikansSaya'    => $ikansSaya,
            'undianOpen'   => $undianOpen,
            'teamChampionOpen' => $teamChampionOpen,
            'mvpOpen'          => $mvpOpen,
            'mvpFeatureEnabled' => $mvpFeatureEnabled,
            'teamChampionFeatureEnabled' => $teamChampionFeatureEnabled,
            'daftarKota'   => $daftarKota,
            'daftarTeam'   => $daftarTeam,
            'teamChampionCount' => $teamChampionCount,
            'mvpCount'          => $mvpCount,
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

        if (!in_array($request->kategori, \App\Helpers\Taxonomy::noKelasNames())) {
            $rules['kelas'] = 'required|string|max:10';
        } else {
            $rules['kelas'] = 'nullable';
        }

        $request->validate($rules);

        $peserta = Peserta::where('user_id', Auth::id())->first();
        if (!$peserta) {
            return response()->json(['success' => false, 'message' => 'Silakan isi profil terlebih dahulu.'], 400);
        }

        $kelas = in_array($request->kategori, \App\Helpers\Taxonomy::noKelasNames()) ? null : $request->kelas;

        $ikan = Ikan::create([
            'peserta_id' => $peserta->id,
            'nama_peserta' => $this->cleanExcelDisplayValue($peserta->nama_peserta, ''),
            'detail_anggota' => $this->cleanExcelDisplayValue($peserta->detail_anggota, ''),
            'jenis_keanggotaan' => $this->cleanExcelDisplayValue($peserta->jenis_keanggotaan, 'perorangan'),
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
        if (!$hasSubRange && in_array($kategori, \App\Helpers\Taxonomy::noKelasNames()) && $classRanges && isset($classRanges[$kategori]['kategori'][$kategori])) {
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
        if (!$hasSubRange && in_array($kategori, \App\Helpers\Taxonomy::noKelasNames()) && $classRanges && isset($classRanges[$kategori]['kategori'][$kategori])) {
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

        if (!$this->isFeatureEnabled('team_champion_feature_enabled')) {
            return response()->json([
                'success' => false,
                'message' => 'Fitur Team Champion sedang dinonaktifkan oleh panitia.',
            ], 403);
        }

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
            $maxTeamChampion = $this->getRegistrationLimit('team_champion_registration_max', 35);

            $count = Ikan::where('peserta_id', $ikan->peserta_id)
                ->where('is_team_champion', true)
                ->count();

            if ($count >= $maxTeamChampion) {
                return response()->json([
                    'success' => false,
                    'message' => 'Batas maksimal ' . $maxTeamChampion . ' ikan Team Champion sudah tercapai.',
                ], 422);
            }
        }

        $ikan->is_team_champion = !$ikan->is_team_champion;

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
        if (!$this->isFeatureEnabled('team_champion_feature_enabled')) {
            return response()->json([
                'success' => false,
                'message' => 'Fitur Team Champion sedang dinonaktifkan oleh panitia.',
            ], 403);
        }

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

        $maxTeamChampion = $this->getRegistrationLimit('team_champion_registration_max', 35);

        $count = Ikan::where('peserta_id', $peserta->id)
            ->where('is_team_champion', true)
            ->count();

        if ($count < 1) {
            return response()->json([
                'success' => false,
                'message' => 'Pilih minimal 1 ikan Team Champion sebelum mengirim.',
            ], 422);
        }

        if ($count > $maxTeamChampion) {
            return response()->json([
                'success' => false,
                'message' => 'Team Champion maksimal ' . $maxTeamChampion . ' ikan. Saat ini terpilih ' . $count . ' ikan.',
            ], 422);
        }

        $peserta->is_team_champion_submitted = true;
        $peserta->save();

        return response()->json([
            'success' => true,
            'message' => 'Data Team Champion berhasil dikirim. Pilihan MVP tetap terpisah dan dapat dipilih dari daftar ikan Anda.',
        ]);
    }

    public function toggleMvpIkan(Request $request)
    {
        $request->validate([
            'ikan_id' => 'required|exists:ikans,id',
        ]);

        if (!$this->isFeatureEnabled('mvp_feature_enabled')) {
            return response()->json([
                'success' => false,
                'message' => 'Fitur MVP sedang dinonaktifkan oleh panitia.',
            ], 403);
        }

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

        if ($ikan->peserta->is_mvp_submitted) {
            return response()->json([
                'success' => false,
                'message' => 'Data MVP sudah dikirim dan tidak dapat diubah.',
            ], 403);
        }

        if (!$ikan->is_mvp) {
            $maxMvp = $this->getRegistrationLimit('mvp_registration_max', 15);

            $mvpCount = Ikan::where('peserta_id', $ikan->peserta_id)
                ->where('is_mvp', true)
                ->count();

            if ($mvpCount >= $maxMvp) {
                return response()->json([
                    'success' => false,
                    'message' => 'Batas maksimal ' . $maxMvp . ' ikan untuk MVP sudah tercapai.',
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
        if (!$this->isFeatureEnabled('mvp_feature_enabled')) {
            return response()->json(['success' => false, 'message' => 'Fitur MVP sedang dinonaktifkan oleh panitia.'], 403);
        }

        $peserta = Peserta::where('user_id', Auth::id())->first();
        if (!$peserta) return response()->json(['success' => false, 'message' => 'Profil peserta tidak ditemukan.'], 404);

        if ($peserta->is_mvp_submitted) {
            return response()->json(['success' => false, 'message' => 'Anda sudah mengirimkan data MVP sebelumnya.'], 400);
        }

        $maxMvp = $this->getRegistrationLimit('mvp_registration_max', 15);

        $mvpCount = Ikan::where('peserta_id', $peserta->id)
            ->where('is_mvp', true)
            ->count();

        if ($mvpCount === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada ikan yang dipilih sebagai MVP.',
            ], 422);
        }

        if ($mvpCount > $maxMvp) {
            return response()->json([
                'success' => false,
                'message' => 'MVP maksimal ' . $maxMvp . ' ikan.',
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
        $maxMvp = $this->getRegistrationLimit('mvp_registration_max', 15);
        $maxTeamChampion = $this->getRegistrationLimit('team_champion_registration_max', 35);

        if (!$peserta) {
            return response()->json([
                'ikans' => [], 
                'reset_info' => $resetInfo, 
                'mvp_open' => $mvpOpen, 
                'mvp_submitted' => $mvpSubmitted,
                'undian_open' => $undianOpen,
                'tank_range_max' => $maxTankRange,
                'max_mvp' => $maxMvp,
                'team_champion_open' => $teamChampionOpen,
                'team_champion_submitted' => $teamChampionSubmitted,
                'max_team_champion' => $maxTeamChampion,
            ]);
        }

        $ikans = $peserta->ikans()->withCount('fotos')->orderBy('created_at', 'desc')->get()->map(function($ikan) {
            return [
                'id' => $ikan->id,
                'nama_peserta' => $this->cleanExcelDisplayValue($ikan->nama_peserta, ''),
                'detail_anggota' => $this->cleanExcelDisplayValue($ikan->detail_anggota, ''),
                'jenis_keanggotaan' => $this->cleanExcelDisplayValue($ikan->jenis_keanggotaan, '-'),
                'kategori' => $ikan->kategori,
                'kelas' => $ikan->kelas,
                'nomor_tank' => $ikan->nomor_tank,
                'is_team_champion' => (bool) ($ikan->is_team_champion ?? false),
                'is_mvp' => (bool) ($ikan->is_mvp ?? false),
                'has_foto' => ($ikan->fotos_count ?? 0) > 0,
                'dibuat_oleh' => $ikan->dibuat_oleh ?? 'user',
            ];
        });

        $maxMvp = $this->getRegistrationLimit('mvp_registration_max', 15);
        $maxTeamChampion = $this->getRegistrationLimit('team_champion_registration_max', 35);

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
                        if (!in_array($ikan->kategori, \App\Helpers\Taxonomy::noKelasNames()) && $ikan->kelas) {
                            $groupLabel .= ' - Kelas ' . $ikan->kelas;
                        }

                        $bonusTotal = (int) $ikan->bonusPoints->sum('points');
                        $rankPoint = (int) ($r['rank_point'] ?? 0);

                        $myResults[] = [
                            'ikan_id'             => $ikan->id,
                            'nama_peserta'        => $this->cleanExcelDisplayValue($ikan->nama_peserta, ''),
                            'jenis_keanggotaan'   => $this->cleanExcelDisplayValue($ikan->jenis_keanggotaan, '-'),
                            'asal_label'          => $this->cleanExcelDisplayValue($ikan->detail_anggota, ''),
                            'kategori'            => $ikan->kategori,
                            'kelas'               => $ikan->kelas ?? '-',
                            'group_key'           => $ikan->kategori . '|' . ($ikan->kelas ?? '-'),
                            'group_label'         => $groupLabel,
                            'detail_anggota'      => $this->cleanExcelDisplayValue($ikan->detail_anggota, ''), 
                            'point'               => round((float)($r['total_point'] ?? 0), 2),
                            'rank_point'          => $rankPoint,
                            'position' => (int) ($r['position'] ?? ($idx + 1)),
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
                        if (!in_array($ikan->kategori, \App\Helpers\Taxonomy::noKelasNames()) && $ikan->kelas) {
                            $groupLabel .= ' - Kelas ' . $ikan->kelas;
                        }

                        return [
                            'ikan_id'             => $ikan->id,
                            'nama_peserta'        => $this->cleanExcelDisplayValue($ikan->nama_peserta, ''),
                            'jenis_keanggotaan'   => $this->cleanExcelDisplayValue($ikan->jenis_keanggotaan, '-'),
                            'asal_label'          => $this->cleanExcelDisplayValue($ikan->detail_anggota, ''),
                            'detail_anggota'      => $this->cleanExcelDisplayValue($ikan->detail_anggota, ''),
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

        // ★ MODUL 3: sembunyikan hasil MVP di Hasil Juara saat fitur MVP dinonaktifkan
        if (!$this->isFeatureEnabled('mvp_feature_enabled')) {
            $myMvpResults = [];
        }

        return response()->json([
            'ikans' => $ikans,
            'reset_info' => $resetInfo,

            'mvp_open' => $mvpOpen,
            'mvp_submitted' => $mvpSubmitted,
            'max_mvp' => $maxMvp,

            'team_champion_open' => $teamChampionOpen,
            'team_champion_submitted' => $teamChampionSubmitted,
            'max_team_champion' => $maxTeamChampion,

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

    /* ═══════════════════════════════════════════
       HASIL JUARA GLOBAL — semua team (hasil juara, MVP, Team Champion)
       Ranking dihitung terhadap seluruh pool (rumus tidak berubah),
       hanya menampilkan ikan dari peserta yang hasilnya sudah dibuka.
       ═══════════════════════════════════════════ */
    public function getPublicResults()
    {
        if (!Auth::id()) {
            return response()->json(['error' => 'unauthenticated', 'message' => 'Sesi telah berakhir. Silakan login kembali.'], 401);
        }

        $mvpFeatureEnabled = $this->isFeatureEnabled('mvp_feature_enabled');
        $tcFeatureEnabled  = $this->isFeatureEnabled('team_champion_feature_enabled');

        $published = $this->isResultsGloballyPublished();

        if (!$published) {
            return response()->json([
                'published' => false,
                'results' => [],
                'mvp' => [],
                'team_champion' => [],
                'mvp_feature_enabled' => $mvpFeatureEnabled,
                'team_champion_feature_enabled' => $tcFeatureEnabled,
            ]);
        }

        // Ikan final yang sudah DIPUBLIKASI (peserta-nya sudah dibuka hasilnya).
        $finalIkans = Ikan::where('is_locked', true)
            ->whereNotNull('nomor_tank')
            ->whereHas('scorings')
            ->with(['peserta', 'scorings', 'bonusPoints'])
            ->get();

        // Kelompokkan per kategori|kelas
        $groups = [];
        foreach ($finalIkans as $ikan) {
            $groups[$ikan->kategori . '|' . ($ikan->kelas ?? '-')][] = $ikan;
        }

        $results    = [];
        $rankByIkan = [];

        foreach ($groups as $key => $publishedIkans) {
            [$kat, $kls] = explode('|', $key, 2);
            $kls = ($kls === '-') ? null : $kls;

            // Pool peringkat = SEMUA ikan terkunci+dinilai di grup (posisi benar).
            $poolQuery = Ikan::where('is_locked', true)
                ->whereNotNull('nomor_tank')
                ->where('kategori', $kat)
                ->whereHas('scorings')
                ->with(['scorings', 'bonusPoints']);
            if ($kls !== null && $kls !== '') $poolQuery->where('kelas', $kls);
            else                              $poolQuery->whereNull('kelas');
            $pool = $poolQuery->get();

            $allItems = [];
            foreach ($pool as $pi) {
                $scorings = $pi->scorings;
                if ($scorings->isEmpty()) continue;

                $avgDetail = [];
                foreach ($scorings as $s) {
                    if ($s->nilai_detail && is_array($s->nilai_detail)) {
                        foreach ($s->nilai_detail as $kt => $fields) {
                            if (!is_array($fields)) continue;
                            foreach ($fields as $fid => $val) {
                                if (!isset($avgDetail[$kt][$fid])) $avgDetail[$kt][$fid] = ['sum' => 0, 'count' => 0];
                                $avgDetail[$kt][$fid]['sum'] += (float)($val ?? 0);
                                $avgDetail[$kt][$fid]['count']++;
                            }
                        }
                    }
                }
                $finalAvgDetail = [];
                foreach ($avgDetail as $kt => $fields) {
                    foreach ($fields as $fid => $d) {
                        $finalAvgDetail[$kt][$fid] = $d['count'] > 0 ? $d['sum'] / $d['count'] : 0;
                    }
                }

                $defectSource = $scorings->first(function ($s) { return $s->edited_by_grand_juri; })
                    ?: $scorings->sortByDesc('updated_at')->first();
                $mergedDefect = [
                    'raw_head_penalty'    => $defectSource ? ($defectSource->raw_head_penalty    ?: ['0']) : ['0'],
                    'raw_face_penalty'    => $defectSource ? ($defectSource->raw_face_penalty    ?: ['0']) : ['0'],
                    'raw_body_penalty'    => $defectSource ? ($defectSource->raw_body_penalty    ?: ['0']) : ['0'],
                    'raw_finnage_penalty' => $defectSource ? ($defectSource->raw_finnage_penalty ?: ['0']) : ['0'],
                ];

                $allItems[] = [
                    'ikan_id'             => $pi->id,
                    'total_point'         => (float) PointCalculator::hitungPoint($pi->kategori, $finalAvgDetail, $mergedDefect),
                    'total_bonus'         => (int) $pi->bonusPoints->sum('points'),
                    'component_subtotals' => $this->buildComponentSubtotalsFromBreakdown($pi->kategori, $finalAvgDetail, $mergedDefect),
                ];
            }

            $ranked = PointCalculator::hitungRankPoints($allItems, 'total_point');
            $publishedIds = collect($publishedIkans)->pluck('id')->all();

            foreach ($ranked as $idx => $r) {
                if (!in_array($r['ikan_id'], $publishedIds)) continue;
                $ikan = $finalIkans->firstWhere('id', $r['ikan_id']);
                if (!$ikan) continue;

                $groupLabel = $ikan->kategori;
                if (!in_array($ikan->kategori, \App\Helpers\Taxonomy::noKelasNames()) && $ikan->kelas) {
                    $groupLabel .= ' - Kelas ' . $ikan->kelas;
                }
                $bonusTotal = (int) $ikan->bonusPoints->sum('points');
                $rankPoint  = (int) ($r['rank_point'] ?? 0);

                $row = [
                    'ikan_id'             => $ikan->id,
                    'nama_peserta'        => $this->cleanExcelDisplayValue($ikan->nama_peserta, ''),
                    'jenis_keanggotaan'   => $this->cleanExcelDisplayValue($ikan->jenis_keanggotaan, '-'),
                    'asal_label'          => $this->cleanExcelDisplayValue($ikan->detail_anggota, ''),
                    'detail_anggota'      => $this->cleanExcelDisplayValue($ikan->detail_anggota, ''),
                    'kategori'            => $ikan->kategori,
                    'kelas'               => $ikan->kelas ?? '-',
                    'group_key'           => $ikan->kategori . '|' . ($ikan->kelas ?? '-'),
                    'group_label'         => $groupLabel,
                    'point'               => round((float)($r['total_point'] ?? 0), 2),
                    'rank_point'          => $rankPoint,
                    'position'            => $idx + 1,
                    'nomor_tank'          => $ikan->nomor_tank,
                    'total_bonus'         => $bonusTotal,
                    'final_rank_point'    => $rankPoint + $bonusTotal,
                    'bonus_list'          => $ikan->bonusPoints->pluck('bonus_type')->toArray(),
                    'is_mvp'              => (bool) $ikan->is_mvp,
                    'is_team_champion'    => (bool) $ikan->is_team_champion,
                    'component_subtotals' => $r['component_subtotals'] ?? [
                        'overall' => ['label' => 'Overall', 'value' => 0], 'head' => ['label' => 'Head', 'value' => 0],
                        'face' => ['label' => 'Face', 'value' => 0], 'body' => ['label' => 'Body', 'value' => 0],
                        'marking' => ['label' => 'Marking', 'value' => 0], 'pearl' => ['label' => 'Pearl', 'value' => 0],
                        'color' => ['label' => 'Color', 'value' => 0], 'finnage' => ['label' => 'Finnage', 'value' => 0],
                    ],
                ];
                $results[] = $row;
                $rankByIkan[$ikan->id] = $row;
            }
        }

        usort($results, function ($a, $b) {
            $c = strcmp($a['group_label'], $b['group_label']);
            return $c !== 0 ? $c : ($a['position'] <=> $b['position']);
        });

        // ── MVP global ──
        $mvp = [];
        if ($mvpFeatureEnabled) {
            foreach ($finalIkans as $ikan) {
                if (
                    $ikan->is_mvp &&
                    optional($ikan->peserta)->is_mvp_submitted &&
                    isset($rankByIkan[$ikan->id])
                ) {
                    $mvp[] = $rankByIkan[$ikan->id];
                }
            }
            usort($mvp, function ($a, $b) { return $b['final_rank_point'] <=> $a['final_rank_point']; });
        }

        // ── Team Champion global (agregasi per team) ──
        // ── Team Champion global (agregasi per Team/Kota) ──
        // Jumlah ikan = semua ikan Team Champion yang SUDAH DIKIRIM peserta.
        // Total rank point = hanya ikan yang telah FINAL dan masuk ranking.
        $teamChampion = [];

        if ($tcFeatureEnabled) {
            $noKelasKategori = \App\Helpers\Taxonomy::noKelasNames();

            /*
            * Jangan memakai $finalIkans untuk jumlah Team Champion.
            * $finalIkans hanya berisi ikan yang sudah terkunci/final.
            *
            * Query ini mengambil semua ikan yang dipilih Team Champion
            * dari peserta yang benar-benar sudah menekan "Kirim Team Champion".
            */
            $selectedTeamChampionIkans = Ikan::query()
                ->where('is_team_champion', true)
                ->whereHas('peserta', function ($q) {
                    $q->where('is_team_champion_submitted', true);
                })
                ->with('peserta')
                ->orderBy('detail_anggota')
                ->orderBy('nomor_tank')
                ->get();

            $byTeam = [];

            foreach ($selectedTeamChampionIkans as $ikan) {
                $team = trim(
                    $this->cleanExcelDisplayValue($ikan->detail_anggota, '')
                ) ?: '(Tanpa Team)';

                if (!isset($byTeam[$team])) {
                    $byTeam[$team] = [
                        'detail_anggota'   => $team,
                        'ikans'             => [],
                        'total_rank_point'  => 0,

                        // Semua ikan Team Champion yang sudah dikirim.
                        'total_ikan'        => 0,

                        // Hanya ikan yang sudah final / memiliki rank point.
                        'total_ikan_final'  => 0,
                    ];
                }

                $groupLabel = $ikan->kategori;

                if (
                    !in_array($ikan->kategori, $noKelasKategori, true) &&
                    !empty($ikan->kelas)
                ) {
                    $groupLabel .= ' - Kelas ' . $ikan->kelas;
                }

                /*
                * Jika ikan sudah final, rank info tersedia di $rankByIkan.
                * Jika belum final, tetap dikirim ke UI dengan status Belum final.
                */
                $rankInfo = $rankByIkan[$ikan->id] ?? null;

                $item = [
                    'ikan_id'          => $ikan->id,
                    'nama_peserta'     => $this->cleanExcelDisplayValue($ikan->nama_peserta, ''),
                    'detail_anggota'   => $team,
                    'kategori'         => $ikan->kategori,
                    'kelas'            => $ikan->kelas ?? '-',
                    'group_key'        => $ikan->kategori . '|' . ($ikan->kelas ?? '-'),
                    'group_label'      => $groupLabel,
                    'nomor_tank'       => $ikan->nomor_tank,
                    'is_final'         => false,
                    'rank_point'       => 0,
                    'final_rank_point' => 0,
                    'status'           => 'Belum final',
                ];

                if ($rankInfo) {
                    $item = array_merge($item, $rankInfo, [
                        'is_final' => true,
                        'status'   => 'Final',
                    ]);

                    $byTeam[$team]['total_rank_point'] +=
                        (int) ($rankInfo['final_rank_point'] ?? 0);

                    $byTeam[$team]['total_ikan_final']++;
                }

                // Selalu bertambah, meskipun ikan belum final.
                $byTeam[$team]['total_ikan']++;

                $byTeam[$team]['ikans'][] = $item;
            }

            /*
            * Ikan final ditampilkan lebih dahulu.
            * Sisanya tetap terlihat di bawah dengan status Belum final.
            */
            foreach ($byTeam as &$teamData) {
                usort($teamData['ikans'], function ($a, $b) {
                    $aFinal = !empty($a['is_final']);
                    $bFinal = !empty($b['is_final']);

                    if ($aFinal !== $bFinal) {
                        return $aFinal ? -1 : 1;
                    }

                    $pointCompare =
                        (float) ($b['final_rank_point'] ?? 0)
                        <=>
                        (float) ($a['final_rank_point'] ?? 0);

                    if ($pointCompare !== 0) {
                        return $pointCompare;
                    }

                    return (int) ($a['nomor_tank'] ?? 0)
                        <=>
                        (int) ($b['nomor_tank'] ?? 0);
                });
            }
            unset($teamData);

            $teamChampion = array_values($byTeam);

            usort($teamChampion, function ($a, $b) {
                $pointCompare =
                    (int) ($b['total_rank_point'] ?? 0)
                    <=>
                    (int) ($a['total_rank_point'] ?? 0);

                if ($pointCompare !== 0) {
                    return $pointCompare;
                }

                return strcasecmp(
                    (string) ($a['detail_anggota'] ?? ''),
                    (string) ($b['detail_anggota'] ?? '')
                );
            });

            foreach ($teamChampion as $index => $team) {
                $teamChampion[$index]['position'] = $index + 1;
            }
        }

        return response()->json([
            'published' => true,
            'results' => $results,
            'mvp' => $mvp,
            'team_champion' => $teamChampion,
            'mvp_feature_enabled' => $mvpFeatureEnabled,
            'team_champion_feature_enabled' => $tcFeatureEnabled,
        ]);
    }

    /* ═══════════════════════════════════════════
       HASIL NOMINASI UNTUK PESERTA
       Menampilkan SELURUH hasil nominasi (semua peserta):
       - approved → dikelompokkan per Kategori + Kelas (gaya sheet)
       - rejected → daftar tank + alasan penolakan
       Hanya tampil jika admin sudah "Kirim Hasil Nominasi".
       ═══════════════════════════════════════════ */
    public function getNominasiResults()
    {
        if (!Auth::id()) {
            return response()->json([
                'error'   => 'unauthenticated',
                'message' => 'Sesi telah berakhir. Silakan login kembali.',
            ], 401);
        }

        $published = (bool) (\DB::table('settings')->where('key', 'nominasi_published')->value('value') ?? false);

        if (!$published) {
            return response()->json([
                'published'      => false,
                'groups'         => [],
                'rejected'       => [],
                'total_approved' => 0,
                'total_rejected' => 0,
            ]);
        }

        $noKelas = \App\Helpers\Taxonomy::noKelasNames();

        $noms = \App\Models\Nominasi::whereIn('status', ['approved', 'rejected'])
            ->with('ikan')
            ->orderByDesc('reviewed_at')
            ->get();

        // Dedupe per ikan: APPROVED selalu menang atas rejected
        $byIkan = [];
        foreach ($noms as $n) {
            if (!$n->ikan) continue;
            $iid = $n->ikan_id;

            if (!isset($byIkan[$iid])) {
                $byIkan[$iid] = ['status' => $n->status, 'ikan' => $n->ikan, 'catatan' => $n->catatan];
            } elseif ($byIkan[$iid]['status'] !== 'approved' && $n->status === 'approved') {
                $byIkan[$iid]['status']  = 'approved';
                $byIkan[$iid]['catatan'] = null;
            }
        }

        $groupsMap = [];
        $rejected  = [];

        foreach ($byIkan as $iid => $row) {
            $ikan      = $row['ikan'];
            $kategori  = $this->cleanExcelDisplayValue($ikan->kategori, '-');
            $isNoKelas = in_array($kategori, $noKelas);
            $kelas     = $isNoKelas ? null : ($ikan->kelas ?: null);
            $tank      = $ikan->nomor_tank;

            if ($row['status'] === 'approved') {
                $key = $kategori . '|' . ($kelas ?? '');
                if (!isset($groupsMap[$key])) {
                    $label = $isNoKelas ? $kategori : trim($kategori . ($kelas ? ' Kelas ' . $kelas : ''));
                    $groupsMap[$key] = ['kategori' => $kategori, 'kelas' => $kelas, 'label' => $label, 'tanks' => []];
                }
                $groupsMap[$key]['tanks'][] = ['ikan_id' => (int) $iid, 'nomor_tank' => $tank];
            } else {
                $rejected[] = [
                    'ikan_id'    => (int) $iid,
                    'nomor_tank' => $tank,
                    'kategori'   => $kategori,
                    'kelas'      => $kelas,
                    'catatan'    => $row['catatan'] ?: null,
                ];
            }
        }

        $groups = array_values($groupsMap);
        usort($groups, function ($a, $b) { return strcmp($a['label'], $b['label']); });
        foreach ($groups as &$g) {
            usort($g['tanks'], function ($a, $b) {
                return (int) ($a['nomor_tank'] ?? 0) <=> (int) ($b['nomor_tank'] ?? 0);
            });
        }
        unset($g);

        usort($rejected, function ($a, $b) {
            return (int) ($a['nomor_tank'] ?? 0) <=> (int) ($b['nomor_tank'] ?? 0);
        });

        $totalApproved = 0;
        foreach ($groups as $g) { $totalApproved += count($g['tanks']); }

        return response()->json([
            'published'      => true,
            'groups'         => $groups,
            'rejected'       => $rejected,
            'total_approved' => $totalApproved,
            'total_rejected' => count($rejected),
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

public function serveFoto($fotoId)
    {
        if (!Auth::id()) {
            abort(403);
        }

        $foto = \App\Models\IkanFoto::find($fotoId);
        if (!$foto || !\Storage::disk('public')->exists($foto->path)) {
            abort(404);
        }

        $mime = \Storage::disk('public')->mimeType($foto->path) ?: 'image/jpeg';

        return response(\Storage::disk('public')->get($foto->path), 200)
            ->header('Content-Type', $mime)
            ->header('Cache-Control', 'private, max-age=300');
    }

    public function ikanFotoInfo($id)
    {
        $ikan = Ikan::where('id', $id)
            ->whereHas('peserta', fn($q) => $q->where('user_id', Auth::id()))
            ->with('fotos')
            ->first();

        if (!$ikan) {
            return response()->json(['success' => false, 'message' => 'Ikan tidak ditemukan.'], 404);
        }

        $maxBytes  = 3 * 1024 * 1024;
        $usedBytes = (int) $ikan->fotos->sum('size');

        $fotos = $ikan->fotos->map(function ($f) {
            return [
                'id'   => $f->id,
                'url'  => route('foto.ikan', ['foto' => $f->id]) . '?v=' . ($f->updated_at ? $f->updated_at->timestamp : time()),
                'size' => (int) $f->size,
            ];
        })->values();

        return response()->json([
            'success'         => true,
            'fotos'           => $fotos,
            'count'           => $fotos->count(),
            'has_foto'        => $fotos->count() > 0,
            'used_bytes'      => $usedBytes,
            'max_bytes'       => $maxBytes,
            'remaining_bytes' => max(0, $maxBytes - $usedBytes),
            'can_upload'      => $usedBytes < $maxBytes,
            'nama'            => $this->cleanExcelDisplayValue($ikan->nama_peserta, ''),
            'kategori'        => $ikan->kategori,
        ]);
    }

    public function uploadFotoIkan(Request $request)
    {
        $request->validate([
            'ikan_id' => 'required|exists:ikans,id',
            'foto'    => 'required|image|mimes:jpeg,jpg,png|max:3072',
        ], [
            'foto.image' => 'File harus berupa gambar.',
            'foto.mimes' => 'Format foto harus JPG atau PNG.',
            'foto.max'   => 'Ukuran foto maksimal 3MB.',
        ]);

        $ikan = Ikan::where('id', $request->ikan_id)
            ->whereHas('peserta', fn($q) => $q->where('user_id', Auth::id()))
            ->with('fotos')
            ->first();

        if (!$ikan) {
            return response()->json(['success' => false, 'message' => 'Ikan tidak ditemukan atau bukan milik Anda.'], 404);
        }

        $maxBytes  = 3 * 1024 * 1024;
        $usedBytes = (int) $ikan->fotos->sum('size');

        if ($usedBytes >= $maxBytes) {
            return response()->json(['success' => false, 'message' => 'Batas total 3MB foto untuk ikan ini sudah tercapai.'], 422);
        }

        [$path, $size] = $this->simpanFotoBaru($ikan, $request->file('foto'));

        if (($usedBytes + $size) > $maxBytes) {
            \Storage::disk('public')->delete($path);
            $sisaKb = round(($maxBytes - $usedBytes) / 1024);
            return response()->json([
                'success' => false,
                'message' => 'Foto melebihi sisa kuota. Sisa kuota Anda ± ' . $sisaKb . ' KB.',
            ], 422);
        }

        $foto = $ikan->fotos()->create(['path' => $path, 'size' => $size]);

        $usedAfter = $usedBytes + $size;

        return response()->json([
            'success'         => true,
            'message'         => 'Foto berhasil disimpan.',
            'foto'            => [
                'id'   => $foto->id,
                'url'  => route('foto.ikan', ['foto' => $foto->id]) . '?v=' . time(),
                'size' => $size,
            ],
            'used_bytes'      => $usedAfter,
            'max_bytes'       => $maxBytes,
            'remaining_bytes' => max(0, $maxBytes - $usedAfter),
            'can_upload'      => $usedAfter < $maxBytes,
        ]);
    }

    private function simpanFotoBaru(Ikan $ikan, $file): array
    {
        $ext  = strtolower($file->getClientOriginalExtension()) === 'png' ? 'png' : 'jpg';
        $name = 'ikan_' . $ikan->id . '_' . uniqid() . '.' . $ext;
        $relativePath = 'ikan-foto/' . $name;

        $stored = false;
        try {
            if (function_exists('imagecreatefromstring')) {
                $img = @imagecreatefromstring(file_get_contents($file->getRealPath()));
                if ($img !== false) {
                    $maxDim = 1600;
                    $w = imagesx($img);
                    $h = imagesy($img);
                    $scale = min(1, $maxDim / max($w, $h));

                    if ($scale < 1) {
                        $nw = (int) round($w * $scale);
                        $nh = (int) round($h * $scale);
                        $resized = imagecreatetruecolor($nw, $nh);
                        if ($ext === 'png') {
                            imagealphablending($resized, false);
                            imagesavealpha($resized, true);
                        }
                        imagecopyresampled($resized, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
                        imagedestroy($img);
                        $img = $resized;
                    }

                    ob_start();
                    if ($ext === 'png') {
                        imagepng($img, null, 6);
                    } else {
                        imagejpeg($img, null, 80);
                    }
                    $binary = ob_get_clean();
                    imagedestroy($img);

                    \Storage::disk('public')->put($relativePath, $binary);
                    $stored = true;
                }
            }
        } catch (\Throwable $e) {
            $stored = false;
        }

        if (!$stored) {
            $file->storeAs('ikan-foto', $name, 'public');
        }

        $size = (int) \Storage::disk('public')->size($relativePath);
        return [$relativePath, $size];
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
            'mvpFeatureEnabled' => $this->isFeatureEnabled('mvp_feature_enabled'),
            'teamChampionFeatureEnabled' => $this->isFeatureEnabled('team_champion_feature_enabled'),
        ]);
    }

    /* ═══════════════════════════════════════════
       PIAGAM HASIL JUARA (khusus milik peserta login)
       ═══════════════════════════════════════════ */
    public function getMyPiagam()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'unauthenticated'], 401);
        }

        // ★ Ikut gate "Kirim ke Semua Peserta": bila akses hasil belum dibuka, piagam TIDAK ditampilkan.
        if (!$this->isResultsGloballyPublished()) {
            return response()->json(['published' => false, 'piagams' => []]);
        }

        $peserta = Peserta::where('user_id', $user->id)->first();
        if (!$peserta) {
            return response()->json(['piagams' => []]);
        }

        $piagams = \App\Models\Piagam::where('peserta_id', $peserta->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($pg) {
                $isPdf = stripos((string) $pg->mime, 'pdf') !== false
                    || strtolower(pathinfo($pg->path, PATHINFO_EXTENSION)) === 'pdf';

                return [
                    'id'            => $pg->id,
                    'judul'         => $pg->judul,
                    'original_name' => $pg->original_name,
                    'kategori'      => $pg->kategori,
                    'kelas'         => $pg->kelas,
                    'is_pdf'        => $isPdf,
                    'view_url'      => route('piagam.serve', $pg->id),
                    'download_url'  => route('piagam.serve', $pg->id) . '?download=1',
                    'created_at'    => optional($pg->created_at)->format('d M Y H:i'),
                ];
            });

        return response()->json(['piagams' => $piagams]);
    }

    public function servePiagam($id)
    {
        $user = Auth::user();
        if (!$user) abort(403);

        $pg = \App\Models\Piagam::find($id);
        if (!$pg || !\Storage::disk('public')->exists($pg->path)) {
            abort(404);
        }

        // Hanya pemilik (peserta) atau admin yang boleh mengakses
        $isAdmin = ($user->role ?? null) === 'admin';
        $peserta = Peserta::find($pg->peserta_id);
        $isOwner = $peserta && (int) $peserta->user_id === (int) $user->id;

        if (!$isAdmin && !$isOwner) {
            abort(403);
        }

        // ★ Owner hanya boleh mengakses bila akses hasil sudah dibuka (admin tetap bebas).
        if (!$isAdmin && !$this->isResultsGloballyPublished()) {
            abort(403);
        }

        $mime  = \Storage::disk('public')->mimeType($pg->path) ?: 'application/octet-stream';
        $bytes = \Storage::disk('public')->get($pg->path);

        $filename = $pg->original_name
            ?: ('piagam-' . $pg->id . '.' . pathinfo($pg->path, PATHINFO_EXTENSION));
        $filename = str_replace('"', '', $filename);

        $mode = request()->boolean('download') ? 'attachment' : 'inline';

        return response($bytes, 200)
            ->header('Content-Type', $mime)
            ->header('Content-Disposition', $mode . '; filename="' . $filename . '"')
            ->header('Cache-Control', 'private, max-age=300');
    }

}