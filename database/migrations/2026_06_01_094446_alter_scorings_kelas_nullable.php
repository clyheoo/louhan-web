<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up()
    {
        Schema::table('scorings', function (Blueprint $table) {
            $table->string('kelas')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('scorings', function (Blueprint $table) {
            $table->string('kelas')->nullable(false)->default('')->change();
        });
    }
};
