<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scorings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('peserta_id')
                  ->constrained('pesertas')
                  ->cascadeOnDelete();

            $table->foreignId('juri_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->integer('total_nilai')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scorings');
    }
};