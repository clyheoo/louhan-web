<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ikans', function (Blueprint $table) {
            if (!Schema::hasColumn('ikans', 'is_team_champion')) {
                $table->boolean('is_team_champion')->default(false)->after('is_mvp');
            }
        });

        Schema::table('pesertas', function (Blueprint $table) {
            if (!Schema::hasColumn('pesertas', 'is_team_champion_submitted')) {
                $table->boolean('is_team_champion_submitted')->default(false)->after('is_mvp_submitted');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ikans', function (Blueprint $table) {
            if (Schema::hasColumn('ikans', 'is_team_champion')) {
                $table->dropColumn('is_team_champion');
            }
        });

        Schema::table('pesertas', function (Blueprint $table) {
            if (Schema::hasColumn('pesertas', 'is_team_champion_submitted')) {
                $table->dropColumn('is_team_champion_submitted');
            }
        });
    }
};