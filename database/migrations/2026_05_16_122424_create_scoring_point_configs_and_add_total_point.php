<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scoring_point_configs', function (Blueprint $table) {
            $table->id();
            $table->string('kategori')->unique();
            $table->decimal('overall_bobot', 5, 2)->default(0);
            $table->decimal('head_bobot', 5, 2)->default(0);
            $table->decimal('face_bobot', 5, 2)->default(0);
            $table->decimal('body_bobot', 5, 2)->default(0);
            $table->decimal('marking_bobot', 5, 2)->default(0);
            $table->decimal('pearl_bobot', 5, 2)->default(0);
            $table->decimal('color_bobot', 5, 2)->default(0);
            $table->decimal('finnage_bobot', 5, 2)->default(0);
            $table->timestamps();
        });

        Schema::table('scorings', function (Blueprint $table) {
            $table->decimal('total_point', 8, 5)->nullable()->after('total_nilai');
        });
    }

    public function down(): void
    {
        Schema::table('scorings', function (Blueprint $table) {
            $table->dropColumn('total_point');
        });
        Schema::dropIfExists('scoring_point_configs');
    }
};