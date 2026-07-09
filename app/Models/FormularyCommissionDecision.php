<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormularyCommissionDecision extends Model
{
    protected $fillable = [
        'medicine_id', 'decided_by', 'decision', 'justification',
        'decision_date', 'review_date', 'reference_document',
    ];

    protected function casts(): array
    {
        return [
            'decision_date' => 'date',
            'review_date' => 'date',
        ];
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function decidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}
