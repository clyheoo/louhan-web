<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JuriController;
use App\Http\Controllers\GrandJuriController;
use App\Http\Controllers\AdminDashboardController;

require __DIR__.'/auth.php';

Route::get('/', function () {
    return redirect()->route('login');
})->middleware('guest');

/* ═══════════════════════════════════════════
   DASHBOARD UTAMA (Dibedakan di Controller)
   ═══════════════════════════════════════════ */
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('auth')->name('dashboard');

/* ═══════════════════════════════════════════
   API REGISTRASI & UNDIAN (DashboardController)
   ═══════════════════════════════════════════ */
Route::post('/api/registrasi-peserta', [DashboardController::class, 'storePeserta'])->middleware('auth')->name('store.registrasi');
Route::post('/api/tambah-ikan', [DashboardController::class, 'storeIkan'])->middleware('auth')->name('store.ikan');
Route::get('/api/peserta-belum-tidak', [DashboardController::class, 'getPesertaBelumDapatTank'])->middleware('auth')->name('api.peserta.belum.tank');
Route::post('/api/acak-nomor-tank-admin', [DashboardController::class, 'acakNomorTankAdmin'])->middleware('auth')->name('api.acak.tank.admin');
Route::post('/api/acak-nomor-tank-user', [DashboardController::class, 'acakNomorTankUser'])->middleware('auth')->name('api.acak.tank.user');

/* ═══════════════════════════════════════════
   KELOLA USER (Hanya Admin)
   ═══════════════════════════════════════════ */
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/api/admin/list-pesertas', [AdminDashboardController::class, 'getListPesertas'])->name('admin.list.pesertas');
    Route::post('/api/admin/tambah-ikan', [AdminDashboardController::class, 'storeIkanAdmin'])->name('admin.tambah.ikan');
    Route::get('/api/list-users', [DashboardController::class, 'getListUsers'])->name('api.list.users');
    Route::post('/api/update-password', [DashboardController::class, 'updatePasswordUser'])->name('api.update.password');
    Route::post('/api/toggle-role', [DashboardController::class, 'toggleRoleUser'])->name('api.toggle.role');
});

/* ═══════════════════════════════════════════
   JURI
   ═══════════════════════════════════════════ */
Route::get('/api/juri/data', [JuriController::class, 'getJuriData'])->middleware('auth');
Route::post('/api/juri/simpan-nilai', [JuriController::class, 'simpanNilai'])->middleware('auth');

/* ═══════════════════════════════════════════
   GRAND JURI
   ═══════════════════════════════════════════ */
Route::middleware('auth')->group(function () {
    Route::get('/grand-juri', [GrandJuriController::class, 'index'])->name('grand-juri.index');
    Route::get('/api/grand-juri/stats', [GrandJuriController::class, 'getStats']);
    Route::get('/api/grand-juri/peserta', [GrandJuriController::class, 'getPeserta']);
    Route::get('/api/grand-juri/juri-summary', [GrandJuriController::class, 'getJuriSummary']);
    Route::post('/api/grand-juri/edit-nilai', [GrandJuriController::class, 'editNilai']);
});

/* ═══════════════════════════════════════════
   ADMIN DASHBOARD (Statistik, Grafik, Data)
   ═══════════════════════════════════════════ */
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/api/admin/dashboard-stats', [AdminDashboardController::class, 'getDashboardStats']);
    Route::get('/api/admin/scoring-data', [AdminDashboardController::class, 'getScoringData']);
    Route::post('/api/admin/create-user', [AdminDashboardController::class, 'createUser']);
    Route::post('/api/admin/change-role', [AdminDashboardController::class, 'changeRole']);
    Route::post('/api/admin/delete-user', [AdminDashboardController::class, 'deleteUser']);
});