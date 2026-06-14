<?php

namespace App\Exports\Sheets;

use App\Support\PesertaRankingBuilder;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class TeamRankingSheet implements FromArray, WithTitle, WithEvents
{
    public function title(): string { return 'JUARA TEAM'; }

    public function array(): array
    {
        $all = PesertaRankingBuilder::build();

        $teamItems = array_filter($all, fn ($p) =>
            $p['jenis'] === 'team' && !in_array($p['team'], ['', '—', '-'], true)
        );

        $teams = [];
        foreach ($teamItems as $p) $teams[$p['team']][] = $p;

        // Urutkan team berdasarkan total rank point tertinggi
        $totals = [];
        foreach ($teams as $name => $items) $totals[$name] = array_sum(array_column($items, 'rank_point'));
        arsort($totals);

        $rows = [];
        foreach (array_keys($totals) as $name) {
            $items = $teams[$name];
            usort($items, fn ($a, $b) =>
                [$a['kategori'], $a['kelas'], $a['juara']] <=> [$b['kategori'], $b['kelas'], $b['juara']]
            );

            $rows[] = ['§TITLE§Team/Club - ' . $name, '', '', '', '', '', ''];
            $rows[] = ['NO', 'NAMA PESERTA', 'KATEGORI', 'NO TANK', 'JUARA', 'BONUS', 'RANK POINT'];

            $no = 1; $totBonus = 0; $totRank = 0;
            foreach ($items as $p) {
                $kat   = $p['kategori'] . (($p['kelas'] && $p['kelas'] !== '—') ? ' ' . $p['kelas'] : '');
                $juara = ($p['juara'] >= 1 && $p['juara'] <= 10) ? 'Juara ' . $p['juara'] : '-';
                $rows[] = [$no++, $p['nama'], $kat, $p['nomor_tank'], $juara, $p['bonus'], $p['rank_point']];
                $totBonus += $p['bonus'];
                $totRank  += $p['rank_point'];
            }
            $rows[] = ['§TOTAL§', '', '', '', '', $totBonus, $totRank];
            $rows[] = ['', '', '', '', '', '', ''];
        }

        if (empty($rows)) $rows[] = ['Belum ada data peserta dengan keanggotaan team.', '', '', '', '', '', ''];

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();

                $widths = ['A'=>5,'B'=>26,'C'=>20,'D'=>10,'E'=>12,'F'=>10,'G'=>14];
                foreach ($widths as $col => $w) $sheet->getColumnDimension($col)->setAutoSize(false)->setWidth($w);

                for ($r = 1; $r <= $lastRow; $r++) {
                    $a = (string) $sheet->getCell("A{$r}")->getValue();

                    if (str_starts_with($a, '§TITLE§')) {
                        $sheet->setCellValue("A{$r}", substr($a, strlen('§TITLE§')));
                        $sheet->mergeCells("A{$r}:G{$r}");
                        $sheet->getStyle("A{$r}:G{$r}")->applyFromArray([
                            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '6D28D9']],
                            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                        ]);
                        $sheet->getRowDimension($r)->setRowHeight(22);
                    } elseif ($a === 'NO') {
                        $sheet->getStyle("A{$r}:G{$r}")->applyFromArray([
                            'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '1E40AF']],
                            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'BFDBFE']]],
                        ]);
                    } elseif (str_starts_with($a, '§TOTAL§')) {
                        $sheet->setCellValue("A{$r}", 'TOTAL RANK POINT');
                        $sheet->mergeCells("A{$r}:E{$r}");
                        $sheet->getStyle("A{$r}:G{$r}")->applyFromArray([
                            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '92400E']],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FEF3C7']],
                            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FDE68A']]],
                        ]);
                        $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal('right')->setVertical('center');
                        $sheet->getStyle("F{$r}:G{$r}")->getAlignment()->setHorizontal('center')->setVertical('center');
                    } elseif ($a !== '' && is_numeric($a)) {
                        $sheet->getStyle("A{$r}:G{$r}")->applyFromArray([
                            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDD6FE']]],
                        ]);
                        $sheet->getStyle("B{$r}:C{$r}")->getAlignment()->setHorizontal('left');
                        $sheet->getStyle("G{$r}")->applyFromArray([
                            'font' => ['bold' => true],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FEF9C3']],
                        ]);
                    }
                }
            },
        ];
    }
}