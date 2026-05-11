<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('password_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('old_password')->nullable(); // Password lama (plain text)
            $table->string('new_password');             // Password baru (plain text)
            $table->string('changed_by');               // Nama Admin yang mengubah
            $table->timestamps();
        });
    }
};
