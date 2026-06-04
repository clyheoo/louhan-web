<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Cek kolom sudah ada belum (safety)
        if (!Schema::hasColumn('ikans', 'detail_anggota')) {
            Schema::table('ikans', function ($table) {
                $table->string('detail_anggota')->nullable()->after('nama_peserta');
            });
        }

        // ★ BACKFILL: Isi detail_anggota ikan yang sudah ada
        DB::statement("
            UPDATE ikans 
            INNER JOIN pesertas ON ikans.peserta_id = pesertas.id 
            SET ikans.detail_anggota = pesertas.detail_anggota 
            WHERE ikans.detail_anggota IS NULL
        ");
    }

    public function down()
    {
        // Tidak drop kolom karena mungkin sudah ada sebelumnya
    }
};