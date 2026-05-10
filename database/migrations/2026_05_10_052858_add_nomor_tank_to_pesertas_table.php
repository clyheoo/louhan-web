<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pesertas', function (Blueprint $table) {
            // Menambahkan kolom nomor_tank, nullable (karena belum dapat undian), dan UNIQUE (mencegah duplikat)
            $table->unsignedInteger('nomor_tank')->nullable()->unique()->after('detail_anggota');
        });
    }

    public function down(): void
    {
        Schema::table('pesertas', function (Blueprint $table) {
            $table->dropColumn('nomor_tank');
        });
    }
};