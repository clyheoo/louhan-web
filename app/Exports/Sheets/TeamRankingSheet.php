<?php

namespace App\Exports\Sheets;

use App\Support\PesertaRankingBuilder;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use App\Support\GridSheetLayout;

class TeamRankingSheet implements FromArray, WithTitle, WithEvents
{
    private ?GridSheetLayout $grid = null;

    public function title(): string { return 'JUARA TEAM'; }

    public function array(): array
    {
        $all = PesertaRankingBuilder::build();

        $teamItems = array_filter($all, fn ($p) =>
            $p['jenis'] === 'team' && !in_array($p['team'], ['', '—', '-'], true)
        );

        $teams = [];
        foreach ($teamItems as $p) $teams[$p['team']][] = $p;

        $totals = [];
        foreach ($teams as $name => $items) $totals[$name] = array_sum(array_column($items, 'rank_point'));
        arsort($totals);

        $blocks = [];
        foreach (array_keys($totals) as $name) {
            $items = $teams[$name];
            usort($items, fn ($a, $b) =>
                [$a['kategori'], $a['kelas'], $a['juara']] <=> [$b['kategori'], $b['kelas'], $b['juara']]
            );

            $rows = []; $no = 1; $sumBonus = 0; $sumPoint = 0; $sumRank = 0;
            foreach ($items as $p) {
                $kat   = $p['kategori'] . (($p['kelas'] && $p['kelas'] !== '—') ? ' ' . $p['kelas'] : '');
                $juara = ($p['juara'] >= 1 && $p['juara'] <= 10) ? 'Juara ' . $p['juara'] : '-';
                $tp = round($p['total_point'], 2);
                $rows[] = [$no++, $p['nama'], $kat, $p['nomor_tank'], $juara, $p['bonus'], $tp, $p['rank_point']];
                $sumBonus += $p['bonus']; $sumPoint += $tp; $sumRank += $p['rank_point'];
            }

            $blocks[] = [
                'title'  => 'Team/Club - ' . $name,
                'header' => ['NO', 'NAMA PESERTA', 'KATEGORI', 'NO TANK', 'JUARA', 'BONUS', 'TOTAL POINT', 'RANK POINT'],
                'rows'   => $rows,
                'total'  => ['TOTAL RANK POINT', '', '', '', '', $sumBonus, round($sumPoint, 2), $sumRank],
                'cols'   => 8,
            ];
        }

        if (empty($blocks)) return [['Belum ada peserta dengan keanggotaan team.']];

        $this->grid = new GridSheetLayout($blocks, 2); // 2 tabel ke kanan, lalu turun
        return $this->grid->toArray();
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                if (!$this->grid) return;
                $this->grid->style($event->sheet->getDelegate(), [
                    'colWidths'     => [0=>5, 1=>22, 2=>18, 3=>9, 4=>11, 5=>9, 6=>12, 7=>12],
                    'pointColIndex' => 6,
                    'rankColIndex'  => 7,
                    'totalMergeTo'  => 4,
                ]);
            },
        ];
    }
}