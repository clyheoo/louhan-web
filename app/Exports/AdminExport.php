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
            $result[] = new MvpIkanSheet();
            $result[] = new PointRankingSheet($rankingScope);
            $result[] = new RumusPenilaianSheet();
            $result[] = new NominasiSheet();
            $result[] = new NilaiMurniJuriSheet();
        } elseif ($this->sheets === 'daftar') {
            $result[] = new AdminDaftarIkanSheet();
        } elseif ($this->sheets === 'users') {
            $result[] = new AdminUserDetailSheet();
        } elseif ($this->sheets === 'mvp') {
            $result[] = new MvpIkanSheet();
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
        }

        return $result;
    }
}