<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pesertas', function (Blueprint $table) {
            $table->timestamp('result_unlocked_at')->nullable()->after('is_mvp_submitted');
        });
    }

    public function down()
    {
        Schema::table('pesertas', function (Blueprint $table) {
            $table->dropColumn('result_unlocked_at');
        });
    }
};
