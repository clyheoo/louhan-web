<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('scorings', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke peserta (ikan yang dinilai)
            $table->foreignId('peserta_id')->constrained()->onDelete('cascade');
            
            // Relasi ke user (juri yang menilai)
            $table->foreignId('juri_id')->constrained('users')->onDelete('cascade');
            
            $table->string('kategori'); // Overall, Head, Face, dll
            $table->string('kelas');    // A, B, C, dll
            
            // Menyimpan nilai detail dalam bentuk JSON (fleksibel untuk tiap kategori)
            // Contoh: {"size": 50, "bentuk": 30}
            $table->json('nilai_detail')->nullable();
            
            $table->integer('total_nilai')->default(0);
            $table->enum('status', ['draft', 'submitted'])->default('draft');
            
            $table->timestamps();
            
            // Mencegah juri menilai ikan yang sama di kategori & kelas yang sama sebanyak 2x
            $table->unique(['peserta_id', 'juri_id', 'kategori', 'kelas'], 'unique_scoring');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scorings');
    }
};
