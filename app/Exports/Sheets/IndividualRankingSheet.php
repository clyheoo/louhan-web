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

class IndividualRankingSheet implements FromArray, WithTitle, WithEvents
{
    private ?GridSheetLayout $grid = null;

    public function title(): string { return 'HASIL PESERTA'; }

    public function array(): array
    {
        $all = PesertaRankingBuilder::build();

        $groups = [];
        foreach ($all as $p) $groups[$p['kategori'] . ' - Kelas ' . $p['kelas']][] = $p;
        ksort($groups);

        $blocks = [];
        foreach ($groups as $name => $items) {
            usort($items, fn ($a, $b) => ($a['juara'] ?: 9999) <=> ($b['juara'] ?: 9999));

            $rows = []; $no = 1; $sumBonus = 0; $sumPoint = 0; $sumRank = 0;
            foreach ($items as $p) {
                $juara = ($p['juara'] >= 1 && $p['juara'] <= 10) ? 'Juara ' . $p['juara'] : '-';
                $tp = round($p['total_point'], 2);
                $rows[] = [$no++, $p['nama'], $p['nomor_tank'], $juara, $p['bonus'], $tp, $p['rank_point']];
                $sumBonus += $p['bonus']; $sumPoint += $tp; $sumRank += $p['rank_point'];
            }

            $blocks[] = [
                'title'  => $name . ' (' . count($items) . ' peserta)',
                'header' => ['NO', 'NAMA PESERTA', 'NO TANK', 'JUARA', 'BONUS', 'TOTAL POINT', 'RANK POINT'],
                'rows'   => $rows,
                'total'  => ['TOTAL', '', '', '', $sumBonus, round($sumPoint, 2), $sumRank],
                'cols'   => 7,
            ];
        }

        if (empty($blocks)) return [['Belum ada peserta yang dinilai.']];

        $this->grid = new GridSheetLayout($blocks, 3); // 3 tabel ke kanan, lalu turun
        return $this->grid->toArray();
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                if (!$this->grid) return;
                $this->grid->style($event->sheet->getDelegate(), [
                    'colWidths'     => [0=>5, 1=>22, 2=>9, 3=>11, 4=>9, 5=>12, 6=>12],
                    'pointColIndex' => 5,
                    'rankColIndex'  => 6,
                    'totalMergeTo'  => 3,
                ]);
            },
        ];
    }
}