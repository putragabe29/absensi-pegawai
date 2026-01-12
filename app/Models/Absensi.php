<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    use HasFactory;

    protected $fillable = [
    'pegawai_id',
    'tanggal',
    'jam',
    'foto',
    'latitude',
    'longitude',
    'jarak',
    'status',
    'tipe', // ✅ tambahkan ini
];

    public function pegawai()
    {
        return $this->belongsTo(\App\Models\Pegawai::class, 'pegawai_id');
    }
}
