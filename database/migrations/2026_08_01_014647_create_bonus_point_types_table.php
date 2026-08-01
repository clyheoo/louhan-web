<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bonus_point_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);                 // nama tampil, mis. "BEST OF THE BEST"
            $table->string('slug', 60)->unique();         // dipakai di ikan_bonus_points.bonus_type
            $table->boolean('adds_point')->default(true); // true = menambah point, false = nama saja
            $table->integer('point_value')->default(100); // besar bonus rank point (0 bila nama saja)
            $table->integer('sort_order')->default(0);
            $table->boolean('is_system')->default(false); // bawaan (tidak bisa dihapus)
            $table->string('icon', 40)->nullable();
            $table->timestamps();
        });

        // Seed 7 jenis bawaan supaya perilaku IDENTIK dengan sekarang.
        $now = now();
        $seed = [
            ['name' => 'BEST OF THE BEST', 'slug' => 'best_of_the_best', 'icon' => 'fa-gem',      'sort_order' => 1],
            ['name' => 'BEST OF SHOW',     'slug' => 'best_of_show',     'icon' => 'fa-star',     'sort_order' => 2],
            ['name' => 'GRAND CHAMPION',   'slug' => 'grand_champion',   'icon' => 'fa-crown',    'sort_order' => 3],
            ['name' => 'YOUNG CHAMPION',   'slug' => 'young_champion',   'icon' => 'fa-medal',    'sort_order' => 4],
            ['name' => 'JUNIOR CHAMPION',  'slug' => 'junior',           'icon' => 'fa-award',    'sort_order' => 5],
            ['name' => 'BABY CHAMPION',    'slug' => 'baby_champion',    'icon' => 'fa-baby',     'sort_order' => 6],
            ['name' => 'MINI CHAMPION',    'slug' => 'mini_champion',    'icon' => 'fa-seedling', 'sort_order' => 7],
        ];
        foreach ($seed as $row) {
            DB::table('bonus_point_types')->insert(array_merge($row, [
                'adds_point'  => true,
                'point_value' => 100,
                'is_system'   => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('bonus_point_types');
    }
};