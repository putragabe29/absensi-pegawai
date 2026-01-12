<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Izin extends Model
{
    use HasFactory;

    protected $fillable = [
        'pegawai_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'jenis',
        'alasan',
        'status',
        'catatan_admin',
    ];

    public function pegawai()
    {
        return $this->belongsTo(\App\Models\Pegawai::class);
    }
}
