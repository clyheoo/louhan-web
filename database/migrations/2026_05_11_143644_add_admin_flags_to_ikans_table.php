<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ikans', function (Blueprint $table) {
            $table->string('dibuat_oleh')->default('user')->after('nomor_tank');
            $table->string('diubah_oleh')->nullable()->after('dibuat_oleh');
        });
    }

    public function down(): void
    {
        Schema::table('ikans', function (Blueprint $table) {
            $table->dropColumn(['dibuat_oleh', 'diubah_oleh']);
        });
    }
};
