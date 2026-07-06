<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradingTemplateItem extends Model
{
    protected $fillable = [
        'grading_template_id',
        'batas_bawah',
        'batas_atas',
        'huruf_mutu',
        'indeks',
    ];

    public function gradingTemplate(): BelongsTo
    {
        return $this->belongsTo(GradingTemplate::class);
    }
}
