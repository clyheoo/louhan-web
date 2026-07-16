<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('piagams', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('peserta_id');       // penerima (peserta / team)
            $table->string('kategori')->nullable();          // label opsional
            $table->string('kelas', 20)->nullable();         // label opsional
            $table->string('judul')->nullable();             // caption opsional
            $table->string('path');                          // lokasi file di disk 'public'
            $table->string('original_name')->nullable();
            $table->string('mime', 100)->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->unsignedBigInteger('uploaded_by')->nullable(); // admin user id
            $table->timestamps();

            $table->index('peserta_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('piagams');
    }
};