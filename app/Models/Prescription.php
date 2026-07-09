<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prescription extends Model
{
    protected $fillable = [
        'consultation_id',
        'doctor_id',
        'patient_id',
        'prescription_number',
        'issued_at',
        'valid_until',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'datetime',
            'valid_until' => 'date',
        ];
    }

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PrescriptionItem::class);
    }

    public function dispensations(): HasMany
    {
        return $this->hasMany(Dispensation::class);
    }

    public function pharmaceuticalValidations(): HasMany
    {
        return $this->hasMany(PharmaceuticalValidation::class);
    }

    public function latestValidation()
    {
        return $this->hasOne(PharmaceuticalValidation::class)->latestOfMany('validated_at');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeDispensed($query)
    {
        return $query->where('status', 'dispensed');
    }
}
