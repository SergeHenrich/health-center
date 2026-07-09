<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrescriptionItem extends Model
{
    protected $fillable = [
        'prescription_id',
        'medicine_id',
        'medicine_name',
        'dosage',
        'frequency',
        'duration',
        'quantity_prescribed',
        'quantity_dispensed',
        'route',
        'instructions',
    ];

    protected function casts(): array
    {
        return [
            'quantity_dispensed' => 'integer',
        ];
    }

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class);
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function getRemainingQuantity(): int
    {
        return $this->quantity_prescribed - $this->quantity_dispensed;
    }
}
