<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('scorings', function (Blueprint $table) {
            $table->text('raw_head_penalty')->nullable()->after('status');
            $table->text('raw_face_penalty')->nullable()->after('raw_head_penalty');
            $table->text('raw_body_penalty')->nullable()->after('raw_face_penalty');
            $table->text('raw_finnage_penalty')->nullable()->after('raw_body_penalty');
            $table->text('keterangan')->nullable()->after('raw_finnage_penalty');
        });
    }

    public function down()
    {
        Schema::table('scorings', function (Blueprint $table) {
            $table->dropColumn(['raw_head_penalty', 'raw_face_penalty', 'raw_body_penalty', 'raw_finnage_penalty', 'keterangan']);
        });
    }
};
