<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class GradingTemplate extends Model
{
    protected $fillable = [
        'nama_template',
        'is_default',
        'mahasiswa_id',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(GradingTemplateItem::class);
    }

    public function mahasiswa()
    {
        return $this->belongsTo(User::class, 'mahasiswa_id');
    }
}
