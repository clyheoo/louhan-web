<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ikan_bonus_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ikan_id')->constrained('ikans')->cascadeOnDelete();
            $table->string('bonus_type', 60);
            $table->integer('points')->default(100);
            $table->foreignId('added_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['ikan_id', 'bonus_type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('ikan_bonus_points');
    }
};