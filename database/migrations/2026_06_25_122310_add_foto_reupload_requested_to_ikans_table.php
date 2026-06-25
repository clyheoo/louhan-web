<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Flag "minta upload ulang" foto ikan.
 *
 * - foto_reupload_requested = false (default): perilaku normal.
 *     -> Juri boleh upload HANYA jika ikan belum punya foto.
 *     -> Setelah foto terkirim, juri TERKUNCI (hanya admin yang bisa ubah).
 *
 * - foto_reupload_requested = true: admin meminta juri mengunggah ulang.
 *     -> Kunci juri dibuka sementara; juri boleh upload lagi.
 *     -> Otomatis kembali false setelah juri berhasil upload.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ikans', function (Blueprint $table) {
            $table->boolean('foto_reupload_requested')->default(false)->after('is_locked');
        });
    }

    public function down(): void
    {
        Schema::table('ikans', function (Blueprint $table) {
            $table->dropColumn('foto_reupload_requested');
        });
    }
};