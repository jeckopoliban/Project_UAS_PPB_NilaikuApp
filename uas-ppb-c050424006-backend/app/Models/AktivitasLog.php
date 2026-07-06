<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class AktivitasLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'mahasiswa_id',
        'aksi',
        'deskripsi',
    ];

    protected $dates = [
        'created_at',
    ];

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mahasiswa_id');
    }
}
