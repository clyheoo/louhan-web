<?php

namespace App\Exports;

use App\Exports\Sheets\AdminDaftarIkanSheet;
use App\Exports\Sheets\AdminUserDetailSheet;
use App\Exports\Sheets\MvpIkanSheet;
use App\Exports\Sheets\PointRankingSheet;
use App\Exports\Sheets\RumusPenilaianSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\Sheets\NominasiSheet;
use App\Exports\Sheets\NilaiMurniJuriSheet;
use App\Exports\Sheets\TeamChampionSheet;
use App\Exports\Sheets\PointRankingSubtotalSheet;

class AdminExport implements WithMultipleSheets
{
    private $sheets;

    public function __construct($sheets = 'all')
    {
        $this->sheets = $sheets;
    }

    public function sheets(): array
    {
        $result = [];
        $rankingScope = 'per_kategori_kelas';

        if ($this->sheets === 'all') {
            $result[] = new AdminDaftarIkanSheet();
            $result[] = new AdminUserDetailSheet();
            $result[] = new TeamChampionSheet();
            $result[] = new MvpIkanSheet('team');
            $result[] = new MvpIkanSheet('perorangan');
            $result[] = new PointRankingSheet($rankingScope);
            $result[] = new RumusPenilaianSheet();
            $result[] = new NominasiSheet();
            $result[] = new NilaiMurniJuriSheet();
            $result[] = new PointRankingSubtotalSheet($rankingScope);
        } elseif ($this->sheets === 'daftar') {
            $result[] = new AdminDaftarIkanSheet();
        } elseif ($this->sheets === 'users') {
            $result[] = new AdminUserDetailSheet();
        } elseif ($this->sheets === 'team_champion') {
            $result[] = new TeamChampionSheet();
        } elseif ($this->sheets === 'mvp') {
            $result[] = new MvpIkanSheet('team');
            $result[] = new MvpIkanSheet('perorangan');
        } elseif ($this->sheets === 'ranking_kk') {
            $result[] = new PointRankingSheet('per_kategori_kelas');
        } elseif ($this->sheets === 'ranking_k') {
            $result[] = new PointRankingSheet('per_kategori');
        } elseif ($this->sheets === 'ranking_global') {
            $result[] = new PointRankingSheet('global');
        } elseif ($this->sheets === 'nominasi') {
            $result[] = new NominasiSheet();
        } elseif ($this->sheets === 'nilai_murni') {
            $result[] = new NilaiMurniJuriSheet();
        } elseif ($this->sheets === 'ranking_subtotal_global') {
            $result[] = new PointRankingSubtotalSheet('global');
        } elseif ($this->sheets === 'ranking_subtotal_kk') {
            $result[] = new PointRankingSubtotalSheet('per_kategori_kelas');
        } elseif ($this->sheets === 'ranking_subtotal_k') {
            $result[] = new PointRankingSubtotalSheet('per_kategori');
        } elseif ($this->sheets === 'ranking_subtotal_global') {
            $result[] = new PointRankingSubtotalSheet('global');
        }


        return $result;
    }
}