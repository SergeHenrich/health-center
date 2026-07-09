<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PharmaceuticalIntervention extends Model
{
    protected $fillable = [
        'validation_id', 'prescription_item_id', 'intervention_type',
        'severity', 'description', 'action_taken', 'status',
    ];

    public function validation(): BelongsTo
    {
        return $this->belongsTo(PharmaceuticalValidation::class, 'validation_id');
    }

    public function prescriptionItem(): BelongsTo
    {
        return $this->belongsTo(PrescriptionItem::class);
    }
}
