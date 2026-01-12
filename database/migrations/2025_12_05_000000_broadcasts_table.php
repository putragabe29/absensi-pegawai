<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Membuat tabel broadcasts
    public function up(): void
    {
        Schema::create('broadcasts', function (Blueprint $table) {
            $table->id();                // Primary key
            $table->string('judul');     // Judul pesan broadcast
            $table->text('pesan');       // Isi pesan broadcast
            $table->timestamps();        // created_at & updated_at
        });
    }

    // Menghapus tabel jika rollback
    public function down(): void
    {
        Schema::dropIfExists('broadcasts');
    }
};
