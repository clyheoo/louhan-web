<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('grand_juri_edits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scoring_id')->nullable()->constrained('scorings')->nullOnDelete();
            $table->foreignId('peserta_id')->constrained('pesertas')->cascadeOnDelete();
            $table->foreignId('grand_juri_id')->constrained('users')->cascadeOnDelete();
            $table->json('nilai_sebelum')->nullable()->comment('Snapshot nilai sebelum diubah');
            $table->json('nilai_sesudah')->nullable()->comment('Snapshot nilai setelah diubah');
            $table->json('changed_fields')->nullable()->comment('Hanya field yang diubah');
            $table->integer('total_sebelum')->default(0);
            $table->integer('total_sesudah')->default(0);
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('grand_juri_edits');
    }
};