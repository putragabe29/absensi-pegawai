<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pegawais', function (Blueprint $table) {
            $table->id();
            $table->string('nip')->unique();
            $table->string('nama');
            $table->string('jabatan')->nullable();
            $table->string('password');
            $table->enum('role', ['admin', 'pegawai'])->default('pegawai');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pegawais');
    }
};
