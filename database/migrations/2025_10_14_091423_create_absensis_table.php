<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')->constrained('pegawais')->onDelete('cascade');
            $table->date('tanggal');
            $table->time('jam');
            $table->string('foto');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->decimal('jarak', 10, 2);
            $table->enum('status', ['Hadir', 'Terlambat', 'Diluar Radius']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensis');
    }
};
