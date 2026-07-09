<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TherapeuticSubstitution extends Model
{
    protected $fillable = [
        'medicine_id', 'substitute_medicine_id',
        'substitution_type', 'reason', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function substitute(): BelongsTo
    {
        return $this->belongsTo(Medicine::class, 'substitute_medicine_id');
    }
}
