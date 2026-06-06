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
            $ikan->nama_peserta ?? '',
            $ikan->kategori ?? '',
            $this->formatKelas($ikan->kategori, $ikan->kelas),
                ucfirst($ikan->jenis_keanggotaan ?? 'Team'),
                $ikan->detail_anggota ?? '',
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
                $ikan->nama_peserta ?? '',
                $ikan->kategori ?? '',
                $this->formatKelas($ikan->kategori, $ikan->kelas),
                ucfirst($ikan->jenis_keanggotaan ?? 'Team'),
                $ikan->detail_anggota ?? '',
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
            $ikan->nama_peserta ?? '',
            $ikan->kategori ?? '',
            $this->formatKelas($ikan->kategori, $ikan->kelas),
                ucfirst($ikan->jenis_keanggotaan ?? 'Team'),
                $ikan->detail_anggota ?? '',
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
            $batch[] = ['sheet' => $sheetName, 'cell' => 'C' . $actualRow, 'value' => $ikan->nama_peserta ?? ''];
            $batch[] = ['sheet' => $sheetName, 'cell' => 'D' . $actualRow, 'value' => $ikan->kategori ?? ''];
            $batch[] = ['sheet' => $sheetName, 'cell' => 'E' . $actualRow, 'value' => $this->formatKelas($ikan->kategori, $ikan->kelas)];
            $batch[] = ['sheet' => $sheetName, 'cell' => 'F' . $actualRow, 'value' => ucfirst($ikan->jenis_keanggotaan ?? 'Team')];
            $batch[] = ['sheet' => $sheetName, 'cell' => 'G' . $actualRow, 'value' => $ikan->detail_anggota ?? ''];
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
        try { $results['cnt'] = $this->syncNilaiJuri(); } catch (\Exception $e) { $results['cnt'] = 'Error: ' . $e->getMessage(); }
        try { $results['hasil_juri'] = $this->syncHasilJuri(); } catch (\Exception $e) { $results['hasil_juri'] = 'Error: ' . $e->getMessage(); }
        try { $results['hasil_nominasi'] = $this->syncHasilNominasi(); } catch (\Exception $e) { $results['hasil_nominasi'] = 'Error: ' . $e->getMessage(); }
        try { $results['nominasi_fix'] = $this->syncNominasiFix(); } catch (\Exception $e) { $results['nominasi_fix'] = 'Error: ' . $e->getMessage(); }
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

    private function buildHasilJuriFormulaMatrix(int $startRow = 5, int $endRow = 104, string $sep = ','): array
    {
        $matrix = [];

        $filterFormula = "=IFERROR(FILTER('NILAI JURI'!\$E\$2:\$E\$2001{$sep}"
                    . "'NILAI JURI'!\$B\$2:\$B\$2001=\$C\$2{$sep}"
                    . "(('NILAI JURI'!\$C\$2:\$C\$2001=\$A\$2)+(\$A\$2=\"(SEMUA)\")+(\$A\$2=\"\"))>0{$sep}"
                    . "(('NILAI JURI'!\$D\$2:\$D\$2001=\$B\$2)+(\$B\$2=\"(SEMUA)\")+(\$B\$2=\"\"))>0"
                    . "){$sep}\"\")";

        // ★ FORMAT BARU: [src_col, bobot_offset, sub_offset, isDefect, defect_src_col]
        //   defect_src_col = kolom NILAI JURI yang berisi % defect (utk dikalikan ke subcategory)
        $columnMap = [
            ['F',  2,  3,  false, null],  // OVERAL
            ['G',  4,  5,  false, 'I'],   // SIZE HEAD       — apply defect head
            ['H',  4,  6,  false, 'I'],   // BENTUK HEAD     — apply defect head
            ['I',  0,  0,  true,  null],  // DEFECT HEAD     → ditulis 0
            ['J',  7,  8,  false, 'K'],   // FACE            — apply defect face
            ['K',  0,  0,  true,  null],  // DEFECT FACE     → ditulis 0
            ['L',  9,  10, false, 'O'],   // BENTUK BODY     — apply defect body
            ['M',  9,  11, false, 'O'],   // PROPOSIONAL     — apply defect body
            ['N',  9,  12, false, 'O'],   // PANGKAL         — apply defect body
            ['O',  0,  0,  true,  null],  // DEFECT BODY     → ditulis 0
            ['P',  13, 14, false, null],  // FULLNESS MARKING
            ['Q',  13, 15, false, null],  // CONTRAST
            ['R',  13, 16, false, null],  // BENTUK MARKING
            ['S',  17, 18, false, null],  // SHINNING
            ['T',  17, 19, false, null],  // FULLNESS PEARL
            ['U',  17, 20, false, null],  // BENTUK PEARL
            ['V',  21, 22, false, null],  // KOMPOSISI
            ['W',  21, 23, false, null],  // KECERAHAN COLOUR
            ['X',  21, 24, false, null],  // FULLNESS COLOUR
            ['Y',  25, 26, false, 'AA'],  // BENTUK FINNAGE  — apply defect finnage
            ['Z',  25, 27, false, 'AA'],  // KECERAHAN FINNAGE — apply defect finnage
            ['AA', 0,  0,  true,  null],  // DEFECT FINNAGE  → ditulis 0
        ];

        for ($row = $startRow; $row <= $endRow; $row++) {
            $rowArr = [];
            $rowArr[] = ($row === $startRow) ? $filterFormula : null;

            $tankKategori = "INDEX('NILAI JURI'!\$C\$2:\$C\$2001{$sep}"
                        . "MATCH(\$A{$row}{$sep}'NILAI JURI'!\$E\$2:\$E\$2001{$sep}0))";

            foreach ($columnMap as [$njCol, $bobotOffset, $subOffset, $isDefect, $defectSrcCol]) {
                $sumNilai = "SUMPRODUCT("
                        . "('NILAI JURI'!\$E\$2:\$E\$2001=\$A{$row})*"
                        . "('NILAI JURI'!\$B\$2:\$B\$2001=\$C\$2)*"
                        . "'NILAI JURI'!\${$njCol}\$2:\${$njCol}\$2001)";
                if ($isDefect) {
                    // ★ DEFECT COLUMN: tampilkan decimal (0.10 / 0.30) — sesuai format NILAI JURI baru.
                    //   TOTAL HASIL formula user pakai (1 - DEFECT_value) → dengan decimal langsung benar.
                    $rowArr[] = "=IF(\$A{$row}=\"\"{$sep}\"\"{$sep}{$sumNilai})";
                } else {
                    // ★ SUBCATEGORY: TULIS RAW (tanpa apply defect).
                    //   Defect penalty diterapkan oleh formula TOTAL HASIL user — jangan double apply!
                    $bobot = "IFERROR(VLOOKUP({$tankKategori}{$sep}'RUMUS PENILAIAN'!\$A\$3:\$AA\$9{$sep}{$bobotOffset}{$sep}FALSE){$sep}0)";
                    $sub   = "IFERROR(VLOOKUP({$tankKategori}{$sep}'RUMUS PENILAIAN'!\$A\$3:\$AA\$9{$sep}{$subOffset}{$sep}FALSE){$sep}0)";
                    $rowArr[] = "=IF(\$A{$row}=\"\"{$sep}\"\"{$sep}({$sumNilai}*{$bobot}*{$sub}/100))";
                }
            }
            $matrix[] = $rowArr;
        }

        return $matrix;
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
        
        // ★ HYBRID: Gabungkan data terkini (Ikan/User) + data historis (Scoring)
        // sehingga dropdown menampilkan SEMUA opsi di website DAN di NILAI JURI

        // ★ Group by BOTH ikan_id + juri_id → semua juri per tank dipertahankan
        $latestIds = \App\Models\Scoring::select('ikan_id', 'juri_id', \DB::raw('MAX(id) as latest_id'))
            ->groupBy('ikan_id', 'juri_id')
            ->pluck('latest_id')
            ->toArray();

        $scoringsForDropdown = \App\Models\Scoring::whereIn('id', $latestIds)
            ->whereNotNull('juri_id')
            ->with(['ikan', 'juri'])
            ->get();

        // ── KATEGORI ──
        // Sumber 1: Data terkini dari tabel Ikan (website/database)
        $katFromIkan = Ikan::whereNotNull('nomor_tank')
            ->select('kategori')
            ->distinct()
            ->pluck('kategori')
            ->map(fn($k) => strtoupper($k))
            ->toArray();
        // Sumber 2: Data historis dari Scoring (bisa beda jika kategori pernah di-edit)
        $katFromScoring = $scoringsForDropdown->pluck('ikan.kategori')
            ->filter()
            ->map(fn($k) => strtoupper($k))
            ->toArray();
        // Gabung + dedup
        $kategoris = array_unique(array_merge($katFromIkan, $katFromScoring));
        sort($kategoris);

        // ── KELAS ──
        // Sumber 1: Data terkini dari tabel Ikan
        $kelasesFromIkan = Ikan::whereNotNull('nomor_tank')
            ->whereNotNull('kelas')
            ->select('kelas')
            ->distinct()
            ->pluck('kelas')
            ->toArray();
        if (!in_array('JUMBO', $kelasesFromIkan)) $kelasesFromIkan[] = 'JUMBO';
        // Sumber 2: Data historis dari Scoring (pakai logika sama persis syncNilaiJuri)
        $kelasesFromScoring = $scoringsForDropdown->map(function ($s) {
            return $s->kelas ?? ($s->ikan ? $s->ikan->kelas : null) ?? '-';
        })->toArray();
        // Gabung + dedup
        $kelases = array_unique(array_merge($kelasesFromIkan, $kelasesFromScoring));
        sort($kelases);

        // ── NAMA JURI ──
        // Sumber 1: Data terkini dari tabel User
        $juriFromUser = \App\Models\User::where('role', 'juri')
            ->orderBy('name')
            ->pluck('name')
            ->toArray();
        // Sumber 2: Data historis dari Scoring (termasuk juri yang sudah dihapus)
        $juriFromScoring = $scoringsForDropdown->pluck('juri.name')
            ->filter()
            ->toArray();
        // Gabung + dedup
        $namasJuri = array_unique(array_merge($juriFromUser, $juriFromScoring));
        sort($namasJuri);
        
// ★ Clear range dropdown source DULU sebelum tulis (urutan sengaja dibalik dari versi lama)
        $this->sheets->clear($sheetName, 'Z1:AB500');

// Kategori dropdown: "(SEMUA)" sebagai opsi pertama → wildcard
        $kategoriColumn = [['DAFTAR KATEGORI'], ['(SEMUA)']];
        foreach ($kategoris as $kat) {
            $kategoriColumn[] = [$kat];
        }
        $this->sheets->write($sheetName, 'Z1', $kategoriColumn);

        // Kelas dropdown: "(SEMUA)" sebagai opsi pertama → wildcard
        $kelasColumn = [['DAFTAR KELAS'], ['(SEMUA)']];
        foreach ($kelases as $kelas) {
            $kelasColumn[] = [$kelas];
        }
        $this->sheets->write($sheetName, 'AA1', $kelasColumn);

        // ★ Tulis kolom AB (nama juri)
        $juriColumn = [['DAFTAR NAMA JURI']];
        foreach ($namasJuri as $nama) {
            $juriColumn[] = [$nama];
        }
        $this->sheets->write($sheetName, 'AB1', $juriColumn);

    // ★ Set dropdown validation A2, B2, C2 SEKALIGUS dalam 1 API call
        //   (Sebelumnya 3 call terpisah → kena cURL timeout. 1 call = 1 round-trip.)
        try {
            $sheetId = $this->sheets->getSheetId($sheetName);
            if ($sheetId !== null) {
                $dropdownConfigs = [
                    ['col' => 0, 'source' => "'{$sheetName}'!Z2:Z"],   // A2 → kategori
                    ['col' => 1, 'source' => "'{$sheetName}'!AA2:AA"], // B2 → kelas
                    ['col' => 2, 'source' => "'{$sheetName}'!AB2:AB"], // C2 → nama juri
                ];

                $validationRequests = [];
                foreach ($dropdownConfigs as $cfg) {
                    $validationRequests[] = [
                        'setDataValidation' => [
                            'range' => [
                                'sheetId'          => $sheetId,
                                'startRowIndex'    => 1,
                                'endRowIndex'      => 2,
                                'startColumnIndex' => $cfg['col'],
                                'endColumnIndex'   => $cfg['col'] + 1,
                            ],
                            'rule' => [
                                'condition' => [
                                    'type'   => 'ONE_OF_RANGE',
                                    'values' => [
                                        ['userEnteredValue' => '=' . $cfg['source']],
                                    ],
                                ],
                                'showCustomUi' => true,
                                'strict'       => false,
                            ],
                        ],
                    ];
                }

                $result = $this->sheets->formatCells($validationRequests);
                Log::info('syncHasilJuri: setDataValidation batch result=' . var_export($result, true));
            } else {
                Log::warning('syncHasilJuri: getSheetId null untuk ' . $sheetName);
            }
        } catch (\Exception $e) {
            Log::warning('setDataValidation HASIL JURI gagal: ' . $e->getMessage());
        }

        // ★ Inisialisasi $batch kosong untuk dipakai oleh bagian penulisan nilai di bawah
        $batch = [];
        
// ═══════════════════════════════════════
        // BAGIAN 2: TULIS SUB-HEADER + FORMULA FILTER/SUMPRODUCT
        // Dropdown A2/B2/C2 → tabel auto re-hitung
        // ═══════════════════════════════════════

        // Sub-headers (baris 3 dan 4)
        $subHeaders = [
            'NO TANK', 'OVERAL', 'HEAD', '', 'DEEFCT',
            'FACE', 'DF FACE', 'BODY', '', '', 'DF BODY',
            'MARKING', '', '', 'PEARL', '', '',
            'COLOR', '', '', 'FINAGE', '', 'DF FINAGE'
        ];

        $subHeaders2 = [
            '', '', 'SIZE', 'BENTUK', '',
            '', '', 'BENTUK BADAN', 'PROPORSIONAL', 'PANGKAL', '',
            'FULLNES', 'CONTRAST', 'BENTUK',
            'SHINING', 'FULLNES', 'BENTUK',
            'KOMPOSISI', 'KECERAHAN', 'FULLNES',
            'BENTUK SIRIP', 'KECERAHAN', ''
        ];

// Auto-detect separator argumen (',' untuk en_US, ';' untuk id_ID)
        $sep = $this->sheets->getLocaleSeparator();
        
        // Generate formula matrix untuk A5:W104 (100 baris)
        $formulaMatrix = $this->buildHasilJuriFormulaMatrix(5, 104, $sep);

        // Gabung: row 3 = subHeaders, row 4 = subHeaders2, row 5+ = formulas
        $fullData = array_merge([$subHeaders, $subHeaders2], $formulaMatrix);

        // Clear seluruh area A3:W5000, lalu tulis sekaligus dalam 1 API call
        $this->sheets->clear($sheetName, 'A3:W5000');
        $writeResult = $this->sheets->write($sheetName, 'A3', $fullData);

        Log::info('syncHasilJuri: write formula result=' . var_export($writeResult, true));

        // Hitung jumlah scoring sebagai return value (info ke user)
        $totalScorings = \App\Models\Scoring::whereNotNull('nilai_detail')
            ->whereNotNull('juri_id')
            ->count();

        return $totalScorings;
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
                $ikan->nama_peserta ?? '',
                strtoupper($ikan->kategori ?? ''),
                $this->formatKelasNominasi($ikan->kategori, $ikan->kelas),
                ucfirst($ikan->jenis_keanggotaan ?? 'Team'),
                $ikan->detail_anggota ?? '',
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

        // ★ Clear + unmerge seluruh area kerja
        $this->sheets->clear($sheetName, 'A1:W1000');
        if ($sheetId !== null) {
            try {
                $this->sheets->formatCells([[
                    'unmergeCells' => [
                        'range' => [
                            'sheetId'          => $sheetId,
                            'startRowIndex'    => 0,
                            'endRowIndex'      => 1000,
                            'startColumnIndex' => 0,
                            'endColumnIndex'   => 23, // A-W
                        ]
                    ]
                ]]);
            } catch (\Exception $e) {
                Log::warning('Unmerge MVP gagal (tidak kritis): ' . $e->getMessage());
            }
        }

        if ($ikans->isEmpty()) return 0;

        // ─── Konfigurasi Layout ───
        $COLS_PER_TABLE = 5;
        $COL_GAP        = 1;
        $TABLES_PER_ROW = 4;

        $tableColStarts = [];
        $col = 0;
        for ($i = 0; $i < $TABLES_PER_ROW; $i++) {
            $tableColStarts[] = $col;
            $col += $COLS_PER_TABLE + $COL_GAP;
        }

        // ★ GROUP BY detail_anggota (Kota / Team)
        $groups = $ikans->groupBy(function ($ikan) {
            $key = trim($ikan->detail_anggota ?? '');
            return $key === '' ? '(Tanpa Kota/Team)' : $key;
        });

        // ─── Siapkan Data Per Kota/Team ───
        $teamData = [];
        foreach ($groups as $detailAnggota => $items) {
            // ★ PREFIX HEADER berdasarkan jenis_keanggotaan
            $jenis  = strtolower(trim($items->first()->jenis_keanggotaan ?? 'perorangan'));
            $prefix = ($jenis === 'team') ? 'Team/Club' : 'Kota';
            $headerText = $prefix . ' - ' . $detailAnggota;

            $rows = [];
            $totalPoint = 0;
            $no = 1;

            $sorted = $items->sortBy(function ($ikan) {
                return $ikan->nomor_tank ?? 99999;
            });

            foreach ($sorted as $ikan) {
                $point = $this->hitungFinalPoint($ikan);
                $totalPoint += $point;

                $rows[] = [
                    $no,
                    $ikan->nama_peserta ?? '—',
                    strtoupper($ikan->kategori ?? ''),
                    $ikan->nomor_tank ?? '',
                    $point,
                ];
                $no++;
            }

            $tableHeight = 2 + count($rows) + 1;

            $teamData[] = [
                'header'     => $headerText,
                'rows'       => $rows,
                'totalPoint' => $totalPoint,
                'height'     => $tableHeight,
            ];
        }

        usort($teamData, function ($a, $b) {
            return strcmp($a['header'], $b['header']);
        });

        // ─── PHASE 1: Build batch write + format requests ───
        $batch          = [];
        $formatRequests = [];
        $mergeRequests  = [];

        // Track posisi baris setiap team untuk merge nanti
        $teamRowMap = []; // [teamIndex => startRow]

        $currentRow = 1;
        $teamIndex  = 0;
        $totalTeam  = count($teamData);

        while ($teamIndex < $totalTeam) {
            $rowTeams  = [];
            $maxHeight = 0;

            for ($i = 0; $i < $TABLES_PER_ROW && $teamIndex < $totalTeam; $i++) {
                $rowTeams[] = [
                    'data'     => $teamData[$teamIndex],
                    'colStart' => $tableColStarts[$i],
                    'index'    => $teamIndex,
                ];
                $teamRowMap[$teamIndex] = $currentRow; // ★ catat row header
                $maxHeight = max($maxHeight, $teamData[$teamIndex]['height']);
                $teamIndex++;
            }

            foreach ($rowTeams as $rt) {
                $data = $rt['data'];
                $cs   = $rt['colStart'];

                // ── Baris 1: Header (tulis di kolom pertama dari blok)
                $batch[] = [
                    'sheet' => $sheetName,
                    'cell'  => $this->sheets->colToLetter($cs + 1) . $currentRow,
                    'value' => $data['header'],
                ];

                // ── Baris 2: Sub-Header
                $subRow = $currentRow + 1;
                $subHeaders = ['NO', 'NAMA PESERTA', 'KATEGORI', 'NO TANK', 'POINT'];
                foreach ($subHeaders as $ci => $val) {
                    $batch[] = [
                        'sheet' => $sheetName,
                        'cell'  => $this->sheets->colToLetter($cs + $ci + 1) . $subRow,
                        'value' => $val,
                    ];
                }

                // ── Baris 3+: Data Ikan
                $dataRow = $subRow + 1;
                foreach ($data['rows'] as $row) {
                    foreach ($row as $ci => $val) {
                        $batch[] = [
                            'sheet' => $sheetName,
                            'cell'  => $this->sheets->colToLetter($cs + $ci + 1) . $dataRow,
                            'value' => $val,
                        ];
                    }
                    $dataRow++;
                }

                // ── Baris TOTAL
                $batch[] = [
                    'sheet' => $sheetName,
                    'cell'  => $this->sheets->colToLetter($cs + 3) . $dataRow,
                    'value' => 'TOTAL',
                ];
                $batch[] = [
                    'sheet' => $sheetName,
                    'cell'  => $this->sheets->colToLetter($cs + 5) . $dataRow,
                    'value' => (int) $data['totalPoint'],
                ];

                // ── Format: sub-header (background abu, bold)
                if ($sheetId !== null) {
                    $formatRequests[] = [
                        'repeatCell' => [
                            'range' => [
                                'sheetId'          => $sheetId,
                                'startRowIndex'    => $subRow - 1,
                                'endRowIndex'      => $subRow,
                                'startColumnIndex' => $cs,
                                'endColumnIndex'   => $cs + $COLS_PER_TABLE,
                            ],
                            'cell' => [
                                'userEnteredFormat' => [
                                    'horizontalAlignment' => 'CENTER',
                                    'backgroundColor'     => ['red' => 0.95, 'green' => 0.95, 'blue' => 0.95],
                                    'textFormat' => ['bold' => true, 'fontSize' => 10],
                                ],
                            ],
                            'fields' => 'userEnteredFormat(horizontalAlignment,backgroundColor,textFormat)',
                        ],
                    ];

                    // ── Format: TOTAL row (background krem, bold)
                    $formatRequests[] = [
                        'repeatCell' => [
                            'range' => [
                                'sheetId'          => $sheetId,
                                'startRowIndex'    => $dataRow - 1,
                                'endRowIndex'      => $dataRow,
                                'startColumnIndex' => $cs,
                                'endColumnIndex'   => $cs + $COLS_PER_TABLE,
                            ],
                            'cell' => [
                                'userEnteredFormat' => [
                                    'backgroundColor' => ['red' => 0.95, 'green' => 0.92, 'blue' => 0.85],
                                    'textFormat' => ['bold' => true],
                                ],
                            ],
                            'fields' => 'userEnteredFormat(backgroundColor,textFormat)',
                        ],
                    ];
                }
            }

            $currentRow += $maxHeight + 1;
        }

        // ─── PHASE 2: Eksekusi batch (tulis data) ───
        if (!empty($batch)) {
            foreach (array_chunk($batch, 500) as $chunk) {
                $this->sheets->batchUpdate($chunk);
            }
        }

        // ─── PHASE 3: Apply format requests (sub-header & TOTAL) ───
        if (!empty($formatRequests) && $sheetId !== null) {
            foreach (array_chunk($formatRequests, 50) as $chunk) {
                try {
                    $this->sheets->formatCells($chunk);
                } catch (\Exception $e) {
                    Log::warning('Format MVP gagal (tidak kritis): ' . $e->getMessage());
                }
            }
        }

        // ─── PHASE 4: MERGE HEADER per team (pakai teamRowMap yg benar) ───
        if ($sheetId !== null) {
            foreach ($teamData as $idx => $data) {
                $headerRow = $teamRowMap[$idx];
                $cs = $tableColStarts[$idx % $TABLES_PER_ROW];

                $mergeRequests[] = [
                    'mergeCells' => [
                        'range' => [
                            'sheetId'          => $sheetId,
                            'startRowIndex'    => $headerRow - 1,
                            'endRowIndex'      => $headerRow,
                            'startColumnIndex' => $cs,
                            'endColumnIndex'   => $cs + $COLS_PER_TABLE,
                        ],
                        'mergeType' => 'MERGE_ALL',
                    ],
                ];
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

        // ─── PHASE 5: APPLY CENTER ALIGNMENT KE HEADER (SETELAH merge) ───
        // ★ Crucial: format diapply SETELAH merge supaya merged cell punya center alignment
        if ($sheetId !== null) {
            $headerFormatRequests = [];
            foreach ($teamData as $idx => $data) {
                $headerRow = $teamRowMap[$idx];
                $cs = $tableColStarts[$idx % $TABLES_PER_ROW];

                $headerFormatRequests[] = [
                    'repeatCell' => [
                        'range' => [
                            'sheetId'          => $sheetId,
                            'startRowIndex'    => $headerRow - 1,
                            'endRowIndex'      => $headerRow,
                            'startColumnIndex' => $cs,
                            'endColumnIndex'   => $cs + $COLS_PER_TABLE,
                        ],
                        'cell' => [
                            'userEnteredFormat' => [
                                'horizontalAlignment' => 'CENTER',
                                'verticalAlignment'   => 'MIDDLE',
                                'backgroundColor'     => ['red' => 0.85, 'green' => 0.92, 'blue' => 1.0],
                                'textFormat' => [
                                    'bold'     => true,
                                    'fontSize' => 11,
                                ],
                            ],
                        ],
                        'fields' => 'userEnteredFormat(horizontalAlignment,verticalAlignment,backgroundColor,textFormat)',
                    ],
                ];
            }

            if (!empty($headerFormatRequests)) {
                try {
                    foreach (array_chunk($headerFormatRequests, 50) as $chunk) {
                        $this->sheets->formatCells($chunk);
                    }
                } catch (\Exception $e) {
                    Log::warning('Format header MVP gagal (tidak kritis): ' . $e->getMessage());
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

        // Ambil 1 scoring terbaru per ikan PER JURI (agar semua juri masuk ke sheet)
        $latestIds = \App\Models\Scoring::select('ikan_id', 'juri_id', \DB::raw('MAX(id) as latest_id'))
            ->groupBy('ikan_id', 'juri_id')
            ->pluck('latest_id')
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
                if ($val === '0' || $val === '' || $val === null) return 0;
                // ★ KEMBALIKAN DECIMAL (0.10 / 0.30) supaya formula TOTAL HASIL (1-DEFECT) benar.
                $intVal = (int) str_replace('%', '', $val); // 10 atau 30
                return $intVal / 100; // 0.10 atau 0.30
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
                $nd['body']['proporsi'] ?? 0,
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
                if ($val === '0' || $val === '' || $val === null) return 0;
                $intVal = (int) str_replace('%', '', $val); // 10 atau 30
                return $intVal / 100; // 0.10 atau 0.30 (decimal)
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
                $nd['body']['proporsi'] ?? 0,                                           // M
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

        // ★ Ambil defect data: prioritas Grand Juri edit, fallback ke scoring terbaru
        $mergedDefect = [
            'raw_head_penalty'    => ['0'],
            'raw_face_penalty'    => ['0'],
            'raw_body_penalty'    => ['0'],
            'raw_finnage_penalty' => ['0'],
        ];
        $grandEdited = $scorings->first(function ($s) { return $s->edited_by_grand_juri; });
        $defectSource = $grandEdited ?: $scorings->sortByDesc('updated_at')->first();
        if ($defectSource) {
            $mergedDefect['raw_head_penalty']    = $defectSource->raw_head_penalty    ?: ['0'];
            $mergedDefect['raw_face_penalty']    = $defectSource->raw_face_penalty    ?: ['0'];
            $mergedDefect['raw_body_penalty']    = $defectSource->raw_body_penalty    ?: ['0'];
            $mergedDefect['raw_finnage_penalty'] = $defectSource->raw_finnage_penalty ?: ['0'];
        }

        $calculatedPoint = PointCalculator::hitungPoint($ikan->kategori, $finalAvgDetail, $mergedDefect);
        return (float) ($calculatedPoint + $totalBonus);
    }
}