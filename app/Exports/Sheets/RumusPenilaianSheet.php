<?php

namespace App\Exports\Sheets;

use App\Models\ScoringPointConfig;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class RumusPenilaianSheet implements FromArray, WithTitle, WithStyles, ShouldAutoSize
{
    public function title(): string
    {
        return 'RUMUS PENILAIAN';
    }

    public function array(): array
    {
        $configs = ScoringPointConfig::orderBy('kategori')->get();

        $rows = [];

        // Section 1: Bobot per Kategori
        $rows[] = ['BOBOT PENILAIAN PER KATEGORI'];
        $rows[] = ['KATEGORI', 'OVERALL', 'HEAD', 'FACE', 'BODY SHAPE', 'MARKING', 'PEARL', 'COLOUR', 'FINNAGE', 'TOTAL BOBOT'];
        foreach ($configs as $cfg) {
            $total = (float)$cfg->overall_bobot + (float)$cfg->head_bobot + (float)$cfg->face_bobot
                   + (float)$cfg->body_bobot + (float)$cfg->marking_bobot + (float)$cfg->pearl_bobot
                   + (float)$cfg->color_bobot + (float)$cfg->finnage_bobot;
            $rows[] = [
                strtoupper($cfg->kategori),
                (float)$cfg->overall_bobot,
                (float)$cfg->head_bobot,
                (float)$cfg->face_bobot,
                (float)$cfg->body_bobot,
                (float)$cfg->marking_bobot,
                (float)$cfg->pearl_bobot,
                (float)$cfg->color_bobot,
                (float)$cfg->finnage_bobot,
                round($total, 2),
            ];
        }

        $rows[] = [];
        $rows[] = [];

        // Section 2: Nilai Maks per Komponen
        $rows[] = ['NILAI MAKSIMUM PER KOMPONEN'];
        $rows[] = ['KATEGORI KOMPONEN', 'KOMPONEN', 'NILAI MAKS'];

        $maxDefs = [
            ['OVERALL',  'Impression',   100],
            ['HEAD',     'Size',          60],
            ['HEAD',     'Bentuk Kepala', 40],
            ['FACE',     'Pipi',          25],
            ['FACE',     'Mata',          25],
            ['FACE',     'Bibir',         25],
            ['FACE',     'Kondisi',       25],
            ['BODY',     'Bentuk Badan',  50],
            ['BODY',     'Proporsional',  40],
            ['BODY',     'Pangkal',       10],
            ['MARKING',  'Fullness',      40],
            ['MARKING',  'Contrast',      40],
            ['MARKING',  'Bentuk',        20],
            ['PEARL',    'Shining',       45],
            ['PEARL',    'Fullness',      35],
            ['PEARL',    'Bentuk',        20],
            ['COLOUR',   'Komposisi',     45],
            ['COLOUR',   'Kecerahan',     35],
            ['COLOUR',   'Fullness',      20],
            ['FINNAGE',  'Bentuk Sirip',  75],
            ['FINNAGE',  'Kecerahan',     25],
        ];

        foreach ($maxDefs as $def) {
            $rows[] = $def;
        }

        $rows[] = [];
        $rows[] = [];

        // Section 3: Rumus Perhitungan
        $rows[] = ['RUMUS PERHITUNGAN POINT'];
        $rows[] = ['LANGKAH', 'RUMUS', 'KETERANGAN'];
        $rows[] = ['1. Rata-rata', '(Nilai Juri 1 + Nilai Juri 2 + ...) / Jumlah Juri', 'Per komponen, dirata-rata dari semua juri yang menilai'];
        $rows[] = ['2. Persentase', 'Rata-rata / Nilai Maks × 100%', 'Mengkonversi ke skala 0-100%'];
        $rows[] = ['3. Point Komponen', 'Persentase × Bobot Kategori / 100', 'Setiap komponen dikali bobot kategorinya'];
        $rows[] = ['4. Subtotal Kategori', 'Jumlah Point semua komponen dalam 1 kategori', 'Misal: Head = Point Size + Point Bentuk Kepala'];
        $rows[] = ['5. Total Point', 'Jumlah semua Subtotal Kategori', 'Overall + Head + Face + Body + Marking + Pearl + Colour + Finnage'];
        $rows[] = ['6. Final Point', 'Total Point + Total Bonus', 'Bonus ditambahkan setelah point dasar dihitung'];
        $rows[] = ['7. Rank Point', '100, 99, 98, ... menurun', 'Peserta Final Point tertinggi = Rank 100'];

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        // Section headers (row 1, 12, 16)
        $sectionRows = [1, 12, 16];
        foreach ($sectionRows as $r) {
            $sheet->getStyle("A{$r}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '4C1D95']],
            ]);
        }

        // Table headers
        $headerRows = [2, 13, 17];
        foreach ($headerRows as $r) {
            $sheet->getStyle("A{$r}:{$sheet->getHighestColumn()}{$r}")->applyFromArray([
                'font'      => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '4C1D95']],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            ]);
        }

        $sheet->freezePane('A2');
    }
}