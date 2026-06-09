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

class MvpIkanSheet implements WithTitle, WithEvents
{
    public function title(): string
    {
        return 'DATA IKAN MVP';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // ═══════════════════════════════════════════════════════
                // 1. STYLE DEFINITIONS
                // ═══════════════════════════════════════════════════════
                $styleHeader = [
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '4C1D95']],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
                ];
                $styleBorder = [
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDD6FE']]],
                ];

                // ═══════════════════════════════════════════════════════
                // 2. DATA RETRIEVAL & RANK CACHE (SAMA PERSIS DENGAN syncMvp)
                // ═══════════════════════════════════════════════════════
                $mvpIkans = Ikan::where('is_mvp', true)
                    ->whereHas('peserta', fn($q) => $q->where('is_mvp_submitted', true))
                    ->with(['peserta', 'bonusPoints', 'scorings'])
                    ->get();

                if ($mvpIkans->isEmpty()) {
                    $sheet->setCellValue('A1', 'Tidak ada data MVP.');
                    return;
                }

                // Build Rank Cache per (kategori|kelas)
                $combos = $mvpIkans->map(fn($i) => $i->kategori . '|' . ($i->kelas ?? '-'))->unique()->values();
                $rankCache = [];

                foreach ($combos as $combo) {
                    [$kat, $kls] = explode('|', $combo, 2);
                    $kls = ($kls === '-') ? null : $kls;

                    $q = Ikan::where('is_locked', true)
                        ->whereNotNull('nomor_tank')
                        ->where('kategori', $kat)
                        ->whereHas('scorings')
                        ->with(['scorings', 'bonusPoints']);
                    
                    if ($kls !== null) $q->where('kelas', $kls);
                    else $q->whereNull('kelas');
                    
                    $pool = $q->get();
                    $items = [];

                    foreach ($pool as $pi) {
                        $avgDetail = [];
                        $jumlahJuri = 0;
                        foreach ($pi->scorings as $s) {
                            if ($s->total_nilai) $jumlahJuri++;
                            if ($s->nilai_detail && is_array($s->nilai_detail)) {
                                foreach ($s->nilai_detail as $kt => $fields) {
                                    if (!is_array($fields)) continue;
                                    foreach ($fields as $fid => $val) {
                                        if ($fid === 'defect') continue;
                                        if ($kt === 'pearl' && $fid === 'shining') $fid = 'shinning';
                                        if (!isset($avgDetail[$kt][$fid])) $avgDetail[$kt][$fid] = ['sum' => 0, 'count' => 0];
                                        $avgDetail[$kt][$fid]['sum'] += (float)($val ?? 0);
                                        $avgDetail[$kt][$fid]['count']++;
                                    }
                                }
                            }
                        }

                        $finalAvg = [];
                        if ($jumlahJuri > 0) {
                            foreach ($avgDetail as $kt => $f) {
                                foreach ($f as $fid => $d) {
                                    $finalAvg[$kt][$fid] = $d['count'] > 0 ? $d['sum'] / $d['count'] : 0;
                                }
                            }
                        }

                        // ★ LOGIKA DEFECT SAMA DENGAN SHEETSSYNCSERVICE (Prioritas Grand Juri)
                        $grandEdited  = $pi->scorings->first(fn($s) => $s->edited_by_grand_juri);
                        $defectSource = $grandEdited ?: $pi->scorings->sortByDesc('updated_at')->first();
                        $merged = [
                            'raw_head_penalty'    => ['0'],
                            'raw_face_penalty'    => ['0'],
                            'raw_body_penalty'    => ['0'],
                            'raw_finnage_penalty' => ['0'],
                        ];
                        if ($defectSource) {
                            $merged['raw_head_penalty']    = $defectSource->raw_head_penalty    ?: ['0'];
                            $merged['raw_face_penalty']    = $defectSource->raw_face_penalty    ?: ['0'];
                            $merged['raw_body_penalty']    = $defectSource->raw_body_penalty    ?: ['0'];
                            $merged['raw_finnage_penalty'] = $defectSource->raw_finnage_penalty ?: ['0'];
                        }

                        $items[] = [
                            'ikan_id'     => $pi->id,
                            'total_point' => (float) PointCalculator::hitungPoint($pi->kategori, $finalAvg, $merged),
                            'total_bonus' => (int) $pi->bonusPoints->sum('points'),
                        ];
                    }

                    $ranked = PointCalculator::hitungRankPoints($items, 'total_point');
                    $cache  = [];
                    foreach ($ranked as $idx => $r) {
                        $cache[$r['ikan_id']] = [
                            'rank_point' => $r['rank_point'],
                            'position'   => $idx + 1, // Re-sort position
                        ];
                    }
                    $rankCache[$combo] = $cache;
                }

                // ═══════════════════════════════════════════════════════
                // 3. GROUPING & TABLE DATA PREPARATION
                // ═══════════════════════════════════════════════════════
                $groups = $mvpIkans->groupBy(function ($ikan) {
                    $key = trim($ikan->detail_anggota ?? '');
                    return $key === '' ? '(Tanpa Kota/Team)' : $key;
                });

                $bonusLabels = [
                    'best_of_the_best' => 'BEST OF THE BEST',
                    'best_of_show'     => 'BEST OF SHOW',
                    'grand_champion'   => 'GRAND CHAMPION',
                    'young_champion'   => 'YOUNG CHAMPION',
                    'junior'           => 'JUNIOR',
                    'baby_champion'    => 'BABY CHAMPION',
                    'mini_champion'    => 'MINI CHAMPION',
                ];

                $teamData = [];
                foreach ($groups as $detailAnggota => $items) {
                    $jenis  = strtolower(trim($items->first()->jenis_keanggotaan ?? 'perorangan'));
                    $prefix = ($jenis === 'team') ? 'Team/Club' : 'Kota';
                    $headerText = $prefix . ' - ' . $detailAnggota;

                    $rows = [];
                    $sumFinalRank   = 0;
                    $bonusDescParts = [];
                    $no = 1;

                    $sorted = $items->sortBy(fn($ikan) => $ikan->nomor_tank ?? 99999);

                    foreach ($sorted as $ikan) {
                        $combo    = $ikan->kategori . '|' . ($ikan->kelas ?? '-');
                        $rankInfo = $rankCache[$combo][$ikan->id] ?? ['rank_point' => 0, 'position' => 0];
                        $rankPt   = (int) $rankInfo['rank_point'];
                        $position = (int) $rankInfo['position'];

                        $bonus = (int) $ikan->bonusPoints->sum('points');
                        $final = $rankPt + $bonus;

                        $sumFinalRank += $final;

                        $kelasDisp    = in_array($ikan->kategori, ['Bonsai', 'Jumbo']) ? '' : ($ikan->kelas ?? '');
                        $katKelasDisp = strtoupper($ikan->kategori ?? '') . ($kelasDisp ? ' ' . $kelasDisp : '');

                        // ★ NO TANK MULTI-LINE (Tank + Juara N)
                        $tankCell = (string) ($ikan->nomor_tank ?? '');
                        if ($position >= 1 && $position <= 10 && $final > 0) {
                            $tankCell .= "\nJuara " . $position;
                        }

                        $rows[] = [
                            $no,
                            $ikan->nama_peserta ?? '—',
                            $katKelasDisp,
                            $tankCell,
                            $final,
                        ];

                        // ★ KETERANGAN BONUS
                        $bonusTypes = $ikan->bonusPoints->pluck('bonus_type')->toArray();
                        if (!empty($bonusTypes)) {
                            $labels = array_map(function ($t) use ($bonusLabels) {
                                return $bonusLabels[$t] ?? strtoupper($t);
                            }, $bonusTypes);
                            $bonusDescParts[] = '[Tank ' . ($ikan->nomor_tank ?? '?') . '] ' . implode(', ', $labels) . ' (+' . $bonus . ')';
                        }
                        $no++;
                    }

                    $tableHeight = 2 + count($rows) + 1 + 1; // Header + SubHeader + Rows + Total + Keterangan

                    $teamData[] = [
                        'header'       => $headerText,
                        'rows'         => $rows,
                        'sumFinalRank' => $sumFinalRank,
                        'bonusDesc'    => empty($bonusDescParts) ? '—' : implode(' | ', $bonusDescParts),
                        'height'       => $tableHeight,
                    ];
                }

                usort($teamData, fn($a, $b) => strcmp($a['header'], $b['header']));

                // ═══════════════════════════════════════════════════════
                // 4. RENDER HORIZONTAL LAYOUT
                // ═══════════════════════════════════════════════════════
                $COLS_PER_TABLE = 5;
                $COL_GAP        = 1;
                $TABLES_PER_ROW = 4;

                $tableColStarts = [];
                $col = 0;
                for ($i = 0; $i < $TABLES_PER_ROW; $i++) {
                    $tableColStarts[] = $col;
                    $col += $COLS_PER_TABLE + $COL_GAP;
                }

                $currentRow = 1;
                $teamIndex  = 0;
                $totalTeam  = count($teamData);

                while ($teamIndex < $totalTeam) {
                    $rowTeams  = [];
                    $maxHeight = 0;

                    for ($i = 0; $i < $TABLES_PER_ROW && $teamIndex < $totalTeam; $i++) {
                        $rowTeams[] = [
                            'data'     => $teamData[$teamIndex],
                            'colStart' => $tableColStarts[$i],
                        ];
                        $maxHeight = max($maxHeight, $teamData[$teamIndex]['height']);
                        $teamIndex++;
                    }

                    foreach ($rowTeams as $rt) {
                        $data = $rt['data'];
                        $cs   = $rt['colStart'];
                        
                        $colLetterStart = Coordinate::stringFromColumnIndex($cs + 1);
                        $colLetterEnd   = Coordinate::stringFromColumnIndex($cs + $COLS_PER_TABLE);

                        // Baris 1: Header (Merge + Style)
                        $sheet->mergeCells("{$colLetterStart}{$currentRow}:{$colLetterEnd}{$currentRow}");
                        $sheet->setCellValue("{$colLetterStart}{$currentRow}", $data['header']);
                        $sheet->getStyle("{$colLetterStart}{$currentRow}:{$colLetterEnd}{$currentRow}")->applyFromArray([
                            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '4C1D95']],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F5F3FF']],
                            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                        ]);

                        // Baris 2: Sub-Header
                        $subRow = $currentRow + 1;
                        $subHeaders = ['NO', 'NAMA PESERTA', 'KATEGORI', 'NO TANK', 'RANK POINT'];
                        foreach ($subHeaders as $ci => $val) {
                            $colLetter = Coordinate::stringFromColumnIndex($cs + $ci + 1);
                            $sheet->setCellValue("{$colLetter}{$subRow}", $val);
                        }
                        $sheet->getStyle("{$colLetterStart}{$subRow}:{$colLetterEnd}{$subRow}")->applyFromArray($styleHeader);

                        // Baris 3+: Data Ikan
                        $dataRow = $subRow + 1;
                        foreach ($data['rows'] as $rowArr) {
                            foreach ($rowArr as $ci => $val) {
                                $colLetter = Coordinate::stringFromColumnIndex($cs + $ci + 1);
                                $sheet->setCellValue("{$colLetter}{$dataRow}", $val);
                            }
                            // Wrap text untuk kolom NO TANK (index 3)
                            $tankColLetter = Coordinate::stringFromColumnIndex($cs + 3 + 1);
                            $sheet->getStyle("{$tankColLetter}{$dataRow}")->applyFromArray([
                                'alignment' => ['wrapText' => true, 'horizontal' => 'center', 'vertical' => 'center'],
                            ]);
                            $dataRow++;
                        }

                        // Baris TOTAL
                        $totalRow = $dataRow;
                        $totalColLetter = Coordinate::stringFromColumnIndex($cs + 3 + 1);
                        $rankPointColLetter = Coordinate::stringFromColumnIndex($cs + 5);
                        $sheet->setCellValue("{$totalColLetter}{$totalRow}", 'TOTAL');
                        $sheet->setCellValue("{$rankPointColLetter}{$totalRow}", (int) $data['sumFinalRank']);
                        $sheet->getStyle("{$colLetterStart}{$totalRow}:{$colLetterEnd}{$totalRow}")->applyFromArray([
                            'font' => ['bold' => true, 'size' => 10],
                        ]);
                        $dataRow++;

                        // Baris KETERANGAN BONUS
                        $sheet->setCellValue("{$colLetterStart}{$dataRow}", 'KETERANGAN BONUS');
                        $ketColLetter = Coordinate::stringFromColumnIndex($cs + 2);
                        $sheet->setCellValue("{$ketColLetter}{$dataRow}", $data['bonusDesc']);
                        $sheet->mergeCells("{$ketColLetter}{$dataRow}:{$colLetterEnd}{$dataRow}");
                        $sheet->getStyle("{$colLetterStart}{$dataRow}:{$colLetterEnd}{$dataRow}")->applyFromArray([
                            'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => '3B82F6']],
                            'alignment' => ['wrapText' => true],
                        ]);

                        // Border untuk seluruh tabel
                        $tableEndRow = $dataRow;
                        $sheet->getStyle("{$colLetterStart}{$currentRow}:{$colLetterEnd}{$tableEndRow}")->applyFromArray($styleBorder);
                    }

                    $currentRow += $maxHeight + 2;
                }

                // Auto Size Columns
                foreach (range('A', 'Z') as $columnID) {
                    $sheet->getColumnDimension($columnID)->setAutoSize(true);
                }
            },
        ];
    }
}