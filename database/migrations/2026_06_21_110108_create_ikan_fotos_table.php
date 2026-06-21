<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ikan_fotos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ikan_id')->constrained('ikans')->cascadeOnDelete();
            $table->string('path');
            $table->unsignedInteger('size')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ikan_fotos');
    }
};