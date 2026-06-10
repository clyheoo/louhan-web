<?php

namespace App\Exports\Sheets;

use App\Models\Ikan;
use App\Helpers\PointCalculator;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class TeamChampionSheet implements WithTitle, WithEvents
{
    public function title(): string
    {
        return 'TEAM CHAMPION';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $styleHeader = [
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '4C1D95']],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
                ];

                $styleBorder = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'DDD6FE'],
                        ],
                    ],
                ];

                $bonusLabels = [
                    'best_of_the_best' => 'BEST OF THE BEST',
                    'best_of_show'     => 'BEST OF SHOW',
                    'grand_champion'   => 'GRAND CHAMPION',
                    'young_champion'   => 'YOUNG CHAMPION',
                    'junior'           => 'JUNIOR',
                    'baby_champion'    => 'BABY CHAMPION',
                    'mini_champion'    => 'MINI CHAMPION',
                ];

                $teamChampionIkans = Ikan::where('is_team_champion', true)
                    ->whereHas('peserta', function ($q) {
                        $q->where('is_team_champion_submitted', true);
                    })
                    ->with(['peserta', 'bonusPoints', 'scorings'])
                    ->get();

                if ($teamChampionIkans->isEmpty()) {
                    $sheet->setCellValue('A1', 'Tidak ada data Team Champion yang sudah disubmit.');
                    return;
                }

                /*
                |--------------------------------------------------------------------------
                | BUILD RANK CACHE PER KATEGORI + KELAS
                |--------------------------------------------------------------------------
                */
                $combos = $teamChampionIkans
                    ->map(fn ($i) => $i->kategori . '|' . ($i->kelas ?? '-'))
                    ->unique()
                    ->values();

                $rankCache = [];

                foreach ($combos as $combo) {
                    [$kat, $kls] = explode('|', $combo, 2);
                    $kls = ($kls === '-') ? null : $kls;

                    $q = Ikan::where('is_locked', true)
                        ->whereNotNull('nomor_tank')
                        ->where('kategori', $kat)
                        ->whereHas('scorings')
                        ->with(['scorings', 'bonusPoints']);

                    if ($kls !== null) {
                        $q->where('kelas', $kls);
                    } else {
                        $q->whereNull('kelas');
                    }

                    $pool = $q->get();
                    $items = [];

                    foreach ($pool as $pi) {
                        $avgDetail = [];
                        $jumlahJuri = 0;

                        foreach ($pi->scorings as $s) {
                            if ($s->total_nilai) {
                                $jumlahJuri++;
                            }

                            if ($s->nilai_detail && is_array($s->nilai_detail)) {
                                foreach ($s->nilai_detail as $kt => $fields) {
                                    if (!is_array($fields)) continue;

                                    foreach ($fields as $fid => $val) {
                                        if ($fid === 'defect') continue;
                                        if ($kt === 'pearl' && $fid === 'shining') $fid = 'shinning';

                                        if (!isset($avgDetail[$kt][$fid])) {
                                            $avgDetail[$kt][$fid] = ['sum' => 0, 'count' => 0];
                                        }

                                        $avgDetail[$kt][$fid]['sum'] += (float) ($val ?? 0);
                                        $avgDetail[$kt][$fid]['count']++;
                                    }
                                }
                            }
                        }

                        $finalAvg = [];

                        if ($jumlahJuri > 0) {
                            foreach ($avgDetail as $kt => $f) {
                                foreach ($f as $fid => $d) {
                                    $finalAvg[$kt][$fid] = $d['count'] > 0
                                        ? $d['sum'] / $d['count']
                                        : 0;
                                }
                            }
                        }

                        $grandEdited = $pi->scorings->first(fn ($s) => $s->edited_by_grand_juri);
                        $defectSource = $grandEdited ?: $pi->scorings->sortByDesc('updated_at')->first();

                        $merged = [
                            'raw_head_penalty'    => ['0'],
                            'raw_face_penalty'    => ['0'],
                            'raw_body_penalty'    => ['0'],
                            'raw_finnage_penalty' => ['0'],
                        ];

                        if ($defectSource) {
                            $merged['raw_head_penalty']    = $defectSource->raw_head_penalty ?: ['0'];
                            $merged['raw_face_penalty']    = $defectSource->raw_face_penalty ?: ['0'];
                            $merged['raw_body_penalty']    = $defectSource->raw_body_penalty ?: ['0'];
                            $merged['raw_finnage_penalty'] = $defectSource->raw_finnage_penalty ?: ['0'];
                        }

                        $items[] = [
                            'ikan_id'     => $pi->id,
                            'total_point' => (float) PointCalculator::hitungPoint($pi->kategori, $finalAvg, $merged),
                            'total_bonus' => (int) $pi->bonusPoints->sum('points'),
                        ];
                    }

                    $ranked = PointCalculator::hitungRankPoints($items, 'total_point');

                    $cache = [];

                    foreach ($ranked as $idx => $r) {
                        $cache[$r['ikan_id']] = [
                            'rank_point' => (int) ($r['rank_point'] ?? 0),
                            'position'   => $idx + 1,
                        ];
                    }

                    $rankCache[$combo] = $cache;
                }

                /*
                |--------------------------------------------------------------------------
                | GROUP DATA TEAM CHAMPION
                |--------------------------------------------------------------------------
                */
                $groups = $teamChampionIkans->groupBy(function ($ikan) {
                    $key = trim($ikan->detail_anggota ?? optional($ikan->peserta)->detail_anggota ?? '');
                    return $key === '' ? '(Tanpa Team/Club)' : $key;
                });

                $teamData = [];

                foreach ($groups as $detailAnggota => $items) {
                    $rows = [];
                    $sumFinalRankPoint = 0;
                    $bonusDescParts = [];
                    $no = 1;

                    $sorted = $items->sortBy(fn ($ikan) => $ikan->nomor_tank ?? 999999);

                    foreach ($sorted as $ikan) {
                        $combo = $ikan->kategori . '|' . ($ikan->kelas ?? '-');

                        $rankInfo = $rankCache[$combo][$ikan->id] ?? [
                            'rank_point' => 0,
                            'position' => 0,
                        ];

                        $rankPoint = (int) $rankInfo['rank_point'];
                        $position = (int) $rankInfo['position'];

                        $bonus = (int) $ikan->bonusPoints->sum('points');
                        $finalRankPoint = $rankPoint + $bonus;

                        $sumFinalRankPoint += $finalRankPoint;

                        $kelasDisp = in_array($ikan->kategori, ['Bonsai', 'Jumbo'])
                            ? ''
                            : ($ikan->kelas ?? '');

                        $katKelasDisp = strtoupper($ikan->kategori ?? '')
                            . ($kelasDisp ? ' ' . $kelasDisp : '');

                        $juaraText = ($position >= 1 && $position <= 10 && $rankPoint > 0)
                            ? 'Juara ' . $position
                            : '-';

                        $rows[] = [
                            $no,
                            $ikan->nama_peserta ?? optional($ikan->peserta)->nama_peserta ?? '—',
                            $katKelasDisp,
                            $ikan->nomor_tank ?? '',
                            $juaraText,
                            $rankPoint,
                            $bonus,
                            $finalRankPoint,
                        ];

                        $bonusTypes = $ikan->bonusPoints->pluck('bonus_type')->toArray();

                        if (!empty($bonusTypes)) {
                            $labels = array_map(function ($type) use ($bonusLabels) {
                                return $bonusLabels[$type] ?? strtoupper(str_replace('_', ' ', $type));
                            }, $bonusTypes);

                            $bonusDescParts[] = '[Tank ' . ($ikan->nomor_tank ?? '?') . '] '
                                . implode(', ', $labels)
                                . ' (+' . $bonus . ')';
                        }

                        $no++;
                    }

                    $teamData[] = [
                        'header' => 'Team/Club - ' . $detailAnggota,
                        'rows' => $rows,
                        'sumFinalRankPoint' => $sumFinalRankPoint,
                        'bonusDesc' => empty($bonusDescParts) ? '—' : implode(' | ', $bonusDescParts),
                        'height' => 2 + count($rows) + 1 + 1,
                    ];
                }

                usort($teamData, fn ($a, $b) => strcmp($a['header'], $b['header']));

                /*
                |--------------------------------------------------------------------------
                | RENDER HORIZONTAL LAYOUT
                |--------------------------------------------------------------------------
                */
                $COLS_PER_TABLE = 8;
                $COL_GAP = 1;
                $TABLES_PER_ROW = 2;

                $tableColStarts = [];
                $col = 0;

                for ($i = 0; $i < $TABLES_PER_ROW; $i++) {
                    $tableColStarts[] = $col;
                    $col += $COLS_PER_TABLE + $COL_GAP;
                }

                $currentRow = 1;
                $teamIndex = 0;
                $totalTeam = count($teamData);

                while ($teamIndex < $totalTeam) {
                    $rowTeams = [];
                    $maxHeight = 0;

                    for ($i = 0; $i < $TABLES_PER_ROW && $teamIndex < $totalTeam; $i++) {
                        $rowTeams[] = [
                            'data' => $teamData[$teamIndex],
                            'colStart' => $tableColStarts[$i],
                        ];

                        $maxHeight = max($maxHeight, $teamData[$teamIndex]['height']);
                        $teamIndex++;
                    }

                    foreach ($rowTeams as $rt) {
                        $data = $rt['data'];
                        $cs = $rt['colStart'];

                        $colLetterStart = Coordinate::stringFromColumnIndex($cs + 1);
                        $colLetterEnd = Coordinate::stringFromColumnIndex($cs + $COLS_PER_TABLE);

                        $sheet->mergeCells("{$colLetterStart}{$currentRow}:{$colLetterEnd}{$currentRow}");
                        $sheet->setCellValue("{$colLetterStart}{$currentRow}", $data['header']);
                        $sheet->getStyle("{$colLetterStart}{$currentRow}:{$colLetterEnd}{$currentRow}")
                            ->applyFromArray([
                                'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '4C1D95']],
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F5F3FF']],
                                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                            ]);

                        $subRow = $currentRow + 1;
                        $subHeaders = [
                            'NO',
                            'NAMA PESERTA',
                            'KATEGORI',
                            'NO TANK',
                            'JUARA',
                            'RANK POINT',
                            'BONUS',
                            'FINAL RANK POINT',
                        ];

                        foreach ($subHeaders as $ci => $val) {
                            $colLetter = Coordinate::stringFromColumnIndex($cs + $ci + 1);
                            $sheet->setCellValue("{$colLetter}{$subRow}", $val);
                        }

                        $sheet->getStyle("{$colLetterStart}{$subRow}:{$colLetterEnd}{$subRow}")
                            ->applyFromArray($styleHeader);

                        $dataRow = $subRow + 1;

                        foreach ($data['rows'] as $rowArr) {
                            foreach ($rowArr as $ci => $val) {
                                $colLetter = Coordinate::stringFromColumnIndex($cs + $ci + 1);
                                $sheet->setCellValue("{$colLetter}{$dataRow}", $val);
                            }

                            $sheet->getStyle("{$colLetterStart}{$dataRow}:{$colLetterEnd}{$dataRow}")
                                ->getAlignment()
                                ->setVertical(Alignment::VERTICAL_CENTER);

                            $dataRow++;
                        }

                            $totalRow = $dataRow;

                            $labelStart = Coordinate::stringFromColumnIndex($cs + 1);
                            $labelEnd = Coordinate::stringFromColumnIndex($cs + 7);
                            $finalRankPointCol = Coordinate::stringFromColumnIndex($cs + 8);

                            $sheet->mergeCells("{$labelStart}{$totalRow}:{$labelEnd}{$totalRow}");
                            $sheet->setCellValue("{$labelStart}{$totalRow}", 'TOTAL FINAL RANK POINT');
                            $sheet->setCellValue("{$finalRankPointCol}{$totalRow}", (int) ($data['sumFinalRankPoint'] ?? 0));

                            $sheet->getStyle("{$labelStart}{$totalRow}:{$finalRankPointCol}{$totalRow}")
                                ->applyFromArray([
                                    'font' => ['bold' => true, 'size' => 10],
                                    'alignment' => ['horizontal' => 'right', 'vertical' => 'center'],
                                ]);

                            $sheet->getStyle("{$finalRankPointCol}{$totalRow}")
                                ->getAlignment()
                                ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                            // Baris KETERANGAN BONUS
                            $bonusRow = $totalRow + 1;

                            $bonusLabelCol = Coordinate::stringFromColumnIndex($cs + 1);
                            $bonusTextStartCol = Coordinate::stringFromColumnIndex($cs + 2);
                            $bonusTextEndCol = Coordinate::stringFromColumnIndex($cs + 8);

                            $sheet->setCellValue("{$bonusLabelCol}{$bonusRow}", 'KETERANGAN BONUS');
                            $sheet->setCellValue("{$bonusTextStartCol}{$bonusRow}", $data['bonusDesc'] ?? '—');
                            $sheet->mergeCells("{$bonusTextStartCol}{$bonusRow}:{$bonusTextEndCol}{$bonusRow}");

                            $sheet->getStyle("{$bonusLabelCol}{$bonusRow}:{$bonusTextEndCol}{$bonusRow}")
                                ->applyFromArray([
                                    'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => '3B82F6']],
                                    'alignment' => ['wrapText' => true, 'vertical' => 'center'],
                                ]);

                            $sheet->getStyle("{$colLetterStart}{$currentRow}:{$colLetterEnd}{$bonusRow}")
                                ->applyFromArray($styleBorder);
                         }

                    $currentRow += $maxHeight + 2;
                }

                foreach (range('A', 'Z') as $columnID) {
                    $sheet->getColumnDimension($columnID)->setAutoSize(true);
                }
            },
        ];
    }
}