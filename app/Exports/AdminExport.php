<?php

namespace App\Exports;

use App\Exports\Sheets\AdminDaftarIkanSheet;
use App\Exports\Sheets\MvpIkanSheet;
use App\Exports\Sheets\PointRankingSheet;
use App\Exports\Sheets\RumusPenilaianSheet;
use App\Exports\Sheets\UserDetailSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

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
            $result[] = new MvpIkanSheet();
            $result[] = new PointRankingSheet($rankingScope);
            $result[] = new RumusPenilaianSheet();
            $result[] = new UserDetailSheet();
        } elseif ($this->sheets === 'daftar') {
            $result[] = new AdminDaftarIkanSheet();
        } elseif ($this->sheets === 'mvp') {
            $result[] = new MvpIkanSheet();
        } elseif ($this->sheets === 'ranking_kk') {
            $result[] = new PointRankingSheet('per_kategori_kelas');
        } elseif ($this->sheets === 'ranking_k') {
            $result[] = new PointRankingSheet('per_kategori');
        } elseif ($this->sheets === 'ranking_global') {
            $result[] = new PointRankingSheet('global');
        } elseif ($this->sheets === 'users') {
            $result[] = new UserDetailSheet();
        }

        return $result;
    }
}