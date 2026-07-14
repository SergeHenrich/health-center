<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Consultation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'medical_record_id',
        'doctor_id',
        'appointment_id',
        'consultation_date',
        'chief_complaint',
        'history_of_illness',
        'physical_examination',
        'clinical_notes',
        'status',
        'follow_up_date',
        'referred_to',
        'referral_reason',
    ];

    protected function casts(): array
    {
        return [
            'consultation_date' => 'date',
            'follow_up_date' => 'date',
        ];
    }

    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function patient()
    {
        return $this->hasOneThrough(Patient::class, MedicalRecord::class, 'id', 'id', 'medical_record_id', 'patient_id');
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class);
    }

    public function scopeByDoctor($query, int $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }
}
