<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scorings', function (Blueprint $table) {
            if (!Schema::hasColumn('scorings', 'raw_head_penalty')) {
                $table->json('raw_head_penalty')->nullable()->after('total_point');
            }
            if (!Schema::hasColumn('scorings', 'raw_face_penalty')) {
                $table->json('raw_face_penalty')->nullable()->after('raw_head_penalty');
            }
            if (!Schema::hasColumn('scorings', 'raw_body_penalty')) {
                $table->json('raw_body_penalty')->nullable()->after('raw_face_penalty');
            }
            if (!Schema::hasColumn('scorings', 'raw_finnage_penalty')) {
                $table->json('raw_finnage_penalty')->nullable()->after('raw_body_penalty');
            }
            if (!Schema::hasColumn('scorings', 'keterangan')) {
                $table->text('keterangan')->nullable()->after('raw_finnage_penalty');
            }
        });
    }

    public function down(): void
    {
        Schema::table('scorings', function (Blueprint $table) {
            $table->dropColumn([
                'raw_head_penalty',
                'raw_face_penalty',
                'raw_body_penalty',
                'raw_finnage_penalty',
                'keterangan',
            ]);
        });
    }
};