<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Pegawai extends Authenticatable
{
    use Notifiable;

    protected $table = 'pegawais';

    protected $fillable = [
        'nama',
        'nip',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}
