<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JuriController;
use App\Http\Controllers\GrandJuriController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\SheetsSyncController;

require __DIR__.'/auth.php';

Route::get('/', function () {
    return redirect()->route('login');
})->middleware('guest');

/* ═══════════════════════════════════════════
   DASHBOARD UTAMA
   ═══════════════════════════════════════════ */
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('auth')->name('dashboard');
Route::get('/hasil-juara', [DashboardController::class, 'hasilJuara'])->middleware('auth')->name('hasil-juara');

/* ═══════════════════════════════════════════
   API REGISTRASI & UNDIAN
   ═══════════════════════════════════════════ */
Route::middleware('auth')->group(function () {
    Route::post('/api/registrasi-peserta', [DashboardController::class, 'storePeserta'])->name('store.registrasi');
    Route::post('/api/tambah-ikan', [DashboardController::class, 'storeIkan'])->name('store.ikan');
    Route::get('/api/peserta-belum-tidak', [DashboardController::class, 'getPesertaBelumDapatTank'])->name('api.peserta.belum.tank');
    Route::post('/api/acak-nomor-tank-admin', [DashboardController::class, 'acakNomorTankAdmin'])->name('api.acak.tank.admin');
    Route::post('/api/acak-nomor-tank-user', [DashboardController::class, 'acakNomorTankUser'])->name('api.acak.tank.user');
    Route::get('/api/user/my-ikans', [DashboardController::class, 'getMyIkans']);
    Route::get('/api/user/nominasi-results', [DashboardController::class, 'getNominasiResults']); // ★ hasil nominasi utk peserta
    Route::post('/api/toggle-team-champion-ikan', [DashboardController::class, 'toggleTeamChampionIkan'])->name('api.toggle.team_champion');
    Route::post('/api/submit-team-champion-ikan', [DashboardController::class, 'submitTeamChampionIkan'])->name('api.submit.team_champion');

    Route::post('/api/toggle-mvp-ikan', [DashboardController::class, 'toggleMvpIkan'])->name('api.toggle.mvp');
    Route::post('/api/submit-mvp-ikan', [DashboardController::class, 'submitMvpIkan'])->name('api.submit.mvp');
});

/* ═══════════════════════════════════════════
   JURI
   ═══════════════════════════════════════════ */
Route::middleware('auth')->group(function () {
    Route::get('/api/juri/data', [JuriController::class, 'getJuriData']);
    Route::post('/api/juri/simpan-nilai', [JuriController::class, 'simpanNilai']);
    Route::post('/api/juri/kirim-ke-grand', [JuriController::class, 'kirimKeGrandJuri']);
    Route::get('/api/juri/nominasi-status', [JuriController::class, 'getNominasiStatus']);
    Route::get('/api/juri/tanks-nominasi', [JuriController::class, 'getTanksForNominasi']);
    Route::post('/api/juri/submit-nominasi', [JuriController::class, 'submitNominasi']);
    Route::post('/api/juri/cancel-nominasi', [JuriController::class, 'cancelNominasi']);
});

Route::get('/juri', function () {
    return view('dashboard.juri');
})->middleware('auth')->name('juri.index');

/* ═══════════════════════════════════════════
   GRAND JURI ( semua route di sini, TANPA duplikat )
   ═══════════════════════════════════════════ */
Route::middleware('auth')->group(function () {
    Route::get('/grand-juri', [GrandJuriController::class, 'index'])->name('grand-juri.index');
    Route::get('/api/grand-juri/stats', [GrandJuriController::class, 'getStats']);
    Route::get('/api/grand-juri/peserta', [GrandJuriController::class, 'getPeserta']);
    Route::get('/api/grand-juri/juri-summary', [GrandJuriController::class, 'getJuriSummary']);
    Route::post('/api/grand-juri/edit-nilai', [GrandJuriController::class, 'editNilai']);
    Route::get('/api/grand-juri/juri-peserta', [GrandJuriController::class, 'getJuriPeserta']);
    Route::get('/api/grand-juri/rincian-detail', [GrandJuriController::class, 'getRincianDetail']);
    Route::get('/api/grand-juri/plot-status', [GrandJuriController::class, 'getPlotStatus']);
    Route::post('/api/grand-juri/kunci-nilai', [GrandJuriController::class, 'kunciNilai']);
    Route::get('/api/grand-juri/mvp-ikan', [GrandJuriController::class, 'getMvpIkan']);
    Route::post('/api/grand-juri/add-bonus', [GrandJuriController::class, 'addBonus']);
    Route::post('/api/grand-juri/remove-bonus', [GrandJuriController::class, 'removeBonus']);
    Route::get('/api/grand-juri/point-ranking', [GrandJuriController::class, 'getPointRanking']);
    Route::get('/api/grand-juri/export', [GrandJuriController::class, 'exportExcel']);
    Route::get('/api/admin/export', [AdminDashboardController::class, 'exportExcel']);
    Route::get('/api/scoring-point-configs', [GrandJuriController::class, 'getPointConfigs']);
});

/* ═══════════════════════════════════════════
   GRAND JURI — NOMINASI REVIEW
   ═══════════════════════════════════════════ */
Route::middleware('auth')->group(function () {
    Route::get('/grand-juri/nominasi', [GrandJuriController::class, 'nominasiIndex'])->name('grand-juri.nominasi');
    Route::get('/api/grand-juri/nominasi', [GrandJuriController::class, 'getNominasi']);
    Route::post('/api/grand-juri/nominasi-review', [GrandJuriController::class, 'reviewNominasi']);
    Route::get('/api/grand-juri/nominasi-history', [GrandJuriController::class, 'getNominasiHistory']);
    Route::get('/api/grand-juri/late-ikan', [GrandJuriController::class, 'getLateIkan']);
    Route::post('/api/grand-juri/late-ikan-review', [GrandJuriController::class, 'reviewLateIkan']);
    Route::post('/api/grand-juri/kunci-semua', [GrandJuriController::class, 'kunciSemua']);
});

Route::middleware('auth')->group(function () {
    Route::post('/api/admin/add-bonus', [AdminDashboardController::class, 'addBonus']);
    Route::post('/api/admin/remove-bonus', [AdminDashboardController::class, 'removeBonus']);
});

/* ═══════════════════════════════════════════
   ADMIN ONLY
   ═══════════════════════════════════════════ */
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/api/admin/dashboard-stats', [AdminDashboardController::class, 'getDashboardStats']);
    Route::post('/api/admin/toggle-scoring-lock', [AdminDashboardController::class, 'toggleScoringLock']);
    Route::get('/api/admin/scoring-status', [AdminDashboardController::class, 'getScoringStatus']);
    Route::get('/api/admin/scoring-data', [AdminDashboardController::class, 'getScoringData']);
    Route::get('/api/admin/available-tank-block', [AdminDashboardController::class, 'getAvailableTankBlock']);
    Route::post('/api/admin/edit-kategori-kelas', [AdminDashboardController::class, 'editKategoriKelas']);
    Route::post('/api/admin/create-user', [AdminDashboardController::class, 'createUser']);
    Route::post('/api/admin/change-role', [AdminDashboardController::class, 'changeRole']);
    Route::post('/api/admin/delete-user', [AdminDashboardController::class, 'deleteUser']);
    Route::post('/api/admin/register-peserta-ikan', [AdminDashboardController::class, 'registerPesertaIkan']);
    Route::post('/api/admin/delete-ikan', [AdminDashboardController::class, 'deleteIkan']);
    Route::post('/api/admin/bulk-delete-ikan', [AdminDashboardController::class, 'bulkDeleteIkan']);
    Route::post('/api/admin/reset-participants', [AdminDashboardController::class, 'resetAllParticipants']);
    Route::get('/api/admin/get-peserta-by-user', [AdminDashboardController::class, 'getPesertaByUser']);
    Route::get('/api/admin/user-peserta-detail', [AdminDashboardController::class, 'getUserPesertaDetail']);
    Route::post('/api/admin/update-peserta-data', [AdminDashboardController::class, 'updatePesertaData']);
    Route::get('/api/list-users', [DashboardController::class, 'getListUsers'])->name('api.list.users');
    Route::post('/api/update-password', [DashboardController::class, 'updatePasswordUser'])->name('api.update.password');
    Route::post('/api/toggle-role', [DashboardController::class, 'toggleRoleUser'])->name('api.toggle.role');
    Route::get('/api/tank-range-global', [AdminDashboardController::class, 'getTankRangeGlobal']);
    Route::post('/api/admin/tank-range-global', [AdminDashboardController::class, 'setTankRangeGlobal']);
    Route::get('/api/admin/team-champion-ikan', [AdminDashboardController::class, 'getTeamChampionIkan']);
    Route::post('/api/admin/toggle-team-champion-registration', [AdminDashboardController::class, 'toggleTeamChampionRegistration']);
    Route::get('/api/admin/team-champion-status', [AdminDashboardController::class, 'getTeamChampionStatus']);
    Route::post('/api/admin/team-champion-registration-max', [AdminDashboardController::class, 'setTeamChampionRegistrationMax']);
    Route::get('/api/admin/team-champion-submitted-peserta', [AdminDashboardController::class, 'getTeamChampionSubmittedPeserta']);
    Route::post('/api/admin/unlock-team-champion-peserta', [AdminDashboardController::class, 'unlockTeamChampionPeserta']);
    Route::post('/api/admin/delete-team-champion-ikan', [AdminDashboardController::class, 'deleteTeamChampionIkan']);
    Route::get('/api/admin/mvp-ikan', [AdminDashboardController::class, 'getMvpIkan']);
    Route::get('/api/admin/mvp-ikan-data', [AdminDashboardController::class, 'getMvpIkanData']);
    Route::post('/api/admin/toggle-mvp-registration', [AdminDashboardController::class, 'toggleMvpRegistration']);
    Route::get('/api/admin/mvp-status', [AdminDashboardController::class, 'getMvpStatus']);
    Route::post('/api/admin/mvp-registration-max', [AdminDashboardController::class, 'setMvpRegistrationMax']);
    Route::post('/api/admin/toggle-undian-registration', [AdminDashboardController::class, 'toggleUndianRegistration']);
    Route::get('/api/admin/undian-status', [AdminDashboardController::class, 'getUndianStatus']);
    Route::post('/api/admin/delete-mvp-ikan', [AdminDashboardController::class, 'deleteMvpIkan']);
    Route::get('/api/admin/mvp-submitted-peserta', [AdminDashboardController::class, 'getMvpSubmittedPeserta']);
    Route::post('/api/admin/unlock-mvp-peserta', [AdminDashboardController::class, 'unlockMvpPeserta']);
    Route::post('/api/admin/import-excel', [AdminDashboardController::class, 'importExcel']);
    Route::get('/api/admin/import-template', [AdminDashboardController::class, 'downloadImportTemplate']);
    Route::get('/api/admin/stat-detail', [AdminDashboardController::class, 'getStatDetail']);
    Route::get('/api/admin/debug-user-ikans', [AdminDashboardController::class, 'debugUserIkans']);
    Route::get('/api/admin/point-ranking', [AdminDashboardController::class, 'getPointRanking']);
    Route::post('/api/admin/submit-nominasi', [AdminDashboardController::class, 'submitAdminNominasi']);
    Route::post('/api/admin/update-nominasi-defect', [AdminDashboardController::class, 'updateNominasiDefect']);
    Route::get('/api/admin/tanks-nominasi-tersedia', [AdminDashboardController::class, 'getTanksTersediaUntukNominasi']);
    Route::get('/api/admin/jumbo-scored-ikans', [AdminDashboardController::class, 'getJumboScoredIkans']);
    Route::post('/api/admin/calc-jumbo-point', [AdminDashboardController::class, 'calcJumboPoint']);
    Route::post('/api/admin/reset-rejected-nominasi', [AdminDashboardController::class, 'resetRejectedNominasi']);
    Route::post('/api/admin/hapus-nominasi', [AdminDashboardController::class, 'hapusNominasi']);
    Route::get('/api/admin/results-status', [AdminDashboardController::class, 'getResultsStatus']);    
    Route::post('/api/admin/publish-results-all', [AdminDashboardController::class, 'publishResultsAll']);
    Route::post('/api/admin/publish-result-user', [AdminDashboardController::class, 'publishResultUser']);
    Route::post('/api/admin/unpublish-result-user', [AdminDashboardController::class, 'unpublishResultUser']);
    Route::post('/api/admin/buka-semua-kunci', [AdminDashboardController::class, 'bukaSemuaKunci']);
    Route::post('/api/admin/unpublish-results-all', [AdminDashboardController::class, 'unpublishResultsAll']);

    // ★ NOMINASI — kirim/cabut hasil nominasi ke semua peserta
    Route::post('/api/admin/toggle-nominasi-publish', [AdminDashboardController::class, 'toggleNominasiPublish']);
    Route::get('/api/admin/nominasi-publish-status', [AdminDashboardController::class, 'getNominasiPublishStatus']);

    // ★ KELOLA JURI — penugasan kategori & kelas per juri
    Route::get('/api/admin/juri-assignments', [AdminDashboardController::class, 'getJuriAssignments']);
    Route::post('/api/admin/juri-assignments', [AdminDashboardController::class, 'saveJuriAssignments']);
    Route::post('/api/admin/reset-juri-assignments', [AdminDashboardController::class, 'resetAllJuriAssignments']);
    Route::get('/api/admin/export-async', [AdminDashboardController::class, 'exportAsyncStart']);
    Route::get('/api/admin/export-status/{token}', [AdminDashboardController::class, 'exportAsyncStatus']);
    Route::get('/api/admin/export-download/{token}', [AdminDashboardController::class, 'exportAsyncDownload']);
});

/* ═══════════════════════════════════════════
   GLOBAL SETTING
   ═══════════════════════════════════════════ */
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/api/tank-range', [AdminDashboardController::class, 'getTankRange']);
    Route::post('/api/admin/tank-range', [AdminDashboardController::class, 'setTankRange']);
    Route::post('/api/admin/reset-tank', [AdminDashboardController::class, 'resetTankNumbers']);
});

/* ═══════════════════════════════════════════
   GOOGLE SHEETS SYNC
   ═══════════════════════════════════════════ */
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/api/sheets/test', [SheetsSyncController::class, 'testConnection']);
    Route::get('/api/sheets/sync-all', [SheetsSyncController::class, 'syncAll']);
    Route::get('/api/sheets/sync-peserta', [SheetsSyncController::class, 'syncPeserta']);
    Route::get('/api/sheets/sync-nominasi', [SheetsSyncController::class, 'syncNominasi']);
    Route::get('/api/sheets/sync-pil-nom', [SheetsSyncController::class, 'syncPilNom']);
    Route::get('/api/sheets/sync-ploting-tank', [SheetsSyncController::class, 'syncPlotingTank']);
    Route::get('/api/sheets/sync-nama-juri', [SheetsSyncController::class, 'syncNamaJuri']);
    Route::get('/api/sheets/sync-hasil-juri', [SheetsSyncController::class, 'syncHasilJuri']);
    Route::get('/api/sheets/sync-hasil-nominasi', [SheetsSyncController::class, 'syncHasilNominasi']);
    Route::get('/api/sheets/sync-nominasi-fix', [SheetsSyncController::class, 'syncNominasiFix']);
    Route::get('/api/sheets/sync-nilai-juri', [SheetsSyncController::class, 'syncNilaiJuri']);
    Route::get('/api/sheets/sync-cnt', [SheetsSyncController::class, 'syncCnt']);
    Route::get('/api/sheets/sync-mvp', [SheetsSyncController::class, 'syncMvp']);
});