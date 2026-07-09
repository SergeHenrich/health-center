<?php

namespace App\Models;

use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    use HasFactory, SoftDeletes, HasAuditLog;

    protected $table = 'patients';

    protected $fillable = [
        'user_id', 'patient_code', 'first_name', 'last_name',
        'date_of_birth', 'gender', 'blood_type', 'address', 'city',
        'phone', 'email', 'emergency_contact_name', 'emergency_contact_phone',
        'insurance_number', 'insurance_provider', 'is_active',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'is_active'     => 'boolean',
        'deleted_at'    => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function medicalRecord(): HasOne
    {
        return $this->hasOne(MedicalRecord::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function hospitalizations(): HasMany
    {
        return $this->hasMany(Hospitalization::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function labRequests(): HasMany
    {
        return $this->hasMany(LabRequest::class);
    }

    public function queues(): HasMany
    {
        return $this->hasMany(Queue::class);
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    public function dispensations(): HasManyThrough
    {
        return $this->hasManyThrough(Dispensation::class, Prescription::class);
    }

    public function medicationEvents(): HasMany
    {
        return $this->hasMany(MedicationEvent::class);
    }

    // ── Business Methods ─────────────────────────────────────

    public function getFullName(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getAge(): ?int
    {
        return $this->date_of_birth?->age;
    }

    public function getActiveHospitalization(): ?Hospitalization
    {
        return $this->hospitalizations()
            ->whereIn('status', ['admitted', 'in_care'])
            ->latest()
            ->first();
    }

    public function getLastConsultation(): ?Consultation
    {
        return $this->medicalRecord?->consultations()
            ->latest('consultation_date')
            ->first();
    }

    public function isHospitalized(): bool
    {
        return $this->getActiveHospitalization() !== null;
    }

    // ── Scopes ───────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('first_name', 'like', "%{$term}%")
              ->orWhere('last_name', 'like', "%{$term}%")
              ->orWhere('patient_code', 'like', "%{$term}%")
              ->orWhere('phone', 'like', "%{$term}%");
        });
    }
}
