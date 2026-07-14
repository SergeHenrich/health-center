<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'lab_request_item_id',
        'lab_request_id',
        'technician_id',
        'validated_by_id',
        'result_value',
        'unit',
        'reference_range',
        'interpretation',
        'is_validated',
        'performed_at',
        'validated_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_validated' => 'boolean',
            'performed_at' => 'datetime',
            'validated_at' => 'datetime',
        ];
    }

    public function labRequestItem(): BelongsTo
    {
        return $this->belongsTo(LabRequestItem::class);
    }

    public function labRequest(): BelongsTo
    {
        return $this->belongsTo(LabRequest::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by_id');
    }

    public function validate(User $user): void
    {
        $this->update([
            'is_validated'    => true,
            'validated_by_id' => $user->id,
            'validated_at'    => now(),
        ]);
    }
}
