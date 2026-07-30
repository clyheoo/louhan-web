<?php

namespace App\Exports\Sheets;

use App\Models\Nominasi;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class NominasiFixSheet implements WithTitle, WithEvents
{
    /**
     * Nominasi final. Ubah ke ['pending','approved','rejected'] bila perlu.
     */
    private array $statusFilter = ['approved'];

    public function title(): string
    {
        return 'NOMINASI FIX (KAT & KELAS)';
    }

    private function normalizeDefectArray($value): array
    {
        if (is_string($value)) {
            $value = [$value];
        }
        if (!is_array($value)) {
            return [];
        }
        return collect($value)
            ->map(function ($v) {
                $v = trim((string) $v);
                $v = preg_replace('/\s+Sempurna/u', '', $v) ?? $v;
                return $v;
            })
            ->filter(fn ($v) => $v !== '' && $v !== '0')
            ->unique()
            ->values()
            ->toArray();
    }

    private function formatNominasiDefect(Nominasi $n): string
    {
        $parts = [
            'HEAD'    => $this->normalizeDefectArray($n->raw_head_penalty ?? ['0']),
            'FACE'    => $this->normalizeDefectArray($n->raw_face_penalty ?? ['0']),
            'BODY'    => $this->normalizeDefectArray($n->raw_body_penalty ?? ['0']),
            'FINNAGE' => $this->normalizeDefectArray($n->raw_finnage_penalty ?? ['0']),
        ];
        $out = [];
        foreach ($parts as $label => $items) {
            if (!empty($items)) {
                $out[] = $label . ': ' . implode(', ', $items);
            }
        }
        return empty($out) ? '-' : implode(' | ', $out);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet  = $event->sheet->getDelegate();
                $col    = fn (int $i) => Coordinate::stringFromColumnIndex($i);

                // ── STYLE ─────────────────────────────────
                $styleMainTitle = [
                    'font'      => ['bold' => true, 'size' => 13, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '0F766E']],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                ];
                $styleBlockTitle = [
                    'font'      => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '0D9488']],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                ];
                $styleHeader = [
                    'font'      => ['bold' => true, 'size' => 9, 'color' => ['rgb' => '134E4A']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'CCFBF1']],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '5EEAD4']]],
                ];
                $styleBorder = [
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCFBF1']]],
                ];
                $styleAlt = [
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F0FDFA']],
                ];
                $styleDefectCell = [
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FDE68A']],
                    'font'      => ['color' => ['rgb' => '92400E'], 'bold' => true, 'size' => 9],
                    'alignment' => ['wrapText' => true, 'vertical' => 'top'],
                ];

                // ── DATA & GROUPING ───────────────────────
                $noms = Nominasi::whereIn('status', $this->statusFilter)
                    ->with('ikan.peserta')
                    ->get()
                    ->filter(fn ($n) => $n->ikan !== null);

                $groups = [];
                foreach ($noms as $n) {
                    $ik    = $n->ikan;
                    $kat   = trim((string) ($ik->kategori ?? '-'));
                    if ($kat === '') { $kat = '-'; }
                    $kelas = trim((string) ($ik->kelas ?? ''));
                    $key   = $kat . '||' . $kelas;
                    if (!isset($groups[$key])) {
                        $groups[$key] = ['kategori' => $kat, 'kelas' => $kelas, 'items' => []];
                    }
                    $groups[$key]['items'][] = [
                        'ikan'   => $ik,
                        'defect' => $this->formatNominasiDefect($n),
                    ];
                }
                uasort($groups, fn ($a, $b) => [$a['kategori'], $a['kelas']] <=> [$b['kategori'], $b['kelas']]);

                // ── LAYOUT GRID ───────────────────────────
                $perRow  = 3;
                $blockW  = 5;
                $gapCol  = 1;
                $stride  = $blockW + $gapCol;              // 6
                $gapRows = 2;
                $topStart = 3;
                $usedW   = $perRow * $stride - $gapCol;     // 17

                $sheet->mergeCells('A1:' . $col($usedW) . '1');
                $sheet->setCellValue('A1', 'NOMINASI FIX — PER KATEGORI & KELAS  |  Total grup: ' . count($groups));
                $sheet->getStyle('A1:' . $col($usedW) . '1')->applyFromArray($styleMainTitle);
                $sheet->getRowDimension(1)->setRowHeight(24);

                if (empty($groups)) {
                    $sheet->mergeCells('A3:' . $col($usedW) . '3');
                    $sheet->setCellValue('A3', 'Belum ada nominasi fix (status approved).');
                    $sheet->getStyle('A3:' . $col($usedW) . '3')->applyFromArray($styleBorder);
                    return;
                }

                $headers  = ['NO URUT', 'KATEGORI', 'KELAS', 'NO TANK', 'KETERANGAN'];
                $blockIdx = 0;
                $bandTop  = $topStart;
                $bandMaxH = 0;

                foreach ($groups as $g) {
                    $pos  = $blockIdx % $perRow;
                    $c0i  = 1 + $pos * $stride;
                    $c0   = $col($c0i);
                    $c4   = $col($c0i + 4);
                    $r    = $bandTop;

                    $items = collect($g['items'])->sortBy(function ($it) {
                        $t = $it['ikan']->nomor_tank;
                        return is_numeric($t) ? (int) $t : 999999;
                    })->values();

                    // judul blok: "KATEGORI KELAS"
                    $titleText = $g['kategori'] . ($g['kelas'] !== '' ? ' ' . $g['kelas'] : '');
                    $sheet->mergeCells("{$c0}{$r}:{$c4}{$r}");
                    $sheet->setCellValue("{$c0}{$r}", $titleText);
                    $sheet->getStyle("{$c0}{$r}:{$c4}{$r}")->applyFromArray($styleBlockTitle);
                    $sheet->getRowDimension($r)->setRowHeight(20);
                    $r++;

                    // header kolom
                    for ($i = 0; $i < 5; $i++) {
                        $sheet->setCellValue($col($c0i + $i) . $r, $headers[$i]);
                    }
                    $sheet->getStyle("{$c0}{$r}:{$c4}{$r}")->applyFromArray($styleHeader);
                    $r++;

                    // data
                    $no        = 1;
                    $dataStart = $r;
                    foreach ($items as $it) {
                        $ik     = $it['ikan'];
                        $defect = $it['defect'];
                        $sheet->setCellValue($col($c0i + 0) . $r, $no);
                        $sheet->setCellValue($col($c0i + 1) . $r, $g['kategori']);
                        $sheet->setCellValue($col($c0i + 2) . $r, $g['kelas'] !== '' ? $g['kelas'] : '-');
                        $sheet->setCellValue($col($c0i + 3) . $r, $ik->nomor_tank ?? '-');
                        $sheet->setCellValue($col($c0i + 4) . $r, $defect !== '-' ? $defect : '');
                        if ($no % 2 === 0) {
                            $sheet->getStyle("{$c0}{$r}:{$c4}{$r}")->applyFromArray($styleAlt);
                        }
                        if ($defect !== '-') {
                            $sheet->getStyle($col($c0i + 4) . $r)->applyFromArray($styleDefectCell);
                        }
                        $no++;
                        $r++;
                    }

                    if ($r > $dataStart) {
                        $sheet->getStyle("{$c0}{$dataStart}:{$c4}" . ($r - 1))->applyFromArray($styleBorder);
                        // rata tengah: NO URUT, KELAS, NO TANK
                        foreach ([0, 2, 3] as $ci) {
                            $cc = $col($c0i + $ci);
                            $sheet->getStyle("{$cc}{$dataStart}:{$cc}" . ($r - 1))
                                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        }
                    }

                    $blockHeight = 2 + $items->count();
                    if ($blockHeight > $bandMaxH) { $bandMaxH = $blockHeight; }

                    $blockIdx++;
                    if ($blockIdx % $perRow === 0) {
                        $bandTop  += $bandMaxH + $gapRows;
                        $bandMaxH  = 0;
                    }
                }

                // ── LEBAR KOLOM (per posisi blok) ─────────
                for ($p = 0; $p < $perRow; $p++) {
                    $base = 1 + $p * $stride;
                    $sheet->getColumnDimension($col($base + 0))->setWidth(9);
                    $sheet->getColumnDimension($col($base + 1))->setWidth(18);
                    $sheet->getColumnDimension($col($base + 2))->setWidth(7);
                    $sheet->getColumnDimension($col($base + 3))->setWidth(10);
                    $sheet->getColumnDimension($col($base + 4))->setWidth(30);
                    if ($p < $perRow - 1) {
                        $sheet->getColumnDimension($col($base + 5))->setWidth(3);
                    }
                }
            },
        ];
    }
}