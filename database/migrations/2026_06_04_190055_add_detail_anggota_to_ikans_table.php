<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('ikans', function (Blueprint $table) {
            $table->string('detail_anggota')->nullable()->after('nama_peserta');
        });

        // ★ BACKFILL: Isi detail_anggota ikan yang sudah ada dengan nilai dari pesertanya
        DB::statement("
            UPDATE ikans 
            INNER JOIN pesertas ON ikans.peserta_id = pesertas.id 
            SET ikans.detail_anggota = pesertas.detail_anggota 
            WHERE ikans.detail_anggota IS NULL
        ");
    }

    public function down()
    {
        Schema::table('ikans', function (Blueprint $table) {
            $table->dropColumn('detail_anggota');
        });
    }
};