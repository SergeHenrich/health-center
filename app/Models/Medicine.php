<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Medicine extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'atc_code',
        'name',
        'generic_name',
        'category',
        'therapeutic_class',
        'form',
        'strength',
        'manufacturer',
        'description',
        'requires_prescription',
        'is_active',
        'formulary_status',
        'is_narcotic',
        'is_psychotropic',
        'is_cold_chain',
        'max_temperature',
        'unit_price',
    ];

    protected function casts(): array
    {
        return [
            'requires_prescription' => 'boolean',
            'is_active' => 'boolean',
            'is_narcotic' => 'boolean',
            'is_psychotropic' => 'boolean',
            'is_cold_chain' => 'boolean',
            'max_temperature' => 'decimal:1',
            'unit_price' => 'decimal:2',
        ];
    }

    public function stock(): HasOne
    {
        return $this->hasOne(Stock::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function prescriptionItems(): HasMany
    {
        return $this->hasMany(PrescriptionItem::class);
    }

    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function dispensationItems(): HasMany
    {
        return $this->hasMany(DispensationItem::class);
    }

    public function therapeuticSubstitutions(): HasMany
    {
        return $this->hasMany(TherapeuticSubstitution::class, 'medicine_id');
    }

    public function substituteFor(): HasMany
    {
        return $this->hasMany(TherapeuticSubstitution::class, 'substitute_medicine_id');
    }

    public function commissionDecisions(): HasMany
    {
        return $this->hasMany(FormularyCommissionDecision::class);
    }

    public function warehouseStocks(): HasMany
    {
        return $this->hasMany(WarehouseStock::class);
    }

    public function narcoticRegisters(): HasMany
    {
        return $this->hasMany(NarcoticRegister::class);
    }

    public function medicationEvents(): HasMany
    {
        return $this->hasMany(MedicationEvent::class);
    }

    public function pharmacyDocuments(): HasMany
    {
        return $this->hasMany(PharmacyDocument::class);
    }

    public function getCurrentStock(): int
    {
        return $this->stock?->quantity_available ?? 0;
    }

    public function isLowStock(): bool
    {
        return $this->stock?->isLow() ?? false;
    }

    public function isOutOfStock(): bool
    {
        return $this->getCurrentStock() === 0;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('code', 'like', "%{$term}%")
              ->orWhere('generic_name', 'like', "%{$term}%")
              ->orWhere('category', 'like', "%{$term}%")
              ->orWhere('atc_code', 'like', "%{$term}%");
        });
    }

    public function scopeOnFormulary(Builder $query): Builder
    {
        return $query->where('formulary_status', '!=', 'non_inscrit');
    }

    public function scopeNarcotic(Builder $query): Builder
    {
        return $query->where('is_narcotic', true);
    }

    public function scopePsychotropic(Builder $query): Builder
    {
        return $query->where('is_psychotropic', true);
    }

    public function scopeColdChain(Builder $query): Builder
    {
        return $query->where('is_cold_chain', true);
    }
}
