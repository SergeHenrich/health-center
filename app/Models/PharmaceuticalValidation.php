<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PharmaceuticalValidation extends Model
{
    protected $fillable = [
        'prescription_id', 'pharmacist_id', 'validated_at',
        'status', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'validated_at' => 'datetime',
        ];
    }

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class);
    }

    public function pharmacist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pharmacist_id');
    }

    public function interventions(): HasMany
    {
        return $this->hasMany(PharmaceuticalIntervention::class, 'validation_id');
    }
}
