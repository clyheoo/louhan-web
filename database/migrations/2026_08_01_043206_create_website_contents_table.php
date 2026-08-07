<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('website_contents', function (Blueprint $table) {
            $table->id();
            $table->string('section')->index(); // hero, about, jadwal, sponsor, faq, settings
            $table->string('key')->index();
            $table->longText('value')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('website_contents');
    }
};