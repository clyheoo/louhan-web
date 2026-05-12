<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Insert default range 1 sampai 1000
        DB::table('settings')->insert([
            ['key' => 'tank_range_min', 'value' => '1', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'tank_range_max', 'value' => '1000', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('settings');
    }
};