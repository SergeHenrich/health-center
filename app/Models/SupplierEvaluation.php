<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierEvaluation extends Model
{
    protected $fillable = [
        'supplier_id', 'evaluated_by', 'evaluation_date',
        'quality_score', 'delivery_score', 'price_score',
        'overall_score', 'comments',
    ];

    protected function casts(): array
    {
        return [
            'evaluation_date' => 'date',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }
}
