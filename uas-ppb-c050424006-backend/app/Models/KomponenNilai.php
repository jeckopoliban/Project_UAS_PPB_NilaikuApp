<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KomponenNilai extends Model
{
    protected $fillable = [
        'mahasiswa_id',
        'mata_kuliah_id',
        'nama_komponen',
        'nilai_angka',
        'bobot_persen',
    ];

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mahasiswa_id');
    }

    public function mataKuliah(): BelongsTo
    {
        return $this->belongsTo(MataKuliah::class, 'mata_kuliah_id');
    }
}
