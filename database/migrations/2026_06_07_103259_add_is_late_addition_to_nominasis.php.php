<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('nominasis', function (Blueprint $table) {
            $table->boolean('is_late_addition')->default(false)->after('catatan');
        });
    }

    public function down(): void
    {
        Schema::table('nominasis', function (Blueprint $table) {
            $table->dropColumn('is_late_addition');
        });
    }
};