<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengaturanKantor extends Model
{
    use HasFactory;
   // ✅ Tambahkan baris ini:
    protected $table = 'pengaturan_kantor';
    protected $fillable = [
        'nama_kantor',
  'latitude',
  'longitude',
  'radius',
  'jam_masuk_mulai',
  'jam_masuk_selesai',
  'jam_pulang_mulai',
  'jam_pulang_selesai'
    ];
}
