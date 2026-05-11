<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scorings', function (Blueprint $table) {

            // nilai detail
            $table->json('nilai_detail')->nullable();

            $table->json('nilai_detail_asli')->nullable();

            // status
            $table->string('status')->default('draft');

            // edited
            $table->boolean('edited_by_grand_juri')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('scorings', function (Blueprint $table) {

            $table->dropColumn([
                'nilai_detail_asli',
                'status',
                'edited_by_grand_juri'
            ]);
        });
    }
};