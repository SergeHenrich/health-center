<?php

namespace App\Services\Pharmacy;

use App\Exceptions\InsufficientStockException;
use App\Models\Medicine;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Support\Facades\DB;

class StockDistributionService
{
    public function getWarehouseStock(Warehouse $warehouse, array $filters = [])
    {
        return WarehouseStock::where('warehouse_id', $warehouse->id)
            ->with('medicine')
            ->when($filters['q'] ?? null, fn($q, $v) => $q->whereHas('medicine', fn($m) => $m->search($v)))
            ->when($filters['low'] ?? null, fn($q) => $q->lowStock())
            ->orderBy('medicine_id')
            ->paginate(25);
    }

    public function adjustWarehouseStock(WarehouseStock $warehouseStock, string $type, int $quantity, string $reason): void
    {
        if ($type === 'out' && $warehouseStock->quantity_available < $quantity) {
            throw new InsufficientStockException(
                "Stock insuffisant pour {$warehouseStock->medicine->name}."
            );
        }

        match ($type) {
            'in' => $warehouseStock->addStock($quantity),
            'out' => $warehouseStock->removeStock($quantity),
            'adjustment' => $warehouseStock->update(['quantity_available' => $quantity]),
        };
    }

    public function transferStock(
        Warehouse $from,
        Warehouse $to,
        Medicine $medicine,
        int $quantity,
        ?string $lotNumber = null,
        ?string $reason = null
    ): void {
        DB::transaction(function () use ($from, $to, $medicine, $quantity, $lotNumber, $reason) {
            $sourceStock = WarehouseStock::where('warehouse_id', $from->id)
                ->where('medicine_id', $medicine->id)
                ->lockForUpdate()
                ->first();

            if (!$sourceStock) {
                throw new \App\Exceptions\InsufficientStockException(
                    "Aucun stock de {$medicine->name} trouvé dans {$from->name}."
                );
            }

            if ($sourceStock->quantity_available < $quantity) {
                throw new \App\Exceptions\InsufficientStockException(
                    "Stock insuffisant dans {$from->name} pour {$medicine->name}."
                );
            }

            $sourceStock->removeStock($quantity);

            $destStock = WarehouseStock::where('warehouse_id', $to->id)
                ->where('medicine_id', $medicine->id)
                ->lockForUpdate()
                ->first() ?? WarehouseStock::create([
                    'warehouse_id' => $to->id,
                    'medicine_id' => $medicine->id,
                    'quantity_available' => 0,
                    'minimum_quantity' => 10,
                    'maximum_quantity' => 500,
                    'last_updated_at' => now(),
                ]);

            $destStock->addStock($quantity);

            $centralStock = Stock::firstOrCreate(
                ['medicine_id' => $medicine->id],
                [
                    'quantity_available' => 0,
                    'minimum_quantity' => 10,
                    'maximum_quantity' => 500,
                    'last_updated_at' => now(),
                ]
            );

            StockMovement::create([
                'stock_id' => $centralStock->id,
                'warehouse_id' => $from->id,
                'destination_warehouse_id' => $to->id,
                'medicine_id' => $medicine->id,
                'user_id' => auth()->id(),
                'type' => 'transfer',
                'quantity' => $quantity,
                'lot_number' => $lotNumber,
                'reason' => $reason ?? "Transfert de {$from->name} vers {$to->name}",
                'moved_at' => now(),
            ]);
        });
    }
}
