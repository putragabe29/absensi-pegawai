<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Broadcast extends Model
{
    // Kolom yang boleh diisi mass-assignment
    protected $fillable = ['judul', 'pesan'];
}
