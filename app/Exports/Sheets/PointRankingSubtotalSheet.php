<?php

namespace App\Exports\Sheets;

use App\Models\Ikan;
use App\Helpers\PointCalculator;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class PointRankingSubtotalSheet implements FromArray, WithTitle, WithEvents
{
    private string $scope;

    private const CATS = [
        'overall' => 'OVERALL',
        'head'    => 'HEAD',
        'face'    => 'FACE',
        'body'    => 'BODY SHAPE',
        'marking' => 'MARKING',
        'pearl'   => 'PEARL',
        'color'   => 'COLOUR',
        'finnage' => 'FINNAGE',
    ];

    public function __construct(string $scope = 'per_kategori_kelas')
    {
        $this->scope = $scope;
    }

    public function title(): string
    {
        return match ($this->scope) {
            'per_kategori_kelas' => 'SUBTOTAL PER KAT+KELAS',
            'per_kategori'       => 'SUBTOTAL PER KATEGORI',
            'global'             => 'SUBTOTAL GLOBAL',
            default              => 'SUBTOTAL RANKING',
        };
    }

    private function calcIkan($ikan): ?array
    {
        $scorings = $ikan->scorings;

        if ($scorings->isEmpty()) {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | 1. Hitung rata-rata nilai detail dari semua juri
        |--------------------------------------------------------------------------
        */
        $avgDetail = [];
        $jumlahJuriYangNilai = 0;

        foreach ($scorings as $s) {
            if ($s->total_nilai) {
                $jumlahJuriYangNilai++;
            }

            if ($s->nilai_detail && is_array($s->nilai_detail)) {
                foreach ($s->nilai_detail as $kat => $fields) {
                    if (!is_array($fields)) continue;

                    foreach ($fields as $fid => $val) {
                        if ($fid === 'defect') continue;
                        if ($kat === 'pearl' && $fid === 'shining') $fid = 'shinning';

                        if (!isset($avgDetail[$kat][$fid])) {
                            $avgDetail[$kat][$fid] = [
                                'sum' => 0,
                                'count' => 0,
                            ];
                        }

                        $avgDetail[$kat][$fid]['sum'] += (float) ($val ?? 0);
                        $avgDetail[$kat][$fid]['count']++;
                    }
                }
            }
        }

        $finalAvgDetail = [];

        if ($jumlahJuriYangNilai > 0) {
            foreach ($avgDetail as $kat => $fields) {
                foreach ($fields as $fid => $d) {
                    $finalAvgDetail[$kat][$fid] = $d['count'] > 0
                        ? $d['sum'] / $d['count']
                        : 0;
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Gabungkan defect dari semua juri
        |--------------------------------------------------------------------------
        */
        $defectKeys = [
            'raw_head_penalty',
            'raw_face_penalty',
            'raw_body_penalty',
            'raw_finnage_penalty',
        ];

        $combinedDefects = [];

        foreach ($defectKeys as $key) {
            $combinedDefects[$key] = [];
        }

        foreach ($scorings as $s) {
            foreach ($defectKeys as $key) {
                $defs = $s->$key;

                if (!$defs) continue;
                if (is_string($defs)) $defs = [$defs];
                if (!is_array($defs)) continue;

                foreach ($defs as $defect) {
                    if ($defect && $defect !== '0' && !in_array($defect, $combinedDefects[$key])) {
                        $combinedDefects[$key][] = $defect;
                    }
                }
            }
        }

        $defectDataForCalc = [];

        foreach ($combinedDefects as $key => $defs) {
            $defectDataForCalc[$key] = count($defs) > 0 ? $defs : ['0'];
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Ambil breakdown dari helper
        |--------------------------------------------------------------------------
        */
        $breakdown = PointCalculator::hitungBreakdown(
            $ikan->kategori,
            $finalAvgDetail,
            $defectDataForCalc
        );

        if (!$breakdown) {
            return null;
        }

        $defectDetails = PointCalculator::getDefectDetails($defectDataForCalc);

        $catSubs = [];

        foreach (self::CATS as $catKey => $catLabel) {
            $catSubs[$catKey] = $breakdown[$catKey]['point'] ?? 0;
        }

        $totalPoint = $breakdown['total'] ?? 0;
        $totalBonus = (int) $ikan->bonusPoints->sum('points');

        return [
            'ikan_id' => $ikan->id,
            'nama_peserta' => $ikan->nama_peserta ?? $ikan->peserta?->nama_peserta ?? '—',
            'kategori' => $ikan->kategori,
            'kelas' => $ikan->kelas ?? '—',
            'nomor_tank' => $ikan->nomor_tank,
            'asal' => $ikan->detail_anggota ?? $ikan->peserta?->detail_anggota ?? '—',
            'jml_juri' => $scorings->count(),
            'cat_subs' => $catSubs,
            'total_point' => $totalPoint,
            'total_deduction_percent' => $defectDetails['total_deduction_percent'] ?? 0,
            'total_bonus' => $totalBonus,
        ];
    }

    public function array(): array
    {
        $ikans = Ikan::where('is_locked', true)
            ->whereNotNull('nomor_tank')
            ->whereHas('scorings')
            ->with(['peserta', 'scorings', 'bonusPoints'])
            ->orderBy('kategori')
            ->orderBy('kelas')
            ->orderBy('nomor_tank')
            ->get();

        $groups = [];

        foreach ($ikans as $ikan) {
            $d = $this->calcIkan($ikan);

            if (!$d) continue;

            $key = match ($this->scope) {
                'per_kategori_kelas' => $ikan->kategori . ' - Kelas ' . ($ikan->kelas ?? '—'),
                'per_kategori'       => $ikan->kategori,
                'global'             => 'GLOBAL',
                default              => $ikan->kategori . ' - Kelas ' . ($ikan->kelas ?? '—'),
            };

            $groups[$key][] = $d;
        }

        if ($this->scope !== 'global') {
            ksort($groups);
        }

        $headers = [
            'RANK',
            'PESERTA',
            'KATEGORI',
            'KELAS',
            'NO TANK',
            'ASAL / TEAM',
            'JML JURI',
        ];

        foreach (self::CATS as $label) {
            $headers[] = $label;
        }

        $headers[] = 'TOTAL POINT';
        $headers[] = 'DEFECT DEDUCTION';
        $headers[] = 'BONUS';
        $headers[] = 'RANK POINT';
        $headers[] = 'JUARA';

        $rows = [$headers];
        $colCount = count($headers);

        foreach ($groups as $groupName => $items) {
            $rankInput = [];

            foreach ($items as $it) {
                $rankInput[] = [
                    'ikan_id' => $it['ikan_id'],
                    'total_point' => $it['total_point'],
                    'total_bonus' => $it['total_bonus'],
                ];
            }

            $rankedItems = PointCalculator::hitungRankPoints($rankInput, 'total_point');

            $rankLookup = [];

            foreach ($rankedItems as $ri) {
                $rankLookup[$ri['ikan_id']] = $ri;
            }

            $ranked = [];

            foreach ($items as $it) {
                $ri = $rankLookup[$it['ikan_id']] ?? null;

                $it['rank_point'] = $ri['rank_point'] ?? 0;
                $it['final_rank_point'] = $ri['final_rank_point'] ?? 0;
                $it['position'] = $ri['position'] ?? 0;

                $ranked[] = $it;
            }

            usort($ranked, fn ($a, $b) => ($b['final_rank_point'] ?? 0) <=> ($a['final_rank_point'] ?? 0));

            $sep = array_fill(0, $colCount, '');
            $sep[0] = '▶ ' . strtoupper($groupName) . ' (' . count($ranked) . ' peserta)';
            $rows[] = $sep;

            foreach ($ranked as $i => $d) {
                $row = [
                    $i + 1,
                    $d['nama_peserta'],
                    strtoupper($d['kategori']),
                    $d['kelas'],
                    $d['nomor_tank'],
                    $d['asal'],
                    $d['jml_juri'],
                ];

                foreach (self::CATS as $catKey => $label) {
                    $row[] = $d['cat_subs'][$catKey] ?? 0;
                }

                $row[] = $d['total_point'];
                $row[] = ($d['total_deduction_percent'] ?? 0) > 0
                    ? $d['total_deduction_percent'] . '%'
                    : '-';
                $row[] = $d['total_bonus'];
                $row[] = $d['final_rank_point'] ?? 0;

                $pos = $d['position'] ?? 0;
                $row[] = ($pos >= 1 && $pos <= 10) ? (string) $pos : '-';

                $rows[] = $row;
            }

            $rows[] = array_fill(0, $colCount, '');
        }

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $lastCol = $sheet->getHighestColumn();
                $lastColIdx = Coordinate::columnIndexFromString($lastCol);
                $lastRow = $sheet->getHighestRow();

                $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 10,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['rgb' => '6D28D9'],
                    ],
                    'alignment' => [
                        'horizontal' => 'center',
                        'vertical' => 'center',
                        'wrapText' => true,
                    ],
                ]);

                $sheet->getStyle("A2:{$lastCol}{$lastRow}")->applyFromArray([
                    'alignment' => [
                        'horizontal' => 'center',
                        'vertical' => 'center',
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'DDD6FE'],
                        ],
                    ],
                ]);

                for ($r = 2; $r <= $lastRow; $r++) {
                    $val = $sheet->getCell("A{$r}")->getValue();

                    if ($val && str_starts_with($val, '▶')) {
                        $sheet->mergeCells("A{$r}:{$lastCol}{$r}");
                        $sheet->getStyle("A{$r}:{$lastCol}{$r}")->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'size' => 11,
                                'color' => ['rgb' => '4C1D95'],
                            ],
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'color' => ['rgb' => 'F5F3FF'],
                            ],
                            'alignment' => [
                                'horizontal' => 'left',
                                'vertical' => 'center',
                            ],
                        ]);

                        continue;
                    }

                    if ($val !== '') {
                        if (($r % 2) === 0) {
                            $sheet->getStyle("A{$r}:{$lastCol}{$r}")->applyFromArray([
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'color' => ['rgb' => 'FAF5FF'],
                                ],
                            ]);
                        }
                    }
                }

                $totalPointCol = Coordinate::stringFromColumnIndex($lastColIdx - 4);
                $bonusCol = Coordinate::stringFromColumnIndex($lastColIdx - 2);
                $rankPointCol = Coordinate::stringFromColumnIndex($lastColIdx - 1);
                $juaraCol = Coordinate::stringFromColumnIndex($lastColIdx);

                $sheet->getStyle("{$totalPointCol}2:{$totalPointCol}{$lastRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['rgb' => 'FEF9C3'],
                    ],
                ]);

                $sheet->getStyle("{$rankPointCol}2:{$rankPointCol}{$lastRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['rgb' => 'FEF3C7'],
                    ],
                ]);

                $sheet->getStyle("{$bonusCol}2:{$bonusCol}{$lastRow}")->applyFromArray([
                    'font' => ['bold' => true],
                ]);

                for ($r = 2; $r <= $lastRow; $r++) {
                    $val = $sheet->getCell("{$juaraCol}{$r}")->getValue();

                    if (is_numeric($val) && (int) $val >= 1 && (int) $val <= 10) {
                        $sheet->getStyle("{$juaraCol}{$r}")->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'color' => ['rgb' => '000000'],
                            ],
                            'alignment' => [
                                'horizontal' => 'center',
                                'vertical' => 'center',
                            ],
                        ]);
                    }
                }

                for ($c = 8; $c <= $lastColIdx - 5; $c++) {
                    $colLetter = Coordinate::stringFromColumnIndex($c);
                    $sheet->getStyle("{$colLetter}2:{$colLetter}{$lastRow}")
                        ->getNumberFormat()
                        ->setFormatCode('0.00');
                }

                $sheet->getStyle("{$totalPointCol}2:{$totalPointCol}{$lastRow}")
                    ->getNumberFormat()
                    ->setFormatCode('0.00');

                $sheet->getStyle("{$rankPointCol}2:{$rankPointCol}{$lastRow}")
                    ->getNumberFormat()
                    ->setFormatCode('0');

                for ($c = 1; $c <= $lastColIdx; $c++) {
                    $letter = Coordinate::stringFromColumnIndex($c);

                    if ($c <= 2) {
                        $width = 16;
                    } elseif ($c <= 7) {
                        $width = 13;
                    } else {
                        $width = 15;
                    }

                    $sheet->getColumnDimension($letter)
                        ->setAutoSize(false)
                        ->setWidth($width);
                }

                $sheet->freezePane('H2');
                $sheet->getRowDimension(1)->setRowHeight(28);
            },
        ];
    }
}