<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'description',
        'item_type',
        'reference_id',
        'quantity',
        'unit_price',
        'discount_percent',
        'total_price',
    ];

    protected function casts(): array
    {
        return [
            'quantity'         => 'decimal:2',
            'unit_price'       => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'total_price'      => 'decimal:2',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
