<?php

namespace App\Exports;

use App\Exports\Sheets\AdminDaftarIkanSheet;
use App\Exports\Sheets\AdminUserDetailSheet;
use App\Exports\Sheets\MvpIkanSheet;
use App\Exports\Sheets\PointRankingSheet;
use App\Exports\Sheets\RumusPenilaianSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\Sheets\NominasiSheet;
use App\Exports\Sheets\JuriAssignmentSheet;
use App\Exports\Sheets\NilaiMurniJuriSheet;
use App\Exports\Sheets\TeamChampionSheet;
use App\Exports\Sheets\PointRankingSubtotalSheet;
use App\Exports\Sheets\IndividualRankingSheet;
use App\Exports\Sheets\TeamRankingSheet;
use App\Exports\Sheets\PemenangKategoriSheet;
use App\Exports\Sheets\PemenangKategoriPointSheet;

class AdminExport implements WithMultipleSheets
{
    private $sheets;

    public function __construct($sheets = 'all')
    {
        $this->sheets = $sheets;
    }

public function sheets(): array
    {
        // 'all' = ekspor lengkap (perilaku lama dipertahankan persis)
        if ($this->sheets === 'all' || $this->sheets === ['all']) {
            return $this->allSheets();
        }

        // Terima string ber-koma ("daftar,mvp,ranking_kk") MAUPUN array
        $keys = is_array($this->sheets)
            ? $this->sheets
            : explode(',', (string) $this->sheets);

        $keys = array_values(array_unique(array_filter(
            array_map('trim', $keys),
            fn ($k) => $k !== ''
        )));

        // Jika salah satu key 'all', langsung ekspor lengkap saja
        if (in_array('all', $keys, true)) {
            return $this->allSheets();
        }

        $result = [];
        foreach ($keys as $key) {
            foreach ($this->sheetsForKey($key) as $sheet) {
                $result[] = $sheet;
            }
        }

        // Fallback aman: tak ada key valid -> ekspor lengkap (perilaku lama)
        return $result ?: $this->allSheets();
    }

    /**
     * Susunan sheet "Semua Data" — sama persis dengan versi lama.
     */
    private function allSheets(): array
    {
        $rankingScope = 'per_kategori_kelas';

        return [
            new AdminDaftarIkanSheet(),
            new IndividualRankingSheet(),
            new TeamRankingSheet(),
            new AdminUserDetailSheet(),
            new TeamChampionSheet(),
            new MvpIkanSheet('team'),
            new MvpIkanSheet('perorangan'),
            new PointRankingSheet($rankingScope),
            new RumusPenilaianSheet(),
            new NominasiSheet(),
            new JuriAssignmentSheet(),
            new NilaiMurniJuriSheet(),
            new PointRankingSubtotalSheet($rankingScope),
            new PemenangKategoriPointSheet(1, 5),
            new PemenangKategoriPointSheet(6, 10),
            new PemenangKategoriSheet(1, 5),
            new PemenangKategoriSheet(6, 10),
            new PemenangKategoriPointSheet(1, 10),
            new PemenangKategoriSheet(1, 10),
        ];
    }

    /**
     * Pemetaan 1 key -> daftar sheet-nya (dipakai single & multi-select).
     * Isinya identik dengan cabang if/elseif versi lama.
     */
    private function sheetsForKey(string $key): array
    {
        return match ($key) {
            'daftar'                  => [new AdminDaftarIkanSheet()],
            'users'                   => [new AdminUserDetailSheet()],
            'team_champion'           => [new TeamChampionSheet()],
            'mvp'                     => [new MvpIkanSheet('team'), new MvpIkanSheet('perorangan')],
            'ranking_kk'              => [new PointRankingSheet('per_kategori_kelas')],
            'ranking_k'               => [new PointRankingSheet('per_kategori')],
            'ranking_global'          => [new PointRankingSheet('global')],
            'nominasi'                => [new NominasiSheet()],
            'nilai_murni'             => [new NilaiMurniJuriSheet()],
            'juri_assignment'         => [new JuriAssignmentSheet()],
            'rumus'                   => [new RumusPenilaianSheet()],
            'ranking_subtotal_global' => [new PointRankingSubtotalSheet('global')],
            'ranking_subtotal_kk'     => [new PointRankingSubtotalSheet('per_kategori_kelas')],
            'ranking_subtotal_k'      => [new PointRankingSubtotalSheet('per_kategori')],
            'juara_peserta'           => [new IndividualRankingSheet()],
            'juara_team'              => [new TeamRankingSheet()],
            'pemenang_point_1_5'      => [new PemenangKategoriPointSheet(1, 5)],
            'pemenang_point_6_10'     => [new PemenangKategoriPointSheet(6, 10)],
            'pemenang_1_5'            => [new PemenangKategoriSheet(1, 5)],
            'pemenang_6_10'           => [new PemenangKategoriSheet(6, 10)],
            'pemenang_point_1_10'     => [new PemenangKategoriPointSheet(1, 10)],
            'pemenang_1_10'           => [new PemenangKategoriSheet(1, 10)],
            default                   => [],
        };
    }
}