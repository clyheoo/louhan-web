<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menambahkan pelacak pengunggah foto ikan.
 *
 * - uploaded_by   : id user yang mengunggah (juri/admin). NULL = foto lama (peserta).
 * - uploaded_role : 'juri' | 'admin' | NULL. NULL diperlakukan sebagai foto "lama" (peserta).
 *
 * Foto lama yang sudah ada di tabel TIDAK diubah — keduanya akan NULL,
 * sehingga otomatis ditampilkan sebagai foto "Lama (Peserta)".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ikan_fotos', function (Blueprint $table) {
            $table->unsignedBigInteger('uploaded_by')->nullable()->after('size');
            $table->string('uploaded_role', 20)->nullable()->after('uploaded_by');

            // Relasi opsional ke users; jika user dihapus, kolom di-NULL-kan (foto tetap aman).
            $table->foreign('uploaded_by')
                ->references('id')->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ikan_fotos', function (Blueprint $table) {
            // Nama default FK Laravel: ikan_fotos_uploaded_by_foreign
            $table->dropForeign(['uploaded_by']);
            $table->dropColumn(['uploaded_by', 'uploaded_role']);
        });
    }
};