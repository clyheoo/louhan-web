<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pesertas', function (Blueprint $table) {
            $table->boolean('is_mvp_submitted')->default(false)->after('detail_anggota');
        });
    }

    public function down()
    {
        Schema::table('pesertas', function (Blueprint $table) {
            $table->dropColumn('is_mvp_submitted');
        });
    }
};
