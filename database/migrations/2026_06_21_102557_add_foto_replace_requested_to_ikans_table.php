<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ikans', function (Blueprint $table) {
            $table->boolean('foto_replace_requested')->default(false)->after('foto_path');
        });
    }

    public function down(): void
    {
        Schema::table('ikans', function (Blueprint $table) {
            $table->dropColumn('foto_replace_requested');
        });
    }
};