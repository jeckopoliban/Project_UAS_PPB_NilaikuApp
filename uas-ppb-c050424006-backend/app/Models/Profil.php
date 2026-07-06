<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profil extends Model
{
    protected $fillable = [
        'user_id',
        'nim_nis',
        'no_hp',
        'nama_institusi',
        'jenis_institusi',
        'program_studi',
        'target_ipk',
        'target_sks',
        'foto_profil',
    ];

    protected $casts = [
        'target_ipk' => 'decimal:2',
        'target_sks' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

}
