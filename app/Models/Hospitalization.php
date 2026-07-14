<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hospitalization extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'room_id',
        'bed_id',
        'admitting_doctor_id',
        'attending_nurse_id',
        'admission_number',
        'admission_date',
        'discharge_date',
        'reason_for_admission',
        'status',
        'discharge_summary',
        'discharge_condition',
    ];

    protected function casts(): array
    {
        return [
            'admission_date' => 'datetime',
            'discharge_date' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function bed(): BelongsTo
    {
        return $this->belongsTo(Bed::class);
    }

    public function admittingDoctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admitting_doctor_id');
    }

    public function attendingNurse(): BelongsTo
    {
        return $this->belongsTo(User::class, 'attending_nurse_id');
    }

    public function careRecords(): HasMany
    {
        return $this->hasMany(CareRecord::class);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['admitted', 'in_care']);
    }

    public function discharge(string $summary, string $condition): void
    {
        $this->update([
            'status'              => 'discharged',
            'discharge_date'      => now(),
            'discharge_summary'   => $summary,
            'discharge_condition' => $condition,
        ]);
    }
}
