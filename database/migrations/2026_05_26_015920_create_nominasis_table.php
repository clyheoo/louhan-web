<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('nominasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('juri_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('ikan_id')->constrained('ikans')->cascadeOnDelete();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['juri_id', 'status']);
            $table->index(['status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('nominasis');
    }
};