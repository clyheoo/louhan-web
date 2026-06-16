<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('contest_categories')) {
            Schema::create('contest_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->boolean('uses_kelas')->default(true); // false = kategori TANPA kelas (mis. Bonsai, Jumbo)
                $table->integer('urutan')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('contest_classes')) {
            Schema::create('contest_classes', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->integer('urutan')->default(0);
                $table->timestamps();
            });
        }

        // ── SEED nilai yang SEKARANG berlaku (agar perilaku hari pertama identik) ──
        $now = now();

        $categories = [
            ['name' => 'Cencu',       'uses_kelas' => true,  'urutan' => 1],
            ['name' => 'Chingwa',     'uses_kelas' => true,  'urutan' => 2],
            ['name' => 'Freemarking', 'uses_kelas' => true,  'urutan' => 3],
            ['name' => 'Goldenbase',  'uses_kelas' => true,  'urutan' => 4],
            ['name' => 'Klasik',      'uses_kelas' => true,  'urutan' => 5],
            ['name' => 'Bonsai',      'uses_kelas' => false, 'urutan' => 6],
            ['name' => 'Jumbo',       'uses_kelas' => false, 'urutan' => 7],
        ];
        foreach ($categories as $c) {
            DB::table('contest_categories')->updateOrInsert(
                ['name' => $c['name']],
                ['uses_kelas' => $c['uses_kelas'], 'urutan' => $c['urutan'], 'updated_at' => $now, 'created_at' => $now]
            );
        }

        foreach (['A', 'B', 'C', 'D', 'E'] as $i => $name) {
            DB::table('contest_classes')->updateOrInsert(
                ['name' => $name],
                ['urutan' => $i + 1, 'updated_at' => $now, 'created_at' => $now]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('contest_classes');
        Schema::dropIfExists('contest_categories');
    }
};