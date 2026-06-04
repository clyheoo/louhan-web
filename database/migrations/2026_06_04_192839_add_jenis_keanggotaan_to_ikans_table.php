<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('ikans', 'jenis_keanggotaan')) {
            Schema::table('ikans', function ($table) {
                $table->string('jenis_keanggotaan')->nullable()->after('detail_anggota');
            });
        }

        // ★ BACKFILL: Isi jenis_keanggotaan ikan yang sudah ada
        DB::statement("
            UPDATE ikans 
            INNER JOIN pesertas ON ikans.peserta_id = pesertas.id 
            SET ikans.jenis_keanggotaan = pesertas.jenis_keanggotaan 
            WHERE ikans.jenis_keanggotaan IS NULL
        ");
    }

    public function down()
    {
        // Tidak drop kolom untuk keamanan data
    }
};