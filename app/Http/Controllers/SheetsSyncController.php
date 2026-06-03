<?php

namespace App\Http\Controllers;

use App\Services\GoogleSheetsService;
use App\Services\SheetsSyncService;

class SheetsSyncController extends Controller
{
    protected $sheets;
    protected $sync;

    public function __construct(GoogleSheetsService $sheets, SheetsSyncService $sync)
    {
        $this->sheets = $sheets;
        $this->sync = $sync;
    }

    public function testConnection()
    {
        if (!$this->sheets->isReady()) {
            return response()->json([
                'success' => false,
                'message' => 'Google Sheets belum dikonfigurasi. Cek .env dan credentials.json',
            ], 500);
        }

        $data = $this->sheets->read('PESERTA', 'A1:G1');

        return response()->json([
            'success'  => true,
            'message'  => 'Koneksi berhasil!',
            'header'   => $data[0] ?? [],
            'sheet_id' => config('google-sheets.spreadsheet_id'),
        ]);
    }

    public function syncAll()
    {
        if (!$this->sync->isReady()) {
            return response()->json(['success' => false, 'message' => 'Google Sheets belum siap.'], 500);
        }
        $results = $this->sync->syncSemua();
        return response()->json(['success' => true, 'message' => 'Sync semua sheet selesai.', 'results' => $results]);
    }

    public function syncPeserta()
    {
        $count = $this->sync->syncSemuaPeserta();
        return response()->json(['success' => true, 'message' => "Sync {$count} peserta.", 'count' => $count]);
    }

    public function syncNominasi()
    {
        $count = $this->sync->syncSemuaNominasi();
        return response()->json(['success' => true, 'message' => "Sync {$count} nominasi.", 'count' => $count]);
    }

    public function syncPilNom()
    {
        $count = $this->sync->syncSemuaPilNom();
        return response()->json(['success' => true, 'message' => "Sync {$count} PIL NOM.", 'count' => $count]);
    }

    public function syncPlotingTank()
    {
        $count = $this->sync->syncPlotingTank();
        return response()->json(['success' => true, 'message' => "Sync {$count} range.", 'count' => $count]);
    }

        public function syncNamaJuri()
    {
        $count = $this->sync->syncNamaJuri();
        return response()->json(['success' => true, 'message' => "Sync {$count} nama juri.", 'count' => $count]);
    }

    public function syncHasilNominasi()
    {
        $count = $this->sync->syncHasilNominasi();
        return response()->json(['success' => true, 'message' => "Sync {$count} hasil nominasi.", 'count' => $count]);
    }

    public function syncHasilJuri()
    {
        $count = $this->sync->syncHasilJuri();
        return response()->json(['success' => true, 'message' => "Sync {$count} hasil penilaian juri.", 'count' => $count]);
    }

    public function syncNominasiFix()
    {
        $count = $this->sync->syncNominasiFix();
        return response()->json(['success' => true, 'message' => "Sync {$count} nominasi fix.", 'count' => $count]);
    }

    public function syncNilaiJuri()
    {
        $count = $this->sync->syncNilaiJuri();
        return response()->json(['success' => true, 'message' => "Sync {$count} NILAI JURI.", 'count' => $count]);
    }

    public function syncCnt()
    {
        $count = $this->sync->syncCnt();
        return response()->json(['success' => true, 'message' => "Sync {$count} CNT.", 'count' => $count]);
    }

    public function syncMvp()
    {
        $count = $this->sync->syncMvp();
        return response()->json(['success' => true, 'message' => "Sync {$count} MVP.", 'count' => $count]);
    }
}