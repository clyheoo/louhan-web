<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Buat tabel baru untuk menampung banyak ikan per peserta
        Schema::create('ikans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peserta_id')->constrained()->onDelete('cascade');
            $table->string('kategori');
            $table->string('kelas');
            $table->integer('nomor_tank')->nullable(); // Kosong jika belum diundi
            $table->timestamps();
        });

        // 2. Hapus kolom yang sudah pindah ke tabel ikans (Karena DB sudah di-drop tadi, ini aman)
        Schema::table('pesertas', function (Blueprint $table) {
            $table->dropColumn(['kategori', 'kelas', 'nomor_tank']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('ikans');
        
        Schema::table('pesertas', function (Blueprint $table) {
            $table->string('kategori')->nullable();
            $table->string('kelas')->nullable();
            $table->integer('nomor_tank')->nullable();
        });
    }
};