<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $mahasiswa_id
 * @property string $nama
 * @property bool $status_aktif
 * @property int|null $grading_template_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class TahunAkademik extends Model
{
    protected $fillable = [
        'mahasiswa_id',
        'nama',
        'status_aktif',
        'grading_template_id',
    ];

    protected $casts = [
        'status_aktif' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saved(function (TahunAkademik $tahunAkademik): void {
            if (! $tahunAkademik->status_aktif) {
                return;
            }

            // Keep exactly one active semester per mahasiswa when a semester
            // is marked active. If all are set inactive, it remains valid.
            self::query()
                ->where('mahasiswa_id', $tahunAkademik->mahasiswa_id)
                ->where('id', '!=', $tahunAkademik->id)
                ->where('status_aktif', true)
                ->update(['status_aktif' => false]);
        });
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mahasiswa_id');
    }

    public function mataKuliahs(): HasMany
    {
        return $this->hasMany(MataKuliah::class, 'tahun_akademik_id');
    }

    public function gradingTemplate(): BelongsTo
    {
        return $this->belongsTo(GradingTemplate::class, 'grading_template_id');
    }
}
