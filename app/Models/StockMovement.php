<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'stock_id',
        'warehouse_id',
        'destination_warehouse_id',
        'medicine_id',
        'user_id',
        'type',
        'quantity',
        'unit_cost',
        'lot_number',
        'expiry_date',
        'reference_type',
        'reference_id',
        'reason',
        'moved_at',
    ];

    protected function casts(): array
    {
        return [
            'expiry_date' => 'date',
            'moved_at' => 'datetime',
            'unit_cost' => 'decimal:2',
        ];
    }

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function destinationWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'destination_warehouse_id');
    }
}
