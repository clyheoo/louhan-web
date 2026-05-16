<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ScoringPointConfig;

class ScoringPointConfigSeeder extends Seeder
{
    public function run(): void
    {
        $configs = [
            ['kategori' => 'Cencu',       'o' => 2.5,  'h' => 17.5, 'f' => 2.5,  'b' => 15,   'm' => 12.5, 'p' => 17.5, 'c' => 17.5, 'fn' => 15],
            ['kategori' => 'Chginwa',     'o' => 2.5,  'h' => 20,   'f' => 2.5,  'b' => 20,   'm' => 12.5, 'p' => 12.5, 'c' => 15,   'fn' => 15],
            ['kategori' => 'Freemarking', 'o' => 2.5,  'h' => 17.5, 'f' => 2.5,  'b' => 17.5, 'm' => 0,    'p' => 25,   'c' => 17.5, 'fn' => 17.5],
            ['kategori' => 'Goldenbase',  'o' => 2.5,  'h' => 20,   'f' => 2.5,  'b' => 17.5, 'm' => 0,    'p' => 15,   'c' => 25,   'fn' => 17.5],
            ['kategori' => 'Klasik',      'o' => 2.5,  'h' => 22.5, 'f' => 2.5,  'b' => 22.5, 'm' => 10,   'p' => 0,    'c' => 22.5, 'fn' => 17.5],
            ['kategori' => 'Bonsai',      'o' => 2.5,  'h' => 20,   'f' => 2.5,  'b' => 40,   'm' => 5,    'p' => 12.5, 'c' => 12.5, 'fn' => 5],
            ['kategori' => 'Jumbo',       'o' => 2.5,  'h' => 17.5, 'f' => 2.5,  'b' => 15,   'm' => 12.5, 'p' => 17.5, 'c' => 17.5, 'fn' => 15],
        ];

        foreach ($configs as $c) {
            ScoringPointConfig::updateOrCreate(['kategori' => $c['kategori']], [
                'overall_bobot' => $c['o'],
                'head_bobot'    => $c['h'],
                'face_bobot'    => $c['f'],
                'body_bobot'    => $c['b'],
                'marking_bobot' => $c['m'],
                'pearl_bobot'   => $c['p'],
                'color_bobot'   => $c['c'],
                'finnage_bobot' => $c['fn'],
            ]);
        }

        // Recompute total_point untuk semua scoring yang sudah ada
        $scorings = \App\Models\Scoring::with('ikan')->get();
        foreach ($scorings as $scoring) {
            if ($scoring->ikan && $scoring->nilai_detail) {
                $scoring->total_point = \App\Helpers\PointCalculator::hitungPoint(
                    $scoring->ikan->kategori,
                    $scoring->nilai_detail
                );
                $scoring->save();
            }
        }

        $this->command->info('Point configs seeded & existing scores recomputed.');
    }
}