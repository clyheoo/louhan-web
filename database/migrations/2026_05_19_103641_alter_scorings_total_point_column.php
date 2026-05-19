<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scorings', function (Blueprint $table) {
            $table->decimal('total_point', 10, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('scorings', function (Blueprint $table) {
            $table->decimal('total_point', 8, 2)->nullable()->change();
        });
    }
};