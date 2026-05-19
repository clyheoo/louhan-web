<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scoring_point_configs', function (Blueprint $table) {
            // Overall
            $table->decimal('overall_point', 8, 2)->default(100)->after('overall_bobot');

            // Head Percentages
            $table->decimal('head_size_pct', 5, 2)->default(60)->after('head_bobot');
            $table->decimal('head_bentuk_k_pct', 5, 2)->default(40)->after('head_size_pct');

            // Face Percentages
            $table->decimal('face_face_pct', 5, 2)->default(100)->after('face_bobot');

            // Body Percentages
            $table->decimal('body_bentuk_pct', 5, 2)->default(50)->after('body_bobot');
            $table->decimal('body_proposional_pct', 5, 2)->default(40)->after('body_bentuk_pct');
            $table->decimal('body_pangkal_pct', 5, 2)->default(10)->after('body_proposional_pct');

            // Marking Percentages
            $table->decimal('marking_fullness_pct', 5, 2)->default(40)->after('marking_bobot');
            $table->decimal('marking_contrast_pct', 5, 2)->default(40)->after('marking_fullness_pct');
            $table->decimal('marking_bentuk_pct', 5, 2)->default(20)->after('marking_contrast_pct');

            // Pearl Percentages (mengikuti penulisan Anda: shinning & fullnes)
            $table->decimal('pearl_shinning_pct', 5, 2)->default(45)->after('pearl_bobot');
            $table->decimal('pearl_fullnes_pct', 5, 2)->default(35)->after('pearl_shinning_pct');
            $table->decimal('pearl_bentuk_pearl_pct', 5, 2)->default(20)->after('pearl_fullnes_pct');

            // Colour Percentages
            $table->decimal('color_komposisi_pct', 5, 2)->default(45)->after('color_bobot');
            $table->decimal('color_kecerahan_pct', 5, 2)->default(35)->after('color_komposisi_pct');
            $table->decimal('color_fullness_colour_pct', 5, 2)->default(20)->after('color_kecerahan_pct');

            // Finnage Percentages
            $table->decimal('finnage_bentuk_sirip_ekor_pct', 5, 2)->default(75)->after('finnage_bobot');
            $table->decimal('finnage_kecerahan_pct', 5, 2)->default(25)->after('finnage_bentuk_sirip_ekor_pct');
        });
    }

    public function down(): void
    {
        Schema::table('scoring_point_configs', function (Blueprint $table) {
            $table->dropColumn([
                'overall_point',
                'head_size_pct', 'head_bentuk_k_pct',
                'face_face_pct',
                'body_bentuk_pct', 'body_proposional_pct', 'body_pangkal_pct',
                'marking_fullness_pct', 'marking_contrast_pct', 'marking_bentuk_pct',
                'pearl_shinning_pct', 'pearl_fullnes_pct', 'pearl_bentuk_pearl_pct',
                'color_komposisi_pct', 'color_kecerahan_pct', 'color_fullness_colour_pct',
                'finnage_bentuk_sirip_ekor_pct', 'finnage_kecerahan_pct',
            ]);
        });
    }
};