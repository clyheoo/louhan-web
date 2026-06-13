<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('juri_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('juri_id')->constrained('users')->cascadeOnDelete();
            $table->string('kategori');
            $table->string('kelas')->nullable(); // null = seluruh kelas pada kategori ini (atau kategori tanpa kelas: Bonsai/Jumbo)
            $table->timestamps();
            $table->index(['juri_id', 'kategori']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('juri_assignments');
    }
};