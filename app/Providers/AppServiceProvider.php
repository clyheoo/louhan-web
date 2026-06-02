<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Ikan;
use App\Models\Nominasi;
use App\Models\User;
use App\Models\Scoring;
use App\Services\SheetsSyncService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Auto sync ketika Nomor Tank peserta diisi/diubah
        Ikan::updated(function (Ikan $ikan) {
            if ($ikan->isDirty('nomor_tank') && !empty($ikan->nomor_tank)) {
                try {
                    app(SheetsSyncService::class)->syncSemuaPeserta();
                } catch (\Exception $e) {
                    \Log::error('Auto Sync Peserta Error: ' . $e->getMessage());
                }
            }
        });

        // Auto sync ketika Nominasi di-approve
        Nominasi::updated(function (Nominasi $nominasi) {
            if ($nominasi->isDirty('status') && $nominasi->status === 'approved') {
                try {
                    $sync = app(SheetsSyncService::class);
                    $sync->syncSemuaNominasi();
                    $sync->syncSemuaPilNom();
                } catch (\Exception $e) {
                    \Log::error('Auto Sync Nominasi Error: ' . $e->getMessage());
                }
            }
        });

        // Auto sync ketika Ada Juri baru ditambahkan
        User::created(function (User $user) {
            if ($user->role === 'juri') {
                try {
                    app(SheetsSyncService::class)->syncNamaJuri();
                } catch (\Exception $e) {
                    \Log::error('Auto Sync Juri Error: ' . $e->getMessage());
                }
            }
        });

        // Auto sync ketika Juri dihapus
        User::deleted(function (User $user) {
            if ($user->role === 'juri') {
                try {
                    app(SheetsSyncService::class)->syncNamaJuri();
                } catch (\Exception $e) {
                    \Log::error('Auto Sync Juri Error: ' . $e->getMessage());
                }
            }
        });
    }
}
