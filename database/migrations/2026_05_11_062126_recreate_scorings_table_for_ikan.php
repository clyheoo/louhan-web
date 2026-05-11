<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Matikan cek foreign key sementara agar tabel bisa dihapus paksa
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Hapus tabel lama jika ada
        Schema::dropIfExists('scorings');
        
        // Nyalakan kembali cek foreign key
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Buat tabel baru yang bersih sesuai sistem Ikan
        Schema::create('scorings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ikan_id')->constrained()->onDelete('cascade');
            $table->foreignId('juri_id')->constrained('users');
            $table->string('kelas');
            $table->json('nilai_detail')->nullable();
            $table->integer('total_nilai')->default(0);
            $table->enum('status', ['draft', 'submitted'])->default('draft');
            $table->boolean('edited_by_grand_juri')->default(false);
            $table->foreignId('grand_juri_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Schema::dropIfExists('scorings');
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
};