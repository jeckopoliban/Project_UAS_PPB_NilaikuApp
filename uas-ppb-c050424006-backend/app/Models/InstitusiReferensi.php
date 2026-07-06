<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstitusiReferensi extends Model
{
    protected $fillable = [
        'nama_institusi',
        'jenis',
        'status_verifikasi',
    ];

    protected $casts = [
        'status_verifikasi' => 'boolean',
    ];
}
