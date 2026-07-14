<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicationEvent extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'type', 'severity', 'status',
        'medicine_id', 'patient_id', 'prescription_id', 'dispensation_id',
        'reported_by', 'assigned_to',
        'description', 'cause', 'action_taken', 'corrective_actions',
        'occurred_at', 'resolved_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class);
    }

    public function dispensation(): BelongsTo
    {
        return $this->belongsTo(Dispensation::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
