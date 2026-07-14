<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VitalSign extends Model
{
    use HasFactory;

    protected $fillable = [
        'medical_record_id',
        'nurse_id',
        'consultation_id',
        'temperature_c',
        'blood_pressure_systolic',
        'blood_pressure_diastolic',
        'heart_rate',
        'respiratory_rate',
        'oxygen_saturation',
        'weight_kg',
        'height_cm',
        'recorded_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'temperature_c'     => 'decimal:1',
            'oxygen_saturation' => 'decimal:1',
            'weight_kg'         => 'decimal:2',
            'height_cm'         => 'decimal:2',
            'recorded_at'       => 'datetime',
        ];
    }

    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    public function nurse(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nurse_id');
    }

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }
}
