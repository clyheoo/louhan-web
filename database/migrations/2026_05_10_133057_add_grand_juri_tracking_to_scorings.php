<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('scorings', function (Blueprint $table) {
            $table->unsignedBigInteger('grand_juri_id')->nullable()->after('juri_id');
            $table->json('nilai_detail_asli')->nullable()->after('nilai_detail');
            $table->boolean('edited_by_grand_juri')->default(false)->after('status');
        });
    }

    public function down()
    {
        Schema::table('scorings', function (Blueprint $table) {
            $table->dropColumn(['grand_juri_id', 'nilai_detail_asli', 'edited_by_grand_juri']);
        });
    }
};