<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarehouseStock extends Model
{
    protected $fillable = [
        'warehouse_id', 'medicine_id', 'quantity_available',
        'minimum_quantity', 'maximum_quantity', 'last_updated_at',
    ];

    protected function casts(): array
    {
        return [
            'last_updated_at' => 'datetime',
        ];
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function addStock(int $quantity): void
    {
        $this->increment('quantity_available', $quantity);
    }

    public function removeStock(int $quantity): void
    {
        $this->decrement('quantity_available', $quantity);
    }

    public function isLow(): bool
    {
        return $this->quantity_available <= $this->minimum_quantity;
    }

    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereColumn('quantity_available', '<=', 'minimum_quantity');
    }
}
