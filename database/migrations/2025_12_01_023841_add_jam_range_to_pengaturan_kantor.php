<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddJamRangeToPengaturanKantor extends Migration
{
    public function up()
    {
        Schema::table('pengaturan_kantor', function (Blueprint $table) {
            $table->time('jam_masuk_mulai')->nullable()->after('radius');
            $table->time('jam_masuk_selesai')->nullable()->after('jam_masuk_mulai');

            $table->time('jam_pulang_mulai')->nullable()->after('jam_masuk_selesai');
            $table->time('jam_pulang_selesai')->nullable()->after('jam_pulang_mulai');
        });
    }

    public function down()
    {
        Schema::table('pengaturan_kantor', function (Blueprint $table) {
            $table->dropColumn([
                'jam_masuk_mulai',
                'jam_masuk_selesai',
                'jam_pulang_mulai',
                'jam_pulang_selesai',
            ]);
        });
    }
}

