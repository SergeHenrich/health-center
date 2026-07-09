<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'medicine_id', 'warehouse_id', 'lot_number', 'expiry_date',
        'quantity_available', 'initial_quantity', 'status', 'unit_cost', 'received_at',
    ];

    protected function casts(): array
    {
        return [
            'expiry_date' => 'date',
            'received_at' => 'datetime',
            'unit_cost' => 'decimal:2',
        ];
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'stock_batch_id');
    }

    public function dispensationItems(): HasMany
    {
        return $this->hasMany(DispensationItem::class, 'stock_batch_id');
    }

    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function isLow(): bool
    {
        return $this->quantity_available <= 0;
    }

    public function consume(int $quantity): void
    {
        $this->decrement('quantity_available', $quantity);
        if ($this->quantity_available <= 0) {
            $this->update(['status' => 'depleted']);
        }
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpiringSoon($query, int $days = 90)
    {
        return $query->active()
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now()->addDays($days))
            ->where('expiry_date', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->whereNotNull('expiry_date')
            ->where('expiry_date', '<', now())
            ->where('quantity_available', '>', 0);
    }

    public function scopeByFefo($query)
    {
        return $query->active()
            ->where('quantity_available', '>', 0)
            ->orderBy('expiry_date')
            ->orderBy('received_at');
    }
}
