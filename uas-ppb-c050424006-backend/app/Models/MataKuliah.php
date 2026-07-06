<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $mahasiswa_id
 * @property int $tahun_akademik_id
 * @property string $nama_mk
 * @property int $sks
 * @property string|null $nama_komponen_penilaian
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class MataKuliah extends Model
{
    protected $fillable = [
        'mahasiswa_id',
        'tahun_akademik_id',
        'nama_mk',
        'sks',
        'nama_komponen_penilaian',
    ];

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mahasiswa_id');
    }

    public function tahunAkademik(): BelongsTo
    {
        return $this->belongsTo(TahunAkademik::class, 'tahun_akademik_id');
    }

    public function komponenNilais(): HasMany
    {
        return $this->hasMany(KomponenNilai::class, 'mata_kuliah_id');
    }
}
