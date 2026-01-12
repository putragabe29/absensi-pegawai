<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('absensis', function (Blueprint $table) {
        $table->enum('tipe', ['Masuk', 'Pulang'])->default('Masuk')->after('status');
    });
}

public function down(): void
{
    Schema::table('absensis', function (Blueprint $table) {
        $table->dropColumn('tipe');
    });
}
};
