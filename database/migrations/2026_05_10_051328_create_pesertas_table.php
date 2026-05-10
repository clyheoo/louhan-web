<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pesertas', function (Blueprint $table) {
            $table->id();
            
            // User yang mendaftarkan (opsional, tapi bagus untuk tracking admin)
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            
            // Sesuai dengan form yang dibuat
            $table->string('nama_peserta');
            $table->enum('kategori', ['Cencu', 'Chginwa', 'Freemarking', 'Goldenbase', 'Klasik', 'Bonsai', 'Jumbo']);
            $table->enum('kelas', ['A', 'B', 'C', 'D', 'E']);
            $table->enum('jenis_keanggotaan', ['perorangan', 'team']);
            $table->string('detail_anggota'); // Isinya bisa Kota Asal atau Nama Team
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesertas');
    }
};