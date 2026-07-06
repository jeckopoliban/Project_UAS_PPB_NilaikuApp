<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'aksi',
        'deskripsi',
        'ip_address',
        'created_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
