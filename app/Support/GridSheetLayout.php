<?php

namespace App\Support;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GridSheetLayout
{
    private array $blocks;
    private int $blocksPerRow;
    private int $gapCols;
    private int $gapRows;
    private array $placements = [];
    private int $totalCols = 0;
    private int $blockCols = 0;

    /** @param array $blocks tiap item: ['title'=>str,'header'=>array,'rows'=>array,'total'=>?array,'cols'=>int] */
    public function __construct(array $blocks, int $blocksPerRow = 3, int $gapCols = 1, int $gapRows = 2)
    {
        $this->blocks = $blocks;
        $this->blocksPerRow = max(1, $blocksPerRow);
        $this->gapCols = $gapCols;
        $this->gapRows = $gapRows;
    }

    public function toArray(): array
    {
        $grid = [];
        $this->placements = [];

        foreach ($this->blocks as $b) $this->blockCols = max($this->blockCols, $b['cols']);
        $stride = $this->blockCols + $this->gapCols;
        $this->totalCols = $this->blocksPerRow * $stride - $this->gapCols;

        $rowCursor = 1; $colIndex = 0; $rowMaxHeight = 0;

        foreach ($this->blocks as $b) {
            $startCol = 1 + $colIndex * $stride;
            $startRow = $rowCursor;

            $grid[$startRow][$startCol] = $b['title'];
            foreach ($b['header'] as $i => $h) $grid[$startRow + 1][$startCol + $i] = $h;

            $dr = $startRow + 2;
            foreach ($b['rows'] as $row) {
                foreach ($row as $i => $v) $grid[$dr][$startCol + $i] = $v;
                $dr++;
            }
            $dataEnd = $dr - 1;

            $totalRow = null;
            if (!empty($b['total'])) {
                $totalRow = $dr;
                foreach ($b['total'] as $i => $v) $grid[$dr][$startCol + $i] = $v;
                $dr++;
            }

            $this->placements[] = [
                'r' => $startRow, 'c' => $startCol, 'cols' => $b['cols'],
                'dataStart' => $startRow + 2, 'dataEnd' => $dataEnd, 'totalRow' => $totalRow,
            ];

            $rowMaxHeight = max($rowMaxHeight, $dr - $startRow);
            $colIndex++;
            if ($colIndex >= $this->blocksPerRow) {
                $colIndex = 0;
                $rowCursor += $rowMaxHeight + $this->gapRows;
                $rowMaxHeight = 0;
            }
        }

        $maxRow = 1;
        foreach ($grid as $r => $_) $maxRow = max($maxRow, $r);

        $dense = [];
        for ($r = 1; $r <= $maxRow; $r++) {
            $line = [];
            for ($c = 1; $c <= $this->totalCols; $c++) $line[] = $grid[$r][$c] ?? '';
            $dense[] = $line;
        }
        return $dense;
    }

    public function style(Worksheet $sheet, array $opt = []): void
    {
        $titleColor  = $opt['titleColor']  ?? '6D28D9';
        $headerColor = $opt['headerColor'] ?? '1E40AF';
        $rankCol     = $opt['rankColIndex']  ?? null;
        $pointCol    = $opt['pointColIndex'] ?? null;
        $mergeTo     = $opt['totalMergeTo']  ?? 0;
        $colWidths   = $opt['colWidths'] ?? [];

        $stride = $this->blockCols + $this->gapCols;
        for ($c = 1; $c <= $this->totalCols; $c++) {
            $offset = ($c - 1) % $stride;
            $letter = Coordinate::stringFromColumnIndex($c);
            if ($offset < $this->blockCols && isset($colWidths[$offset])) {
                $sheet->getColumnDimension($letter)->setAutoSize(false)->setWidth($colWidths[$offset]);
            } elseif ($offset >= $this->blockCols) {
                $sheet->getColumnDimension($letter)->setAutoSize(false)->setWidth(2.5);
            }
        }

        foreach ($this->placements as $p) {
            $c0 = $p['c']; $cN = $p['c'] + $p['cols'] - 1;
            $L0 = Coordinate::stringFromColumnIndex($c0);
            $LN = Coordinate::stringFromColumnIndex($cN);
            $r = $p['r'];

            $sheet->mergeCells("{$L0}{$r}:{$LN}{$r}");
            $sheet->getStyle("{$L0}{$r}:{$LN}{$r}")->applyFromArray([
                'font' => ['bold'=>true,'size'=>11,'color'=>['rgb'=>'FFFFFF']],
                'fill' => ['fillType'=>Fill::FILL_SOLID,'color'=>['rgb'=>$titleColor]],
                'alignment'=>['horizontal'=>'center','vertical'=>'center'],
            ]);
            $sheet->getRowDimension($r)->setRowHeight(20);

            $hr = $r + 1;
            $sheet->getStyle("{$L0}{$hr}:{$LN}{$hr}")->applyFromArray([
                'font'=>['bold'=>true,'size'=>9,'color'=>['rgb'=>'FFFFFF']],
                'fill'=>['fillType'=>Fill::FILL_SOLID,'color'=>['rgb'=>$headerColor]],
                'alignment'=>['horizontal'=>'center','vertical'=>'center'],
                'borders'=>['allBorders'=>['borderStyle'=>Border::BORDER_THIN,'color'=>['rgb'=>'BFDBFE']]],
            ]);

            if ($p['dataEnd'] >= $p['dataStart']) {
                $sheet->getStyle("{$L0}{$p['dataStart']}:{$LN}{$p['dataEnd']}")->applyFromArray([
                    'alignment'=>['horizontal'=>'center','vertical'=>'center'],
                    'borders'=>['allBorders'=>['borderStyle'=>Border::BORDER_THIN,'color'=>['rgb'=>'DDD6FE']]],
                ]);
                $nameL = Coordinate::stringFromColumnIndex($c0 + 1);
                $sheet->getStyle("{$nameL}{$p['dataStart']}:{$nameL}{$p['dataEnd']}")->getAlignment()->setHorizontal('left');

                if ($pointCol !== null) {
                    $pl = Coordinate::stringFromColumnIndex($c0 + $pointCol);
                    $sheet->getStyle("{$pl}{$p['dataStart']}:{$pl}{$p['dataEnd']}")->applyFromArray([
                        'font'=>['bold'=>true],
                        'fill'=>['fillType'=>Fill::FILL_SOLID,'color'=>['rgb'=>'F5F3FF']],
                    ]);
                }
                if ($rankCol !== null) {
                    $rl = Coordinate::stringFromColumnIndex($c0 + $rankCol);
                    $sheet->getStyle("{$rl}{$p['dataStart']}:{$rl}{$p['dataEnd']}")->applyFromArray([
                        'font'=>['bold'=>true],
                        'fill'=>['fillType'=>Fill::FILL_SOLID,'color'=>['rgb'=>'FEF9C3']],
                    ]);
                }
            }

            if ($p['totalRow']) {
                $tr = $p['totalRow'];
                $sheet->getStyle("{$L0}{$tr}:{$LN}{$tr}")->applyFromArray([
                    'font'=>['bold'=>true,'size'=>10,'color'=>['rgb'=>'92400E']],
                    'fill'=>['fillType'=>Fill::FILL_SOLID,'color'=>['rgb'=>'FEF3C7']],
                    'borders'=>['allBorders'=>['borderStyle'=>Border::BORDER_THIN,'color'=>['rgb'=>'FDE68A']]],
                    'alignment'=>['horizontal'=>'center','vertical'=>'center'],
                ]);
                if ($mergeTo > 0) {
                    $ml = Coordinate::stringFromColumnIndex($c0 + $mergeTo);
                    $sheet->mergeCells("{$L0}{$tr}:{$ml}{$tr}");
                    $sheet->getStyle("{$L0}{$tr}")->getAlignment()->setHorizontal('right')->setVertical('center');
                }
            }
        }
    }
}