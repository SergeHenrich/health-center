<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'order_number',
        'pharmacist_id',
        'supplier_id',
        'approved_by_id',
        'supplier_name',
        'supplier_contact',
        'delivery_address',
        'payment_terms',
        'status',
        'ordered_at',
        'expected_delivery',
        'received_at',
        'total_amount',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'ordered_at' => 'datetime',
            'expected_delivery' => 'date',
            'received_at' => 'datetime',
            'total_amount' => 'decimal:2',
        ];
    }

    public function pharmacist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pharmacist_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function approve(User $user): void
    {
        $this->update([
            'approved_by_id' => $user->id,
            'status' => 'approved',
        ]);
    }

    public function receive(): void
    {
        $this->update([
            'status' => 'received',
            'received_at' => now(),
        ]);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'draft');
    }
}
