<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ✅ Cek di luar closure supaya aman
        if (!Schema::hasColumn('pegawais', 'role')) {
            Schema::table('pegawais', function (Blueprint $table) {
                $table->string('role')->default('pegawai');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('pegawais', 'role')) {
            Schema::table('pegawais', function (Blueprint $table) {
                $table->dropColumn('role');
            });
        }
    }
};
