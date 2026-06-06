<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ScoringPointConfig;

class ScoringPointConfigSeeder extends Seeder
{
    public function run(): void
    {
        // Persentase bersifat global (sama untuk semua kategori)
        $percentages = [
            'overall_point' => 100,
            'head_size_pct' => 60, 'head_bentuk_k_pct' => 40,
            'face_face_pct' => 100,
            'body_bentuk_pct' => 50, 'body_proposional_pct' => 40, 'body_pangkal_pct' => 10,
            'marking_fullness_pct' => 40, 'marking_contrast_pct' => 40, 'marking_bentuk_pct' => 20,
            'pearl_shinning_pct' => 45, 'pearl_fullnes_pct' => 35, 'pearl_bentuk_pearl_pct' => 20,
            'color_komposisi_pct' => 45, 'color_kecerahan_pct' => 35, 'color_fullness_colour_pct' => 20,
            'finnage_bentuk_sirip_ekor_pct' => 75, 'finnage_kecerahan_pct' => 25,
        ];

        $configs = [
            ['kategori' => 'Cencu',       'o' => 2.5,  'h' => 17.5, 'f' => 2.5,  'b' => 15,   'm' => 12.5, 'p' => 17.5, 'c' => 17.5, 'fn' => 15],
            ['kategori' => 'Chingwa',     'o' => 2.5,  'h' => 20,   'f' => 2.5,  'b' => 20,   'm' => 12.5, 'p' => 12.5, 'c' => 15,   'fn' => 15],
            ['kategori' => 'Freemarking', 'o' => 2.5,  'h' => 17.5, 'f' => 2.5,  'b' => 17.5, 'm' => 0,    'p' => 25,   'c' => 17.5, 'fn' => 17.5],
            ['kategori' => 'Goldenbase',  'o' => 2.5,  'h' => 20,   'f' => 2.5,  'b' => 17.5, 'm' => 0,    'p' => 15,   'c' => 25,   'fn' => 17.5],
            ['kategori' => 'Klasik',      'o' => 2.5,  'h' => 22.5, 'f' => 2.5,  'b' => 22.5, 'm' => 10,   'p' => 0,    'c' => 22.5, 'fn' => 17.5],
            ['kategori' => 'Bonsai',      'o' => 2.5,  'h' => 20,   'f' => 2.5,  'b' => 40,   'm' => 5,    'p' => 12.5, 'c' => 12.5, 'fn' => 5],
            ['kategori' => 'Jumbo',       'o' => 2.5,  'h' => 17.5, 'f' => 2.5,  'b' => 15,   'm' => 12.5, 'p' => 17.5, 'c' => 17.5, 'fn' => 15],
        ];

        foreach ($configs as $c) {
            // updateOrCreate akan mengupdate data lama, atau membuat baru jika tidak ada
            ScoringPointConfig::updateOrCreate(
                ['kategori' => $c['kategori']], 
                array_merge([
                    'overall_bobot' => $c['o'],
                    'head_bobot'    => $c['h'],
                    'face_bobot'    => $c['f'],
                    'body_bobot'    => $c['b'],
                    'marking_bobot' => $c['m'],
                    'pearl_bobot'   => $c['p'],
                    'color_bobot'   => $c['c'],
                    'finnage_bobot' => $c['fn'],
                ], $percentages) // Menggabungkan bobot utama dengan data persentase baru
            );
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

        $this->command->info('Point configs (with percentages) seeded & existing scores recomputed.');
    }
}