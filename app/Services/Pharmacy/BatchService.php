<?php

namespace App\Services\Pharmacy;

use App\Models\Medicine;
use App\Models\Stock;
use App\Models\StockBatch;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;

class BatchService
{
    public function getBatches(array $filters = [])
    {
        return StockBatch::with(['medicine', 'warehouse'])
            ->when($filters['medicine_id'] ?? null, fn($q, $v) => $q->where('medicine_id', $v))
            ->when($filters['warehouse_id'] ?? null, fn($q, $v) => $q->where('warehouse_id', $v))
            ->when($filters['status'] ?? null, fn($q, $v) => $q->where('status', $v))
            ->when($filters['expiring'] ?? null, fn($q) => $q->expiringSoon())
            ->when($filters['expired'] ?? null, fn($q) => $q->expired())
            ->latest()
            ->paginate(25);
    }

    public function receiveBatch(Medicine $medicine, array $data): StockBatch
    {
        return DB::transaction(function () use ($medicine, $data) {
            $batch = StockBatch::create([
                'medicine_id' => $medicine->id,
                'warehouse_id' => $data['warehouse_id'] ?? null,
                'lot_number' => $data['lot_number'],
                'expiry_date' => $data['expiry_date'] ?? null,
                'quantity_available' => $data['quantity'],
                'initial_quantity' => $data['quantity'],
                'status' => 'active',
                'unit_cost' => $data['unit_cost'] ?? null,
                'received_at' => now(),
            ]);

            $stock = Stock::firstOrCreate(
                ['medicine_id' => $medicine->id],
                ['quantity_available' => 0, 'minimum_quantity' => 10, 'maximum_quantity' => 500, 'last_updated_at' => now()]
            );
            $stock->addStock($data['quantity']);

            StockMovement::create([
                'stock_id' => $stock->id,
                'stock_batch_id' => $batch->id,
                'medicine_id' => $medicine->id,
                'user_id' => $data['user_id'] ?? auth()->id(),
                'type' => 'in',
                'quantity' => $data['quantity'],
                'unit_cost' => $data['unit_cost'] ?? null,
                'lot_number' => $data['lot_number'],
                'expiry_date' => $data['expiry_date'] ?? null,
                'reason' => 'Réception lot',
                'moved_at' => now(),
            ]);

            return $batch;
        });
    }

    public function pickBatches(Medicine $medicine, int $quantity): array
    {
        $batches = StockBatch::byFefo()
            ->where('medicine_id', $medicine->id)
            ->get();

        $picks = [];
        $remaining = $quantity;

        foreach ($batches as $batch) {
            if ($remaining <= 0) {
                break;
            }

            $take = min($remaining, $batch->quantity_available);
            $picks[] = ['batch' => $batch, 'quantity' => $take];
            $remaining -= $take;
        }

        if ($remaining > 0) {
            throw new \App\Exceptions\InsufficientStockException(
                "Stock insuffisant pour {$medicine->name} dans les lots disponibles."
            );
        }

        return $picks;
    }

    public function writeOff(StockBatch $batch, int $quantity, string $reason): void
    {
        DB::transaction(function () use ($batch, $quantity, $reason) {
            if ($batch->quantity_available < $quantity) {
                throw new \App\Exceptions\InsufficientStockException(
                    "Quantité insuffisante dans le lot {$batch->lot_number}."
                );
            }

            $batch->consume($quantity);
            $batch->update(['status' => $reason === 'expired' ? 'expired' : ($reason === 'damaged' ? 'written_off' : $batch->status)]);

            $stock = Stock::where('medicine_id', $batch->medicine_id)->first();
            if ($stock) {
                $stock->removeStock($quantity);
            }

            StockMovement::create([
                'stock_id' => $stock?->id,
                'stock_batch_id' => $batch->id,
                'medicine_id' => $batch->medicine_id,
                'user_id' => auth()->id(),
                'type' => 'out',
                'quantity' => $quantity,
                'lot_number' => $batch->lot_number,
                'reason' => "Mise au rebut ($reason)",
                'moved_at' => now(),
            ]);
        });
    }

    public function returnToSupplier(StockBatch $batch, int $quantity): void
    {
        DB::transaction(function () use ($batch, $quantity) {
            if ($batch->quantity_available < $quantity) {
                throw new \App\Exceptions\InsufficientStockException(
                    "Quantité insuffisante dans le lot {$batch->lot_number}."
                );
            }

            $batch->consume($quantity);

            $stock = Stock::where('medicine_id', $batch->medicine_id)->first();
            if ($stock) {
                $stock->removeStock($quantity);
            }

            StockMovement::create([
                'stock_id' => $stock?->id,
                'stock_batch_id' => $batch->id,
                'medicine_id' => $batch->medicine_id,
                'user_id' => auth()->id(),
                'type' => 'out',
                'quantity' => $quantity,
                'lot_number' => $batch->lot_number,
                'reason' => 'Retour fournisseur',
                'moved_at' => now(),
            ]);
        });
    }

    public function getExpiringBatches(int $days = 90)
    {
        return StockBatch::expiringSoon($days)->with('medicine')->get();
    }

    public function getExpiredBatches()
    {
        return StockBatch::expired()->with('medicine')->get();
    }
}
