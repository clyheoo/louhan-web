<?php

namespace App\Jobs;

use App\Exports\AdminExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;

class GenerateAdminExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 1800; // izinkan worker berjalan sampai 30 menit
    public $tries   = 1;

    public function __construct(
        public string $token,
        public string $sheets = 'all'
    ) {}

    public function handle(): void
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '1024M');

        Cache::put("export:{$this->token}", ['status' => 'processing'], now()->addHours(2));

        $path = "exports/{$this->token}.xlsx";
        Excel::store(new AdminExport($this->sheets), $path, 'local');

        Cache::put("export:{$this->token}", [
            'status' => 'ready',
            'path'   => $path,
            'name'   => 'LCI_Admin_Semua_Data_' . now()->format('Y-m-d_His') . '.xlsx',
        ], now()->addHours(2));
    }

    public function failed(\Throwable $e): void
    {
        Cache::put("export:{$this->token}", [
            'status'  => 'failed',
            'message' => $e->getMessage(),
        ], now()->addHours(2));
    }
}