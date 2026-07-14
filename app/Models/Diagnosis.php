<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Diagnosis extends Model
{
    use HasFactory;

    protected $fillable = [
        'consultation_id',
        'icd10_code',
        'description',
        'type',
        'is_chronic',
    ];

    protected function casts(): array
    {
        return [
            'is_chronic' => 'boolean',
        ];
    }

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }
}
