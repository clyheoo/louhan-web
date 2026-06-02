<?php

namespace App\Services;

use App\Models\Ikan;
use App\Models\Peserta;
use App\Models\Nominasi;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SheetsSyncService
{
    protected $sheets;
    protected $sheetNames;

    public function __construct(GoogleSheetsService $sheets)
    {
        $this->sheets = $sheets;
        $this->sheetNames = config('google-sheets.sheets', []);
    }

    public function isReady(): bool
    {
        return $this->sheets->isReady();
    }

    /* ═══════════════════════════════════════════
       SHEET: PESERTA
       A=Tanggal, B=Nama, C=Kategori, D=Kelas,
       E=Tim/Perorangan, F=Team, G=No Tank
       ═══════════════════════════════════════════ */

    public function tambahPeserta(Ikan $ikan)
    {
        $peserta = $ikan->peserta;
        if (!$peserta) return false;

        $row = [
            $ikan->created_at ? Carbon::parse($ikan->created_at)->format('m/d/Y H:i:s') : now()->format('m/d/Y H:i:s'),
            $peserta->nama_peserta ?? '',
            $ikan->kategori ?? '',
            $this->formatKelas($ikan->kategori, $ikan->kelas),
            ucfirst($peserta->jenis_keanggotaan ?? 'Team'),
            $peserta->detail_anggota ?? '',
            $ikan->nomor_tank ?? '',
        ];

        return $this->sheets->append($this->sheetNames['peserta'], $row);
    }

    public function syncSemuaPeserta()
    {
        $sheetName = $this->sheetNames['peserta'];
        $ikans = Ikan::with('peserta')
            ->whereNotNull('nomor_tank')
            ->orderBy('nomor_tank')
            ->get();

        $rows = $ikans->map(function ($ikan) {
            $peserta = $ikan->peserta;
            return [
                $ikan->created_at ? Carbon::parse($ikan->created_at)->format('m/d/Y H:i:s') : '',
                $peserta->nama_peserta ?? '',
                $ikan->kategori ?? '',
                $this->formatKelas($ikan->kategori, $ikan->kelas),
                ucfirst($peserta->jenis_keanggotaan ?? 'Team'),
                $peserta->detail_anggota ?? '',
                $ikan->nomor_tank ?? '',
            ];
        })->toArray();

        $this->sheets->clear($sheetName, 'A2:G2000');
        if (!empty($rows)) {
            $this->sheets->write($sheetName, 'A2', $rows);
        }
        return count($rows);
    }

    /* ═══════════════════════════════════════════
       SHEET: NOMINASI
       A=Tanggal, B=Nama Juri, C=No Tank,
       D=Kategori, E=Kelas
       ═══════════════════════════════════════════ */

    public function tambahNominasi(Nominasi $nominasi)
    {
        $juri = $nominasi->juri;
        $ikan = $nominasi->ikan;
        if (!$juri || !$ikan) return false;

        $row = [
            $nominasi->created_at ? Carbon::parse($nominasi->created_at)->format('m/d/Y H:i:s') : now()->format('m/d/Y H:i:s'),
            $juri->name ?? '',
            $ikan->nomor_tank ?? '',
            strtoupper($ikan->kategori ?? ''),
            $this->formatKelasNominasi($ikan->kategori, $ikan->kelas),
        ];

        return $this->sheets->append($this->sheetNames['nominasi'], $row);
    }

    public function syncSemuaNominasi()
    {
        $sheetName = $this->sheetNames['nominasi'];
        $nominasis = Nominasi::with(['juri', 'ikan'])
            ->where('status', 'approved')
            ->orderByDesc('created_at')
            ->get();

        $rows = $nominasis->map(function ($n) {
            $ikan = $n->ikan;
            return [
                $n->created_at ? Carbon::parse($n->created_at)->format('m/d/Y H:i:s') : '',
                $n->juri->name ?? '',
                $ikan->nomor_tank ?? '',
                strtoupper($ikan->kategori ?? ''),
                $this->formatKelasNominasi($ikan->kategori, $ikan->kelas),
            ];
        })->toArray();

        $this->sheets->clear($sheetName, 'A2:E2000');
        if (!empty($rows)) {
            $this->sheets->write($sheetName, 'A2', $rows);
        }
        return count($rows);
    }

    /* ═══════════════════════════════════════════
       SHEET: PLOTING TANK
       B=Kategori, C=Kelas, D=Min, E=Max
       Mulai baris 3
       ═══════════════════════════════════════════ */

    public function syncPlotingTank()
    {
        $sheetName = $this->sheetNames['ploting'];
        $classRanges = json_decode(
            DB::table('settings')->where('key', 'tank_class_ranges')->value('value'),
            true
        );

        if (!$classRanges || !is_array($classRanges)) return 0;

        $rows = [];
        foreach ($classRanges as $kelas => $data) {
            $kategoris = $data['kategori'] ?? [];
            foreach ($kategoris as $katName => $range) {
                $rows[] = [
                    strtoupper($katName),
                    $kelas,
                    $range['min'] ?? '',
                    $range['max'] ?? '',
                ];
            }
        }

        $this->sheets->clear($sheetName, 'B3:E100');
        if (!empty($rows)) {
            $this->sheets->write($sheetName, 'B3', $rows);
        }
        return count($rows);
    }

    /* ═══════════════════════════════════════════
       SHEET: PIL NOM
       A-H = data utama
       L ke depan = mapping kategori-kelas
       ═══════════════════════════════════════════ */

    private function getPilNomColumnMap(): array
    {
        return [
            'CENCU A'         => 11, 'CENCU B'         => 12, 'CENCU C'         => 13,
            'CENCU D'         => 14, 'CENCU E'         => 15,
            'CHINGWA A'       => 16, 'CHINGWA B'       => 17, 'CHINGWA C'       => 18,
            'CHINGWA D'       => 19, 'CHINGWA E'       => 20,
            'FREEMARKING A'   => 21, 'FREEMARKING B'   => 22, 'FREE MARKING C'  => 23,
            'FREEMARKING D'   => 24, 'FREEMARKING E'   => 25,
            'GOLDENBASE A'    => 26, 'GOLDENBASE B'    => 27, 'GOLDEN BASE C'   => 28,
            'GOLDEN BASE D'   => 29, 'GOLDEN BASE E'   => 30,
            'KLASIK A'        => 31, 'KLASIK B'        => 32, 'KLASIK C'        => 33,
            'KLASIK D'        => 34, 'KLASIK E'        => 35,
            'BONSAI'          => 36, 'JUMBO'           => 37,
        ];
    }

    private function findPilNomColumn($kategori, $kelas): ?int
    {
        $map = $this->getPilNomColumnMap();
        $kat = strtoupper($kategori);

        if ($kat === 'JUMBO') return $map['JUMBO'] ?? null;
        if ($kat === 'BONSAI') return $map['BONSAI'] ?? null;

        $key = $kat . ' ' . strtoupper($kelas);
        if (isset($map[$key])) return $map[$key];

        if ($kat === 'FREEMARKING') {
            $alt = 'FREE MARKING ' . strtoupper($kelas);
            if (isset($map[$alt])) return $map[$alt];
        }
        if ($kat === 'GOLDENBASE') {
            $alt = 'GOLDEN BASE ' . strtoupper($kelas);
            if (isset($map[$alt])) return $map[$alt];
        }
        return null;
    }

    public function tambahPilNom(Nominasi $nominasi)
    {
        $sheetName = $this->sheetNames['pil_nom'];
        $juri = $nominasi->juri;
        $ikan = $nominasi->ikan;
        if (!$juri || !$ikan) return false;

        $peserta = $ikan->peserta;
        $noTank = $ikan->nomor_tank;

        $row = [
            $nominasi->created_at ? Carbon::parse($nominasi->created_at)->format('d/m/Y H:i:s') : now()->format('d/m/Y H:i:s'),
            $juri->name ?? '',
            $peserta->nama_peserta ?? '',
            $ikan->kategori ?? '',
            $this->formatKelas($ikan->kategori, $ikan->kelas),
            ucfirst($peserta->jenis_keanggotaan ?? 'Team'),
            $peserta->detail_anggota ?? '',
            $noTank ?? '',
        ];

        $this->sheets->append($sheetName, $row);

        if ($noTank) {
            $colIndex = $this->findPilNomColumn($ikan->kategori, $ikan->kelas);
            if ($colIndex !== null) {
                $nextRow = $this->sheets->getNextRow($sheetName);
                $cellLetter = $this->sheets->colToLetter($colIndex + 1);
                $this->sheets->writeCell($sheetName, $cellLetter . ($nextRow - 1), $noTank);
            }
        }
        return true;
    }

    public function syncSemuaPilNom()
    {
        $sheetName = $this->sheetNames['pil_nom'];
        $nominasis = Nominasi::with(['juri', 'ikan.peserta'])
            ->where('status', 'approved')
            ->orderBy('juri_id')
            ->orderBy('created_at')
            ->get();

        $this->sheets->clear($sheetName, 'A2:Z1000');
        if ($nominasis->isEmpty()) return 0;

        $batch = [];
        $rowIdx = 0;

        foreach ($nominasis as $n) {
            $juri = $n->juri;
            $ikan = $n->ikan;
            $peserta = $ikan->peserta ?? null;
            $noTank = $ikan->nomor_tank;
            $actualRow = $rowIdx + 2;

            $batch[] = ['sheet' => $sheetName, 'cell' => 'A' . $actualRow, 'value' => $n->created_at ? Carbon::parse($n->created_at)->format('d/m/Y H:i:s') : ''];
            $batch[] = ['sheet' => $sheetName, 'cell' => 'B' . $actualRow, 'value' => $juri->name ?? ''];
            $batch[] = ['sheet' => $sheetName, 'cell' => 'C' . $actualRow, 'value' => $peserta->nama_peserta ?? ''];
            $batch[] = ['sheet' => $sheetName, 'cell' => 'D' . $actualRow, 'value' => $ikan->kategori ?? ''];
            $batch[] = ['sheet' => $sheetName, 'cell' => 'E' . $actualRow, 'value' => $this->formatKelas($ikan->kategori, $ikan->kelas)];
            $batch[] = ['sheet' => $sheetName, 'cell' => 'F' . $actualRow, 'value' => ucfirst($peserta->jenis_keanggotaan ?? 'Team')];
            $batch[] = ['sheet' => $sheetName, 'cell' => 'G' . $actualRow, 'value' => $peserta->detail_anggota ?? ''];
            $batch[] = ['sheet' => $sheetName, 'cell' => 'H' . $actualRow, 'value' => $noTank ?? ''];

            $rowIdx++;
        }

        if (!empty($batch)) {
            $this->sheets->batchUpdate($batch);
        }
        return count($nominasis);
    }

    /* ═══════════════════════════════════════════
       SYNC SEMUA
       ═══════════════════════════════════════════ */

    public function syncSemua()
    {
        $results = [];
        try { $results['peserta'] = $this->syncSemuaPeserta(); } catch (\Exception $e) { $results['peserta'] = 'Error: ' . $e->getMessage(); }
        try { $results['nominasi'] = $this->syncSemuaNominasi(); } catch (\Exception $e) { $results['nominasi'] = 'Error: ' . $e->getMessage(); }
        try { $results['pil_nom'] = $this->syncSemuaPilNom(); } catch (\Exception $e) { $results['pil_nom'] = 'Error: ' . $e->getMessage(); }
        try { $results['ploting_tank'] = $this->syncPlotingTank(); } catch (\Exception $e) { $results['ploting_tank'] = 'Error: ' . $e->getMessage(); }
        
        // ★ TAMBAHKAN INI:
        try { $results['nama_juri'] = $this->syncNamaJuri(); } catch (\Exception $e) { $results['nama_juri'] = 'Error: ' . $e->getMessage(); }
        try { $results['hasil_juri'] = $this->syncHasilJuri(); } catch (\Exception $e) { $results['hasil_juri'] = 'Error: ' . $e->getMessage(); }
        
        return $results;
    }

    private function formatKelas($kategori, $kelas): string
    {
        if (in_array($kategori, ['Bonsai', 'Jumbo'])) return '-';
        return $kelas ?? '-';
    }

        /* ═══════════════════════════════════════════
       SHEET: NAMA JURI
       Menulis daftar nama juri terdaftar
       ═══════════════════════════════════════════ */
    public function syncNamaJuri()
    {
        $sheetName = $this->sheetNames['nama_juri'];
        
        $juris = \App\Models\User::where('role', 'juri')
            ->orderBy('name')
            ->get();

        // Kita akan letakkan di kolom yang jauh di kanan agar tidak menimpa rumus
        // Misal mulai kolom AQ (ke-43)
        $startColIndex = 0; 
        $startRow = 1;

        $batch = [];
        
        // Header
        $batch[] = [
            'sheet' => $sheetName, 
            'cell'  => $this->sheets->colToLetter($startColIndex + 1) . $startRow, 
            'value' => 'DAFTAR NAMA JURI (AUTO SYNC)'
        ];
        $batch[] = [
            'sheet' => $sheetName, 
            'cell'  => $this->sheets->colToLetter($startColIndex + 1) . ($startRow + 1), 
            'value' => 'NO'
        ];
        $batch[] = [
            'sheet' => $sheetName, 
            'cell'  => $this->sheets->colToLetter($startColIndex + 2) . ($startRow + 1), 
            'value' => 'NAMA JURI'
        ];
        $batch[] = [
            'sheet' => $sheetName, 
            'cell'  => $this->sheets->colToLetter($startColIndex + 3) . ($startRow + 1), 
            'value' => 'EMAIL'
        ];

        foreach ($juris as $index => $juri) {
            $rowNum = $startRow + 2 + $index;
            $batch[] = [
                'sheet' => $sheetName, 
                'cell'  => $this->sheets->colToLetter($startColIndex + 1) . $rowNum, 
                'value' => $index + 1
            ];
            $batch[] = [
                'sheet' => $sheetName, 
                'cell'  => $this->sheets->colToLetter($startColIndex + 2) . $rowNum, 
                'value' => $juri->name
            ];
            $batch[] = [
                'sheet' => $sheetName, 
                'cell'  => $this->sheets->colToLetter($startColIndex + 3) . $rowNum, 
                'value' => $juri->email
            ];
        }

        if (!empty($batch)) {
            $this->sheets->batchUpdate($batch);
        }

        return count($juris);
    }

    /* ═══════════════════════════════════════════
       SHEET: HASIL JURI (Ditulis di Sheet RUMUS PENILAIAN baris bawah)
       Format: No Tank | Peserta | Kategori | Kelas | Nama Juri | Total Nilai | Detail Nilai
       ═══════════════════════════════════════════ */
    public function syncHasilJuri()
    {
        $sheetName = $this->sheetNames['rumus'];
        
        // Ambil semua scoring yang sudah dikirim
        $scorings = \App\Models\Scoring::where('submitted_to_grand', true)
            ->with(['ikan.peserta', 'juri'])
            ->orderBy('ikan_id')
            ->orderBy('juri_id')
            ->get();

        if ($scorings->isEmpty()) return 0;

        // Kita mulai tulis dari baris 30 ke bawah di Sheet Rumus
        $startRow = 30;
        
        // Hapus data lama (asumsi max 5000 baris data juri)
        $this->sheets->clear($sheetName, "A{$startRow}:X500");

        $batch = [];
        
        // Tulis Header
        $headers = ['NO TANK', 'NAMA PESERTA', 'KATEGORI', 'KELAS', 'NAMA JURI', 'TOTAL NILAI', 'TOTAL POINT'];
        foreach ($headers as $colIdx => $val) {
            $batch[] = [
                'sheet' => $sheetName, 
                'cell'  => $this->sheets->colToLetter($colIdx + 1) . $startRow, 
                'value' => $val
            ];
        }

        // Map detail nilai ke kolom (A=0, G=6)
        $detailColumns = [
            'head.size'        => 8,
            'head.bentuk_k'    => 9,
            'face.face'        => 10,
            'bodyshape.bentuk' => 11,
            'bodyshape.proporsional' => 12,
            'bodyshape.pangkal' => 13,
            'marking.fullness' => 14,
            'marking.contrast' => 15,
            'marking.bentuk'   => 16,
            'pearl.shinning'   => 17,
            'pearl.fullness'   => 18,
            'pearl.bentuk'     => 19,
            'colour.komposisi' => 20,
            'colour.kecerahan' => 21,
            'colour.fullness'  => 22,
            'finnage.bentuk_sirip_dan_ekor' => 23,
            'finnage.kecerahan' => 24,
        ];

        // Tulis Header Detail
        foreach ($detailColumns as $key => $col) {
            $batch[] = [
                'sheet' => $sheetName, 
                'cell'  => $this->sheets->colToLetter($col + 1) . $startRow, 
                'value' => strtoupper(str_replace('.', ' ', $key))
            ];
        }

        $rowIndex = $startRow + 1;

        foreach ($scorings as $s) {
            $ikan = $s->ikan;
            $peserta = $ikan->peserta;
            $juri = $s->juri;

            // Data Utama
            $batch[] = ['sheet' => $sheetName, 'cell' => 'A' . $rowIndex, 'value' => $ikan->nomor_tank ?? ''];
            $batch[] = ['sheet' => $sheetName, 'cell' => 'B' . $rowIndex, 'value' => $peserta->nama_peserta ?? 'Unknown'];
            $batch[] = ['sheet' => $sheetName, 'cell' => 'C' . $rowIndex, 'value' => $ikan->kategori ?? ''];
            $batch[] = ['sheet' => $sheetName, 'cell' => 'D' . $rowIndex, 'value' => $s->kelas ?? $ikan->kelas ?? '-'];
            $batch[] = ['sheet' => $sheetName, 'cell' => 'E' . $rowIndex, 'value' => $juri->name ?? '—'];
            $batch[] = ['sheet' => $sheetName, 'cell' => 'F' . $rowIndex, 'value' => $s->total_nilai ?? 0];
            $batch[] = ['sheet' => $sheetName, 'cell' => 'G' . $rowIndex, 'value' => $s->total_point ?? 0];

            // Detail Nilai
            $nilaiDetail = $s->nilai_detail ?: [];
            foreach ($detailColumns as $key => $col) {
                $parts = explode('.', $key);
                $val = 0;
                if (isset($nilaiDetail[$parts[0]][$parts[1]])) {
                    $val = (float) $nilaiDetail[$parts[0]][$parts[1]];
                }
                $batch[] = [
                    'sheet' => $sheetName, 
                    'cell'  => $this->sheets->colToLetter($col + 1) . $rowIndex, 
                    'value' => $val
                ];
            }

            $rowIndex++;
        }

        if (!empty($batch)) {
            // Kirim dalam batch kecil (maks 500 cell per request agar tidak timeout)
            $chunks = array_chunk($batch, 500);
            foreach ($chunks as $chunk) {
                $this->sheets->batchUpdate($chunk);
            }
        }

        return count($scorings);
    }

    private function formatKelasNominasi($kategori, $kelas): string
    {
        if (strtoupper($kategori) === 'JUMBO') return 'JUMBO';
        if (strtoupper($kategori) === 'BONSAI') return '-';
        return $kelas ?? '-';
    }

    public function getNextRow(string $sheetName, string $col = 'A')
    {
        $data = $this->read($sheetName, "{$col}1000");
        return count($data) + 1;
    }
}