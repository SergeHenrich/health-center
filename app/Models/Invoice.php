<?php

namespace App\Models;

use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Invoice extends Model
{
    use HasFactory, HasAuditLog;

    protected $fillable = [
        'patient_id',
        'created_by_id',
        'invoice_number',
        'invoice_date',
        'due_date',
        'status',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'amount_paid',
        'notes',
        'invoiceable_type',
        'invoiceable_id',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date'    => 'date',
            'due_date'        => 'date',
            'subtotal'        => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount'      => 'decimal:2',
            'total_amount'    => 'decimal:2',
            'amount_paid'     => 'decimal:2',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function invoiceable(): MorphTo
    {
        return $this->morphTo();
    }

    public function markAsPaid(): void
    {
        $this->update(['status' => 'paid']);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isOverdue(): bool
    {
        return $this->status === 'overdue';
    }

    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    public function getBalanceDue(): float
    {
        return max(0, (float) $this->total_amount - (float) $this->amount_paid);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue');
    }

    public function scopeUnpaid($query)
    {
        return $query->whereNotIn('status', ['paid', 'cancelled']);
    }
}
