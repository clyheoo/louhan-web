<?php

namespace App\Exports\Sheets;

use App\Support\PesertaRankingBuilder;
use App\Support\GridSheetLayout;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

/**
 * Daftar PEMENANG per kategori — TANPA point.
 * Kolom: NO TANK | JUARA | NAMA PESERTA (seperti Gambar 2).
 * Tata letak: blok kategori mengalir KE KANAN dulu, lalu turun (via GridSheetLayout).
 * Rentang juara diatur lewat constructor: (1,5) atau (6,10).
 */
class PemenangKategoriSheet implements FromArray, WithTitle, WithEvents
{
    private ?GridSheetLayout $grid = null;
    private int $from;
    private int $to;

    public function __construct(int $from = 1, int $to = 5)
    {
        $this->from = $from;
        $this->to   = $to;
    }

    public function title(): string
    {
        return 'PEMENANG ' . $this->from . '-' . $this->to;
    }

    public function array(): array
    {
        $all = PesertaRankingBuilder::build();

        // Kelompokkan per kategori + kelas, hanya juara dalam rentang band.
        $groups = [];
        foreach ($all as $p) {
            $juara = (int) ($p['juara'] ?? 0);
            if ($juara < $this->from || $juara > $this->to) {
                continue;
            }
            $groups[$p['kategori'] . ' - Kelas ' . $p['kelas']][] = $p;
        }
        ksort($groups);

        $blocks = [];
        foreach ($groups as $name => $items) {
            usort($items, fn ($a, $b) => (($a['juara'] ?: 9999) <=> ($b['juara'] ?: 9999)));

            $rows = [];
            foreach ($items as $p) {
                $rows[] = [
                    $p['nomor_tank'],
                    'Juara ' . $p['juara'],
                    $p['nama'],
                ];
            }

            $blocks[] = [
                'title'  => $name,
                'header' => ['NO TANK', 'JUARA', 'NAMA PESERTA'],
                'rows'   => $rows,
                'total'  => null,
                'cols'   => 3,
            ];
        }

        if (empty($blocks)) {
            return [['Belum ada pemenang pada rentang juara ' . $this->from . '-' . $this->to . '.']];
        }

        $this->grid = new GridSheetLayout($blocks, 3); // 3 blok ke kanan, lalu turun
        return $this->grid->toArray();
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                if (!$this->grid) {
                    return;
                }
                $this->grid->style($event->sheet->getDelegate(), [
                    'titleColor'  => '166534', // hijau tua — pembeda visual sheet pemenang
                    'headerColor' => '15803D',
                    'colWidths'   => [0 => 9, 1 => 11, 2 => 26],
                ]);
            },
        ];
    }
}