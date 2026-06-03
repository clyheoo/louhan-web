<?php

namespace App\Services;

use App\Models\Ikan;
use App\Models\Peserta;
use App\Models\Nominasi;
use App\Helpers\PointCalculator;
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

        $this->sheets->clear($sheetName, 'A2:G500');
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

        $this->sheets->clear($sheetName, 'A2:E500');
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

    // ★ 1. Baca & tulis global range ke B1
    $globalMax = (int) (DB::table('settings')->where('key', 'tank_range_max')->value('value') ?? 1000);
    $resultB1 = $this->sheets->writeCell($sheetName, 'B1', $globalMax);
    Log::info('syncPlotingTank: B1 write result=' . var_export($resultB1, true) . ', value=' . $globalMax);

    // ★ 2. Baca sub-range dari database
    $rawJson = DB::table('settings')->where('key', 'tank_class_ranges')->value('value');
    Log::info('syncPlotingTank: raw JSON=' . $rawJson);

    $classRanges = json_decode($rawJson, true);

    if (!$classRanges || !is_array($classRanges)) {
        Log::info('syncPlotingTank: tidak ada sub-range, return 1');
        return 1;
    }

    // ★ 3. Susun data: C=Kategori, D=Kelas, E=Min, F=Max
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

    Log::info('syncPlotingTank: rows=' . json_encode($rows));

    // ★ 4. Clear & tulis
    $clearResult = $this->sheets->clear($sheetName, 'A3:D100');
    Log::info('syncPlotingTank: clear result=' . var_export($clearResult, true));

    if (!empty($rows)) {
        $writeResult = $this->sheets->write($sheetName, 'A3', $rows);
        Log::info('syncPlotingTank: write result=' . var_export($writeResult, true));
    } else {
        Log::info('syncPlotingTank: rows kosong, skip write');
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

        // Ambil baris tujuan SEBELUM append
        $targetRow = $this->sheets->getNextRow($sheetName);

        $this->sheets->append($sheetName, $row);

        // Tulis ke kolom mapping jika ada no tank
        if ($noTank) {
            $colIndex = $this->findPilNomColumn($ikan->kategori, $ikan->kelas);
            if ($colIndex !== null) {
                $colNumber = $colIndex + 1;
                // ⛔ Skip jika melebihi kolom Z (26)
                if ($colNumber <= 26) {
                    $cellLetter = $this->sheets->colToLetter($colNumber);
                    // ⛔ Pastikan baris minimal 1 (tidak pernah 0)
                    $safeRow = max(1, $targetRow);
                    $this->sheets->writeCell($sheetName, $cellLetter . $safeRow, $noTank);
                } else {
                    Log::warning("tambahPilNom: kolom {$colNumber} melebihi Z, skip mapping untuk tank {$noTank}");
                }
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
            $actualRow = $rowIdx + 2; // mulai dari baris 2

            // Data utama A-H
            $batch[] = ['sheet' => $sheetName, 'cell' => 'A' . $actualRow, 'value' => $n->created_at ? Carbon::parse($n->created_at)->format('d/m/Y H:i:s') : ''];
            $batch[] = ['sheet' => $sheetName, 'cell' => 'B' . $actualRow, 'value' => $juri->name ?? ''];
            $batch[] = ['sheet' => $sheetName, 'cell' => 'C' . $actualRow, 'value' => $peserta->nama_peserta ?? ''];
            $batch[] = ['sheet' => $sheetName, 'cell' => 'D' . $actualRow, 'value' => $ikan->kategori ?? ''];
            $batch[] = ['sheet' => $sheetName, 'cell' => 'E' . $actualRow, 'value' => $this->formatKelas($ikan->kategori, $ikan->kelas)];
            $batch[] = ['sheet' => $sheetName, 'cell' => 'F' . $actualRow, 'value' => ucfirst($peserta->jenis_keanggotaan ?? 'Team')];
            $batch[] = ['sheet' => $sheetName, 'cell' => 'G' . $actualRow, 'value' => $peserta->detail_anggota ?? ''];
            $batch[] = ['sheet' => $sheetName, 'cell' => 'H' . $actualRow, 'value' => $noTank ?? ''];

            // ★ TAMBAHAN: Tulis ke kolom mapping
            if ($noTank) {
                $colIndex = $this->findPilNomColumn($ikan->kategori, $ikan->kelas);
                if ($colIndex !== null) {
                    $colNumber = $colIndex + 1;
                    if ($colNumber <= 26) {
                        $cellLetter = $this->sheets->colToLetter($colNumber);
                        $batch[] = ['sheet' => $sheetName, 'cell' => $cellLetter . $actualRow, 'value' => $noTank];
                    }
                }
            }

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
        try { $results['nama_juri'] = $this->syncNamaJuri(); } catch (\Exception $e) { $results['nama_juri'] = 'Error: ' . $e->getMessage(); }
        try { $results['hasil_juri'] = $this->syncHasilJuri(); } catch (\Exception $e) { $results['hasil_juri'] = 'Error: ' . $e->getMessage(); }
        try { $results['hasil_nominasi'] = $this->syncHasilNominasi(); } catch (\Exception $e) { $results['hasil_nominasi'] = 'Error: ' . $e->getMessage(); }
        try { $results['nominasi_fix'] = $this->syncNominasiFix(); } catch (\Exception $e) { $results['nominasi_fix'] = 'Error: ' . $e->getMessage(); }
        try { $results['cnt'] = $this->syncNilaiJuri(); } catch (\Exception $e) { $results['cnt'] = 'Error: ' . $e->getMessage(); }
        try { $results['mvp'] = $this->syncMvp(); } catch (\Exception $e) { $results['mvp'] = 'Error: ' . $e->getMessage(); }
        
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

        // ★ TAMBAHAN: Hapus data lama sebelum tulis baru
        $this->sheets->clear($sheetName, 'A1:C100');

        $startColIndex = 0; 
        $startRow = 1;

        $batch = [];
        
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
       SHEET: HASIL JURI
       Baris 1: Header label (KATEGORI, KELAS, NAMA JURI) - JANGAN DITIMPA
       Baris 2: Cell untuk dropdown filter - JANGAN DITIMPA
       Baris 3 ke bawah: Data blok per juri
       Kolom A-W: Data mentah nilai
       Kolom X ke depan: RUMUS - JANGAN DITIMPA
       
       Dropdown source ditulis di kolom Z, AA, AB
       ═══════════════════════════════════════════ */
    public function syncHasilJuri()
    {
        $sheetName = 'HASIL JURI';
        
        // ═══════════════════════════════════════
        // BAGIAN 1: TULIS DATA SOURCE DROPDOWN
        // Ke kolom Z (kategori), AA (kelas), AB (nama juri)
        // ═══════════════════════════════════════
        
        // Ambil daftar kategori unik dari database
        $kategoris = Ikan::whereNotNull('nomor_tank')
            ->select('kategori')
            ->distinct()
            ->orderBy('kategori')
            ->pluck('kategori')
            ->map(fn($k) => strtoupper($k))
            ->toArray();
        
        // Ambil daftar kelas unik
        $kelases = Ikan::whereNotNull('nomor_tank')
            ->whereNotNull('kelas')
            ->select('kelas')
            ->distinct()
            ->orderBy('kelas')
            ->pluck('kelas')
            ->toArray();
        
        // Tambahkan kelas khusus
        if (!in_array('JUMBO', $kelases)) $kelases[] = 'JUMBO';
        sort($kelases);
        
        // Ambil daftar nama juri
        $namasJuri = \App\Models\User::where('role', 'juri')
            ->orderBy('name')
            ->pluck('name')
            ->toArray();
        
        // Tulis header dropdown source
        $batch = [];
        $batch[] = ['sheet' => $sheetName, 'cell' => 'Z1', 'value' => 'DAFTAR KATEGORI'];
        $batch[] = ['sheet' => $sheetName, 'cell' => 'AA1', 'value' => 'DAFTAR KELAS'];
        $batch[] = ['sheet' => $sheetName, 'cell' => 'AB1', 'value' => 'DAFTAR NAMA JURI'];
        
        // Tulis data kategori
        foreach ($kategoris as $idx => $kat) {
            $batch[] = ['sheet' => $sheetName, 'cell' => 'Z' . ($idx + 2), 'value' => $kat];
        }
        
        // Tulis data kelas
        foreach ($kelases as $idx => $kelas) {
            $batch[] = ['sheet' => $sheetName, 'cell' => 'AA' . ($idx + 2), 'value' => $kelas];
        }
        
        // Tulis data nama juri
        foreach ($namasJuri as $idx => $nama) {
            $batch[] = ['sheet' => $sheetName, 'cell' => 'AB' . ($idx + 2), 'value' => $nama];
        }
        
        // Clear range dropdown source dulu
        $this->sheets->clear($sheetName, 'Z1:AB100');
        
        // ═══════════════════════════════════════
        // BAGIAN 2: TULIS DATA NILAI KE BLOK PER JURI
        // Mulai baris 3, hanya kolom A-W (jangan sentuh rumus di X+)
        // ═══════════════════════════════════════
        
        // Ambil semua scoring yang sudah dikirim
        $scorings = \App\Models\Scoring::whereNotNull('nilai_detail')
            ->whereNotNull('juri_id')
            ->with(['ikan.peserta', 'juri'])
            ->orderBy('juri_id')
            ->orderBy('ikan_id')
            ->get();

        // Clear data lama HANYA kolom A-W mulai baris 3
        $this->sheets->clear($sheetName, 'A3:W5000');

        if ($scorings->isEmpty()) {
            // Tetap tulis dropdown source meski tidak ada data
            if (!empty($batch)) {
                $this->sheets->batchUpdate($batch);
            }
            return 0;
        }

        // Sub-header untuk setiap blok (baris pertama blok)
        $subHeaders = [
            'NO TANK', 'OVERAL', 'SIZE', 'BENTUK', 'DEEFCT',
            'FACE', 'DF FACE', 'BENTUK', 'PROPOSIONAL', 'PANGKAL',
            'DF BODY', 'FULLNESS', 'CONTRAST', 'BENTUK',
            'SHINNING', 'FULLNESS', 'BENTUK',
            'KOMPOSISI', 'KECERAHAN', 'FULLNESS',
            'BENTUK', 'KECERAHAN', 'DF FINAGE'
        ];

        // Sub-header baris kedua (kolom yang merge)
        $subHeaders2 = [
            '', '', 'HEAD', 'HEAD', '',
            '', '', 'BODY SHAPE', 'BODY SHAPE', 'BODY SHAPE',
            '', 'MARKING', 'MARKING', 'MARKING',
            'PEARL', 'PEARL', 'PEARL',
            'COLOUR', 'COLOUR', 'COLOUR',
            'FINNAGE', 'FINNAGE', ''
        ];

        // Group scoring by juri
        $groupedByJuri = $scorings->groupBy(function($s) {
            return $s->juri_id . '|' . strtoupper($s->ikan->kategori ?? '') . '|' . ($s->kelas ?? $s->ikan->kelas ?? '-');
        });

        $currentRow = 3;
        $rowsPerBlock = 12;
        $gapRows = 2;

        // Tulis sub-header HANYA SEKALI di baris 3 dan 4
        foreach ($subHeaders as $colIdx => $val) {
            $colLetter = $this->sheets->colToLetter($colIdx + 1);
            $batch[] = ['sheet' => $sheetName, 'cell' => $colLetter . $currentRow, 'value' => $val];
        }
        $currentRow++;

        foreach ($subHeaders2 as $colIdx => $val) {
            $colLetter = $this->sheets->colToLetter($colIdx + 1);
            $batch[] = ['sheet' => $sheetName, 'cell' => $colLetter . $currentRow, 'value' => $val];
        }
        $currentRow++;

        foreach ($groupedByJuri as $groupKey => $jurisScorings) {   
            
            // Tulis data nilai
            $dataRowCount = 0;
            foreach ($jurisScorings as $s) {
                if ($dataRowCount >= $rowsPerBlock) break;
                
                $ikan = $s->ikan;
                $nd = $s->nilai_detail ?: [];
                
                // Defect eval
                $defectInput = [
                    'raw_head_penalty'    => $s->raw_head_penalty ?? ['0'],
                    'raw_face_penalty'    => $s->raw_face_penalty ?? ['0'],
                    'raw_body_penalty'    => $s->raw_body_penalty ?? ['0'],
                    'raw_finnage_penalty' => $s->raw_finnage_penalty ?? ['0'],
                ];
                $defectEval = \App\Helpers\PointCalculator::evaluateDefects($defectInput);
                
                $gv = function($kat, $field) use ($nd) {
                    return $nd[$kat][$field] ?? 0;
                };
                
                $formatDefectPct = function($key) use ($defectEval) {
                    $val = $defectEval[$key] ?? '0';
                    return $val === '0' ? 0 : (float) $val;
                };
                
                // Kolom A-W sesuai struktur sheet HASIL JURI
                $row = [
                    $ikan->nomor_tank ?? '',                                          // A: NO TANK
                    $gv('overall', 'impression'),                                     // B: OVERAL
                    $gv('head', 'size'),                                              // C: SIZE (HEAD)
                    $gv('head', 'bentuk'),                                            // D: BENTUK (HEAD)
                    $formatDefectPct('head_penalty'),                                 // E: DEEFCT
                    $gv('face', 'face'),                                              // F: FACE
                    $formatDefectPct('face_penalty'),                                 // G: DF FACE
                    $gv('body', 'bentuk'),                                            // H: BENTUK (BODY SHAPE)
                    $gv('body', 'proporsional'),                                      // I: PROPOSIONAL
                    $gv('body', 'pangkal'),                                           // J: PANGKAL
                    $formatDefectPct('body_penalty'),                                 // K: DF BODY
                    $gv('marking', 'fullness'),                                       // L: FULLNESS (MARKING)
                    $gv('marking', 'contrast'),                                       // M: CONTRAST
                    $gv('marking', 'bentuk'),                                         // N: BENTUK
                    $gv('pearl', 'shinning') ?: ($nd['pearl']['shining'] ?? 0),      // O: SHINNING
                    $gv('pearl', 'fullness'),                                         // P: FULLNESS
                    $gv('pearl', 'bentuk'),                                           // Q: BENTUK
                    $gv('color', 'komposisi'),                                        // R: KOMPOSISI
                    $gv('color', 'kecerahan'),                                        // S: KECERAHAN
                    $gv('color', 'fullness'),                                         // T: FULLNESS
                    $gv('finnage', 'bentuk_sirip_dan_ekor') ?: ($nd['finnage']['bentuk'] ?? 0), // U: BENTUK
                    $gv('finnage', 'kecerahan'),                                      // V: KECERAHAN
                    $formatDefectPct('finnage_penalty'),                              // W: DF FINAGE
                ];
                
                foreach ($row as $colIdx => $val) {
                    $colLetter = $this->sheets->colToLetter($colIdx + 1);
                    $batch[] = ['sheet' => $sheetName, 'cell' => $colLetter . $currentRow, 'value' => $val];
                }
                
                $currentRow++;
                $dataRowCount++;
            }
            
            // Tambahkan baris kosong untuk gap
            $currentRow += $gapRows;
        }

        // Kirim batch
        if (!empty($batch)) {
            $chunks = array_chunk($batch, 500);
            foreach ($chunks as $chunk) {
                $this->sheets->batchUpdate($chunk);
            }
        }

        return count($scorings);
    }

        /* ═══════════════════════════════════════════
       SHEET: HASIL NOMINASI
       A=Tanggal, B=Nama Juri, C=Nama Peserta,
       D=Kategori, E=Kelas, F=Tim, G=Team, H=No Tank
       ═══════════════════════════════════════════ */

    public function syncHasilNominasi()
    {
        $sheetName = $this->sheetNames['hasil_nominasi'];
        
        $nominasis = Nominasi::with(['juri', 'reviewer', 'ikan.peserta'])
            ->whereIn('status', ['approved', 'rejected'])
            ->whereNotNull('reviewed_by')
            ->orderByDesc('reviewed_at')
            ->get();

        $this->sheets->clear($sheetName, 'A2:H500');

        if ($nominasis->isEmpty()) return 0;

        $rows = $nominasis->map(function ($n) {
            $ikan = $n->ikan;
            $peserta = $ikan->peserta ?? null;

            return [
                $n->reviewed_at ? Carbon::parse($n->reviewed_at)->format('d/m/Y H:i:s') : '',
                $n->status === 'approved' ? '✅ DISETUJUI GRAND JURI' : '❌ DITOLAK GRAND JURI',
                $peserta->nama_peserta ?? '',
                strtoupper($ikan->kategori ?? ''),
                $this->formatKelasNominasi($ikan->kategori, $ikan->kelas),
                ucfirst($peserta->jenis_keanggotaan ?? 'Team'),
                $peserta->detail_anggota ?? '',
                $ikan->nomor_tank ?? '',
            ];
        })->toArray();

        if (!empty($rows)) {
            $this->sheets->write($sheetName, 'A2', $rows);
        }

        return count($rows);
    }

        /* ═══════════════════════════════════════════
       SHEET: NOMINASI FIX (FORMAT VERTIKAL)
       A=No Urut, B=Kategori, C=Kelas, 
       D=No Tank, E=Keterangan
       ═══════════════════════════════════════════ */

    public function syncNominasiFix()
    {
        $sheetName = $this->sheetNames['nominasi_fix'];

        $nominasis = Nominasi::with('ikan')
            ->where('status', 'approved')
            ->get()
            ->unique('ikan_id');

        $this->sheets->clear($sheetName, 'A3:E500');

        if ($nominasis->isEmpty()) return 0;

        $groups = [];
        foreach ($nominasis as $n) {
            $ikan = $n->ikan;
            // ★ Hanya skip jika ikan benar-benar tidak ada
            if (!$ikan) continue;

            $kat = strtoupper($ikan->kategori ?? '');
            $kelas = $this->formatKelasNominasi($ikan->kategori, $ikan->kelas);

            if ($kat === 'BONSAI') {
                $groupKey = 'BONSAI';
            } elseif ($kat === 'JUMBO') {
                $groupKey = 'JUMBO';
            } else {
                $groupKey = $kat . ' ' . $kelas;
            }

            $groups[$groupKey][] = [
                'kategori'  => $kat,
                'kelas'     => $kelas,
                'no_tank'   => $ikan->nomor_tank ?? '',
            ];
        }

        ksort($groups);

        $batch = [];
        $formatRequests = [];
        $sheetId = $this->sheets->getSheetId($sheetName);

        $cyanBg = [
            'red'   => 0.0,
            'green' => 1.0,
            'blue'  => 1.0,
            'alpha' => 1.0
        ];

        $row = 3;

        $formatRequests[] = [
            'unmergeCells' => [
                'range' => [
                    'sheetId' => $sheetId,
                    'startRowIndex' => 2,
                    'endRowIndex' => 500,
                    'startColumnIndex' => 0,
                    'endColumnIndex' => 5
                ]
            ]
        ];

        foreach ($groups as $groupName => $items) {
            // 1. Header Grup
            $batch[] = ['sheet' => $sheetName, 'cell' => 'A' . $row, 'value' => $groupName];

            $formatRequests[] = [
                'mergeCells' => [
                    'range' => [
                        'sheetId' => $sheetId,
                        'startRowIndex' => $row - 1, 'endRowIndex' => $row,
                        'startColumnIndex' => 0, 'endColumnIndex' => 5
                    ],
                    'mergeType' => 'MERGE_ALL'
                ]
            ];
            $formatRequests[] = [
                'repeatCell' => [
                    'range' => [
                        'sheetId' => $sheetId,
                        'startRowIndex' => $row - 1, 'endRowIndex' => $row,
                        'startColumnIndex' => 0, 'endColumnIndex' => 5
                    ],
                    'cell' => [
                        'userEnteredFormat' => [
                            'horizontalAlignment' => 'CENTER',
                            'backgroundColor'   => $cyanBg,
                            'textFormat' => [
                                'bold'     => true,
                                'fontSize' => 17
                            ]
                        ]
                    ],
                    'fields' => 'userEnteredFormat(horizontalAlignment,backgroundColor,textFormat)'
                ]
            ];
            $row++;

            // 2. Sub-header
            $batch[] = ['sheet' => $sheetName, 'cell' => 'A' . $row, 'value' => 'NO URUT'];
            $batch[] = ['sheet' => $sheetName, 'cell' => 'B' . $row, 'value' => 'KATEGORI'];
            $batch[] = ['sheet' => $sheetName, 'cell' => 'C' . $row, 'value' => 'KELAS'];
            $batch[] = ['sheet' => $sheetName, 'cell' => 'D' . $row, 'value' => 'NO TANK'];
            $batch[] = ['sheet' => $sheetName, 'cell' => 'E' . $row, 'value' => 'KETERANGAN'];

            $formatRequests[] = [
                'repeatCell' => [
                    'range' => [
                        'sheetId' => $sheetId,
                        'startRowIndex' => $row - 1, 'endRowIndex' => $row,
                        'startColumnIndex' => 0, 'endColumnIndex' => 5
                    ],
                    'cell' => [
                        'userEnteredFormat' => [
                            'backgroundColor' => $cyanBg,
                            'textFormat' => [
                                'bold'     => false,
                                'fontSize' => 10
                            ]
                        ]
                    ],
                    'fields' => 'userEnteredFormat(backgroundColor,textFormat)'
                ]
            ];
            $row++;

            // 3. Data Rows
            usort($items, fn($a, $b) => $a['no_tank'] <=> $b['no_tank']);

            foreach ($items as $idx => $item) {
                $batch[] = ['sheet' => $sheetName, 'cell' => 'A' . $row, 'value' => $idx + 1];
                $batch[] = ['sheet' => $sheetName, 'cell' => 'B' . $row, 'value' => $item['kategori']];
                $batch[] = ['sheet' => $sheetName, 'cell' => 'C' . $row, 'value' => $item['kelas']];
                $batch[] = ['sheet' => $sheetName, 'cell' => 'D' . $row, 'value' => $item['no_tank']];
                $batch[] = ['sheet' => $sheetName, 'cell' => 'E' . $row, 'value' => ''];
                $row++;
            }

            $row += 2;
        }

        if (!empty($batch)) {
            $this->sheets->batchUpdate($batch);
        }

        if (!empty($formatRequests)) {
            $this->sheets->formatCells($formatRequests);
        }

        return $nominasis->count();
    }

    /* ═══════════════════════════════════════════
       SHEET: MVP (LAYOUT HORIZONTAL)
       Setiap peserta = 1 tabel (5 kolom)
       Maks 4 tabel per baris, gap 1 kolom
       Batas kolom W
       ═══════════════════════════════════════════ */

    public function syncMvp()
    {
        $sheetName = $this->sheetNames['mvp'];

        $ikans = Ikan::where('is_mvp', true)
            ->whereHas('peserta', function ($q) {
                $q->where('is_mvp_submitted', true);
            })
            ->with(['peserta', 'bonusPoints', 'scorings'])
            ->get();

        $sheetId = $this->sheets->getSheetId($sheetName);

        // ★ Clear data saja
        $this->sheets->clear($sheetName, 'A1:W1000');

        if ($ikans->isEmpty()) return 0;

        // ─── Konfigurasi Layout ───
        $COLS_PER_TABLE = 5;
        $COL_GAP = 1;
        $TABLES_PER_ROW = 4;

        $tableColStarts = [];
        $col = 0;
        for ($i = 0; $i < $TABLES_PER_ROW; $i++) {
            $tableColStarts[] = $col;
            $col += $COLS_PER_TABLE + $COL_GAP;
        }

        // ─── Group & Siapkan Data Per Peserta ───
        $groups = $ikans->groupBy('peserta_id');
        $pesertaData = [];

        foreach ($groups as $pesertaId => $items) {
            $peserta = $items->first()->peserta;
            $nama = $peserta->nama_peserta ?? 'Unknown';
            $team = $peserta->detail_anggota ?? '';

            $headerText = $nama;
            if ($team) $headerText .= ' - ' . $team;

            $rows = [];
            $totalPoint = 0;
            $no = 1;

            foreach ($items as $ikan) {
                $point = $this->hitungFinalPoint($ikan);
                $totalPoint += $point;

                $rows[] = [
                    $no,
                    $nama,
                    strtoupper($ikan->kategori ?? ''),
                    $ikan->nomor_tank ?? '',
                    $point
                ];
                $no++;
            }

            $tableHeight = 2 + count($rows) + 1;

            $pesertaData[] = [
                'header'     => $headerText,
                'rows'       => $rows,
                'totalPoint' => $totalPoint,
                'height'     => $tableHeight
            ];
        }

        // ─── Bangun Batch Write ───
        $batch = [];
        $formatRequests = [];
        $currentRow = 1;
        $pesertaIndex = 0;
        $totalPeserta = count($pesertaData);

        while ($pesertaIndex < $totalPeserta) {
            $rowPesertas = [];
            $maxHeight = 0;

            for ($i = 0; $i < $TABLES_PER_ROW && $pesertaIndex < $totalPeserta; $i++) {
                $rowPesertas[] = [
                    'data'     => $pesertaData[$pesertaIndex],
                    'colStart' => $tableColStarts[$i]
                ];
                $maxHeight = max($maxHeight, $pesertaData[$pesertaIndex]['height']);
                $pesertaIndex++;
            }

            foreach ($rowPesertas as $rp) {
                $data = $rp['data'];
                $cs = $rp['colStart'];

                // ── Baris 1: Header di SETIAP kolom (tanpa merge) ──
                for ($ci = 0; $ci < $COLS_PER_TABLE; $ci++) {
                    $batch[] = [
                        'sheet' => $sheetName,
                        'cell'  => $this->sheets->colToLetter($cs + $ci + 1) . $currentRow,
                        'value' => $data['header']
                    ];
                }

                if ($sheetId !== null) {
                    $formatRequests[] = [
                        'repeatCell' => [
                            'range' => [
                                'sheetId' => $sheetId,
                                'startRowIndex' => $currentRow - 1,
                                'endRowIndex' => $currentRow,
                                'startColumnIndex' => $cs,
                                'endColumnIndex' => $cs + $COLS_PER_TABLE
                            ],
                            'cell' => [
                                'userEnteredFormat' => [
                                    'horizontalAlignment' => 'CENTER',
                                    'textFormat' => [
                                        'bold'     => true,
                                        'fontSize' => 10
                                    ]
                                ]
                            ],
                            'fields' => 'userEnteredFormat(horizontalAlignment,textFormat)'
                        ]
                    ];
                }

                // ── Baris 2: Sub-Header ──
                $subRow = $currentRow + 1;
                $subHeaders = ['NO', 'NAMA PESERTA', 'KATEGORI', 'NO TANK', 'POINT'];
                foreach ($subHeaders as $ci => $val) {
                    $batch[] = [
                        'sheet' => $sheetName,
                        'cell'  => $this->sheets->colToLetter($cs + $ci + 1) . $subRow,
                        'value' => $val
                    ];
                }

                // ── Baris 3+: Data Ikan ──
                $dataRow = $subRow + 1;
                foreach ($data['rows'] as $row) {
                    foreach ($row as $ci => $val) {
                        $batch[] = [
                            'sheet' => $sheetName,
                            'cell'  => $this->sheets->colToLetter($cs + $ci + 1) . $dataRow,
                            'value' => $val
                        ];
                    }
                    $dataRow++;
                }

                // ── Baris Terakhir: TOTAL ──
                $batch[] = [
                    'sheet' => $sheetName,
                    'cell'  => $this->sheets->colToLetter($cs + 3) . $dataRow,
                    'value' => 'TOTAL'
                ];
                $batch[] = [
                    'sheet' => $sheetName,
                    'cell'  => $this->sheets->colToLetter($cs + 5) . $dataRow,
                    'value' => (int) $data['totalPoint']
                ];
            }

            $currentRow += $maxHeight + 1;
        }

        // ─── Eksekusi Data ───
        if (!empty($batch)) {
            foreach (array_chunk($batch, 500) as $chunk) {
                $this->sheets->batchUpdate($chunk);
            }
        }

        // ─── Eksekusi Format (center, bold) ───
        if (!empty($formatRequests) && $sheetId !== null) {
            foreach (array_chunk($formatRequests, 50) as $chunk) {
                $this->sheets->formatCells($chunk);
            }
        }

        // ─── Merge Header (terpisah, boleh gagal) ───
        if ($sheetId !== null) {
            $mergeRequests = [];
            $currentRowMerge = 1;
            $pesertaIndexMerge = 0;

            while ($pesertaIndexMerge < $totalPeserta) {
                for ($i = 0; $i < $TABLES_PER_ROW && $pesertaIndexMerge < $totalPeserta; $i++) {
                    $cs = $tableColStarts[$i];
                    $height = $pesertaData[$pesertaIndexMerge]['height'];

                    $mergeRequests[] = [
                        'mergeCells' => [
                            'range' => [
                                'sheetId' => $sheetId,
                                'startRowIndex' => $currentRowMerge - 1,
                                'endRowIndex' => $currentRowMerge,
                                'startColumnIndex' => $cs,
                                'endColumnIndex' => $cs + $COLS_PER_TABLE
                            ],
                            'mergeType' => 'MERGE_ALL'
                        ]
                    ];

                    $currentRowMerge += $height + 1;
                    $pesertaIndexMerge++;
                }
            }

            if (!empty($mergeRequests)) {
                try {
                    foreach (array_chunk($mergeRequests, 50) as $chunk) {
                        $this->sheets->formatCells($chunk);
                    }
                } catch (\Exception $e) {
                    Log::warning('Merge header MVP gagal (tidak kritis): ' . $e->getMessage());
                }
            }
        }

        return $ikans->count();
    }

        /* ═══════════════════════════════════════════════════════
       SHEET: NILAI JURI (NILAI FINAL SETELAH GRAND JURI EDIT)
       28 kolom: A-AB (struktur sama CNT)
       ═══════════════════════════════════════════════════════ */
    public function syncNilaiJuri()
    {
        $sheetName = $this->sheetNames['nilai_juri'];

        // Ambil 1 scoring terbaru per ikan (yang mana punya nilai dari juri biasa ATAU sudah di-edit grand juri)
        $latestIds = \App\Models\Scoring::select('ikan_id', \DB::raw('MAX(id) as latest_id'))
            ->groupBy('ikan_id')
            ->pluck('latest_id', 'ikan_id')
            ->toArray();

        $scorings = \App\Models\Scoring::whereIn('id', $latestIds)
            ->whereNotNull('juri_id')
            ->with(['ikan.peserta', 'juri'])
            ->orderBy('created_at', 'desc')
            ->get();

        $this->sheets->clear($sheetName, 'A2:AB2000');

        if ($scorings->isEmpty()) return 0;

        $rows = [];
        foreach ($scorings as $s) {
            $ikan = $s->ikan;
            $peserta = $ikan->peserta ?? null;
            $nd = $s->nilai_detail ?: [];

            $defectInput = [
                'raw_head_penalty'    => $s->raw_head_penalty ?? ['0'],
                'raw_face_penalty'    => $s->raw_face_penalty ?? ['0'],
                'raw_body_penalty'    => $s->raw_body_penalty ?? ['0'],
                'raw_finnage_penalty' => $s->raw_finnage_penalty ?? ['0'],
            ];
            $defectEval = \App\Helpers\PointCalculator::evaluateDefects($defectInput);

            $gv = function($kat, $field) use ($nd) {
                return $nd[$kat][$field] ?? 0;
            };

            $formatDefectKet = function($raw) {
                if (!$raw || !is_array($raw)) return '-';
                $filtered = array_filter($raw, fn($v) => $v && $v !== '0');
                return empty($filtered) ? '-' : implode(', ', $filtered);
            };

            $formatDefectPct = function($key) use ($defectEval) {
                $val = $defectEval[$key] ?? '0';
                return $val === '0' ? 0 : (int) $val;
            };

            $rows[] = [
                $s->created_at ? Carbon::parse($s->created_at)->format('d/m/Y H:i:s') : '',
                $s->juri->name ?? '',
                strtoupper($ikan->kategori ?? ''),
                $s->kelas ?? $ikan->kelas ?? '-',
                $ikan->nomor_tank ?? '',
                $gv('overall', 'impression'),
                $gv('head', 'size'),
                $gv('head', 'bentuk'),
                $formatDefectPct('head_penalty'),
                $gv('face', 'face'),
                $formatDefectPct('face_penalty'),
                $gv('body', 'bentuk'),
                $gv('body', 'proporsional'),
                $gv('body', 'pangkal'),
                $formatDefectPct('body_penalty'),
                $gv('marking', 'fullness'),
                $gv('marking', 'contrast'),
                $gv('marking', 'bentuk'),
                $gv('pearl', 'shinning') ?: ($nd['pearl']['shining'] ?? 0),
                $gv('pearl', 'fullness'),
                $gv('pearl', 'bentuk'),
                $gv('color', 'komposisi'),
                $gv('color', 'kecerahan'),
                $gv('color', 'fullness'),
                $gv('finnage', 'bentuk_sirip_dan_ekor') ?: ($nd['finnage']['bentuk'] ?? 0),
                $gv('finnage', 'kecerahan'),
                $formatDefectPct('finnage_penalty'),
                $formatDefectKet($s->raw_head_penalty),
            ];
        }

        if (!empty($rows)) {
            $this->sheets->write($sheetName, 'A2', $rows);
        }

        return count($rows);
    }

    /* ═══════════════════════════════════════════════════════
       SHEET: CNT (NILAI JURI MENTAH)
       28 kolom: A-AB
       ═══════════════════════════════════════════════════════ */
    public function syncCnt()
    {
        $sheetName = $this->sheetNames['cnt'];

        $scorings = \App\Models\Scoring::whereNotNull('nilai_detail')
            ->whereNotNull('juri_id')
            ->with(['ikan.peserta', 'juri'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Clear baris 2 ke bawah (baris 1 = header manual)
        $this->sheets->clear($sheetName, 'A2:AB2000');

        if ($scorings->isEmpty()) return 0;

        $rows = [];
        foreach ($scorings as $s) {
            $ikan = $s->ikan;
            $peserta = $ikan->peserta ?? null;
            $nd = $s->nilai_detail ?: [];

            // Defect eval
            $defectInput = [
                'raw_head_penalty'    => $s->raw_head_penalty ?? ['0'],
                'raw_face_penalty'    => $s->raw_face_penalty ?? ['0'],
                'raw_body_penalty'    => $s->raw_body_penalty ?? ['0'],
                'raw_finnage_penalty' => $s->raw_finnage_penalty ?? ['0'],
            ];
            $defectEval = \App\Helpers\PointCalculator::evaluateDefects($defectInput);

            // Helper: ambil nilai atau 0
            $gv = function($kat, $field) use ($nd) {
                return $nd[$kat][$field] ?? 0;
            };

            // Helper: format defect keterangan
            $formatDefectKet = function($raw) {
                if (!$raw || !is_array($raw)) return '-';
                $filtered = array_filter($raw, fn($v) => $v && $v !== '0');
                return empty($filtered) ? '-' : implode(', ', $filtered);
            };

            // Helper: format defect persen
            $formatDefectPct = function($key) use ($defectEval) {
                $val = $defectEval[$key] ?? '0';
                return $val === '0' ? 0 : (int) $val;
            };

            $rows[] = [
                $s->created_at ? Carbon::parse($s->created_at)->format('d/m/Y H:i:s') : '',  // A
                $s->juri->name ?? '',                                                     // B
                strtoupper($ikan->kategori ?? ''),                                       // C
                $s->kelas ?? $ikan->kelas ?? '-',                                        // D
                $ikan->nomor_tank ?? '',                                                // E
                $gv('overall', 'impression'),                                            // F
                $gv('head', 'size'),                                                    // G
                $gv('head', 'bentuk'),                                                  // H
                $formatDefectPct('head_penalty'),                                       // I
                $gv('face', 'face'),                                                    // J
                $formatDefectPct('face_penalty'),                                       // K
                $gv('body', 'bentuk'),                                                  // L
                $gv('body', 'proporsional'),                                            // M
                $gv('body', 'pangkal'),                                                 // N
                $formatDefectPct('body_penalty'),                                       // O
                $gv('marking', 'fullness'),                                             // P
                $gv('marking', 'contrast'),                                              // Q
                $gv('marking', 'bentuk'),                                               // R
                $gv('pearl', 'shinning') ?: ($nd['pearl']['shining'] ?? 0),            // S
                $gv('pearl', 'fullness'),                                               // T
                $gv('pearl', 'bentuk'),                                                 // U
                $gv('color', 'komposisi'),                                               // V
                $gv('color', 'kecerahan'),                                               // W
                $gv('color', 'fullness'),                                               // X
                $gv('finnage', 'bentuk_sirip_dan_ekor') ?: ($nd['finnage']['bentuk'] ?? 0), // Y
                $gv('finnage', 'kecerahan'),                                             // Z
                $formatDefectPct('finnage_penalty'),                                    // AA
                $formatDefectKet($s->raw_head_penalty),                                 // AB
            ];
        }

        // Tulis mulai A2 (menggunakan write() karena mendukung kolom di atas Z)
        if (!empty($rows)) {
            $this->sheets->write($sheetName, 'A2', $rows);
        }

        return count($rows);
    }

    private function formatKelasNominasi($kategori, $kelas): string
    {
        if (strtoupper($kategori) === 'JUMBO') return 'JUMBO';
        if (strtoupper($kategori) === 'BONSAI') return '-';
        return $kelas ?? '-';
    }

    private function hitungFinalPoint(Ikan $ikan): float
    {
        $scorings = $ikan->scorings;
        $totalBonus = (int) $ikan->bonusPoints->sum('points');

        if ($scorings->isEmpty()) {
            return (float) $totalBonus;
        }

        // Hitung rata-rata nilai_detail dari semua juri
        $avgDetail = [];
        foreach ($scorings as $s) {
            if ($s->nilai_detail && is_array($s->nilai_detail)) {
                foreach ($s->nilai_detail as $kat => $fields) {
                    if (!is_array($fields)) continue;
                    foreach ($fields as $fid => $val) {
                        if (!isset($avgDetail[$kat][$fid])) {
                            $avgDetail[$kat][$fid] = ['sum' => 0, 'count' => 0];
                        }
                        $avgDetail[$kat][$fid]['sum'] += (float)($val ?? 0);
                        $avgDetail[$kat][$fid]['count']++;
                    }
                }
            }
        }

        $finalAvgDetail = [];
        foreach ($avgDetail as $kat => $fields) {
            $finalAvgDetail[$kat] = [];
            foreach ($fields as $fid => $d) {
                $finalAvgDetail[$kat][$fid] = $d['count'] > 0
                    ? $d['sum'] / $d['count']
                    : 0;
            }
        }

        $calculatedPoint = PointCalculator::hitungPoint($ikan->kategori, $finalAvgDetail);
        return (float) ($calculatedPoint + $totalBonus);
    }
}