<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Dispensation;
use App\Models\DispensationItem;
use App\Models\Medicine;
use App\Models\PharmaceuticalValidation;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Stock;
use App\Models\StockBatch;
use App\Models\StockMovement;
use App\Services\Pharmacy\BatchService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PharmacyService
{
    public function __construct(
        private readonly BatchService $batchService,
        private readonly BillingService $billingService,
    ) {}

    public function dispense(Prescription $prescription, array $itemsData = []): Dispensation
    {
        return DB::transaction(function () use ($prescription, $itemsData) {
            $latestValidation = $prescription->latestValidation;
            if (!$latestValidation || !in_array($latestValidation->status, ['approved', 'approved_with_interventions'])) {
                throw new \RuntimeException(
                    "L'ordonnance {$prescription->prescription_number} n'a pas été validée pharmaceutiquement."
                );
            }

            if (empty($itemsData)) {
                foreach ($prescription->items as $item) {
                    $qty = $item->getRemainingQuantity();
                    if ($qty > 0) {
                        $picks = $this->batchService->pickBatches($item->medicine, $qty);
                        $itemsData[] = [
                            'prescription_item_id' => $item->id,
                            'medicine_id' => $item->medicine_id,
                            'quantity' => $qty,
                            'picks' => array_map(fn($p) => ['batch_id' => $p['batch']->id, 'quantity' => $p['quantity']], $picks),
                        ];
                    }
                }
            }

            $dispensation = Dispensation::create([
                'prescription_id' => $prescription->id,
                'pharmacist_id'   => auth()->id(),
                'dispensed_at'    => now(),
            ]);

            foreach ($itemsData as $itemData) {
                $qty = $itemData['quantity'];
                if ($qty <= 0) {
                    continue;
                }

                $prescriptionItem = $prescription->items->find($itemData['prescription_item_id']);
                if (!$prescriptionItem) {
                    continue;
                }

                $dispItem = DispensationItem::create([
                    'dispensation_id'      => $dispensation->id,
                    'prescription_item_id' => $itemData['prescription_item_id'],
                    'medicine_id'          => $itemData['medicine_id'],
                    'quantity_dispensed'   => $qty,
                    'unit_price'           => $prescriptionItem->medicine->unit_price,
                ]);

                if (isset($itemData['picks'])) {
                    $stock = Stock::firstOrCreate(
                        ['medicine_id' => $itemData['medicine_id']],
                        ['quantity_available' => 0, 'minimum_quantity' => 10, 'maximum_quantity' => 500, 'last_updated_at' => now()]
                    );

                    foreach ($itemData['picks'] as $pick) {
                        $batch = StockBatch::findOrFail($pick['batch_id']);
                        $batch->consume($pick['quantity']);

                        $stock->removeStock($pick['quantity']);

                        $dispItem->update(['stock_batch_id' => $batch->id, 'lot_number' => $batch->lot_number]);

                        StockMovement::create([
                            'stock_id'        => $stock->id,
                            'stock_batch_id'  => $batch->id,
                            'medicine_id'     => $itemData['medicine_id'],
                            'user_id'         => auth()->id(),
                            'type'            => 'out',
                            'quantity'        => $pick['quantity'],
                            'lot_number'      => $batch->lot_number,
                            'reference_type'  => 'dispensations',
                            'reference_id'    => $dispensation->id,
                            'moved_at'        => now(),
                        ]);
                    }
                } else {
                    $stock = Stock::where('medicine_id', $itemData['medicine_id'])->lockForUpdate()->first();
                    if ($stock) {
                        $stock->removeStock($qty);
                    }

                    StockMovement::create([
                        'medicine_id'    => $itemData['medicine_id'],
                        'user_id'        => auth()->id(),
                        'type'           => 'out',
                        'quantity'       => $qty,
                        'unit_cost'      => $prescriptionItem->medicine->unit_price,
                        'reference_type' => 'dispensations',
                        'reference_id'   => $dispensation->id,
                        'moved_at'       => now(),
                    ]);
                }

                $prescriptionItem->increment('quantity_dispensed', $qty);
            }

            // Recalculate prescription status based on all items
            $anyDispensed = false;
            $anyRemaining = false;
            foreach ($prescription->fresh()->items as $item) {
                if ($item->quantity_dispensed > 0) {
                    $anyDispensed = true;
                }
                if ($item->getRemainingQuantity() > 0) {
                    $anyRemaining = true;
                }
            }

            $newStatus = 'pending';
            if ($anyDispensed && !$anyRemaining) {
                $newStatus = 'dispensed';
            } elseif ($anyDispensed && $anyRemaining) {
                $newStatus = 'partially_dispensed';
            }

            $prescription->update([
                'status' => $newStatus,
            ]);

            return $dispensation->load('items.medicine');
        });
    }

    public function dispenseAndInvoice(Prescription $prescription, array $itemsData = []): array
    {
        return DB::transaction(function () use ($prescription, $itemsData) {
            $dispensation = $this->dispense($prescription, $itemsData);

            $invoiceItems = $dispensation->items->map(fn($di) => [
                'description'   => $di->medicine?->name
                    . ($di->medicine?->strength ? " {$di->medicine->strength}" : ''),
                'item_type'     => 'medicine',
                'quantity'      => $di->quantity_dispensed,
                'unit_price'    => $di->unit_price,
                'reference_id'  => $di->medicine_id,
            ])->toArray();

            $invoice = $this->billingService->generateInvoice([
                'patient_id'       => $prescription->patient_id,
                'due_date'         => today(),
                'notes'            => "Dispensation de l'ordonnance {$prescription->prescription_number}",
                'items'            => $invoiceItems,
                'invoiceable_type' => \App\Models\Dispensation::class,
                'invoiceable_id'   => $dispensation->id,
            ]);

            $dispensation->load('invoice');

            return ['dispensation' => $dispensation, 'invoice' => $invoice];
        });
    }

    public function reverseDispensation(Dispensation $dispensation): void
    {
        DB::transaction(function () use ($dispensation) {
            foreach ($dispensation->items as $item) {
                if ($item->stock_batch_id) {
                    $batch = StockBatch::find($item->stock_batch_id);
                    if ($batch) {
                        $batch->increment('quantity_available', $item->quantity_dispensed);
                        if ($batch->status === 'depleted' && $batch->quantity_available > 0) {
                            $batch->update(['status' => 'active']);
                        }
                    }
                }

                $stock = Stock::where('medicine_id', $item->medicine_id)
                    ->lockForUpdate()
                    ->first();

                if ($stock) {
                    $stock->update(['quantity_available' => $stock->quantity_available + $item->quantity_dispensed]);
                }

                $prescriptionItem = $item->prescriptionItem;
                if ($prescriptionItem) {
                    $prescriptionItem->decrement('quantity_dispensed', $item->quantity_dispensed);
                }

                StockMovement::create([
                    'stock_id'        => $stock?->id,
                    'stock_batch_id'  => $item->stock_batch_id,
                    'medicine_id'     => $item->medicine_id,
                    'user_id'         => auth()->id(),
                    'type'            => 'in',
                    'quantity'        => $item->quantity_dispensed,
                    'lot_number'      => $item->lot_number,
                    'reference_type'  => 'dispensation_reversal',
                    'reference_id'    => $dispensation->id,
                    'reason'          => 'Annulation dispensation',
                    'moved_at'        => now(),
                ]);
            }

            $prescription = $dispensation->prescription;
            $items = PrescriptionItem::where('prescription_id', $prescription->id)->get();

            $allDispensed = $items->every(fn($i) => $i->quantity_dispensed >= $i->quantity_prescribed);
            $anyDispensed = $items->contains(fn($i) => $i->quantity_dispensed > 0);
            $hasValidation = PharmaceuticalValidation::where('prescription_id', $prescription->id)
                ->whereIn('status', ['approved', 'approved_with_interventions'])
                ->exists();

            if ($allDispensed) {
                $prescription->update(['status' => 'dispensed']);
            } elseif ($anyDispensed) {
                $prescription->update(['status' => 'partially_dispensed']);
            } elseif ($hasValidation) {
                $prescription->update(['status' => 'validated']);
            } else {
                $prescription->update(['status' => 'pending']);
            }

            $dispensation->delete();
        });
    }

    /**
     * @throws InsufficientStockException
     */
    public function checkStock(Medicine $medicine, int $requiredQty): bool
    {
        $available = $medicine->getCurrentStock();

        if ($available < $requiredQty) {
            throw new InsufficientStockException(
                "Stock insuffisant pour {$medicine->name} : requis {$requiredQty}, disponible {$available}."
            );
        }

        return true;
    }

    public function receiveStock(int $medicineId, int $qty, float $unitCost, ?string $lot, string|Carbon|null $expiry, int $purchaseOrderItemId): void
    {
        DB::transaction(function () use ($medicineId, $qty, $unitCost, $lot, $expiry, $purchaseOrderItemId) {
            $stock = Stock::firstOrCreate(
                ['medicine_id' => $medicineId],
                [
                    'quantity_available' => 0,
                    'minimum_quantity'   => 10,
                    'maximum_quantity'   => 500,
                    'last_updated_at'    => now(),
                ]
            );

            $stock->addStock($qty);

            $batch = null;
            if ($lot) {
                $batch = StockBatch::create([
                    'medicine_id'       => $medicineId,
                    'lot_number'        => $lot,
                    'expiry_date'       => $expiry,
                    'quantity_available' => $qty,
                    'initial_quantity'  => $qty,
                    'status'            => 'active',
                    'unit_cost'         => $unitCost,
                    'received_at'       => now(),
                ]);
            }

            StockMovement::create([
                'stock_id'       => $stock->id,
                'stock_batch_id' => $batch?->id,
                'medicine_id'    => $medicineId,
                'user_id'        => auth()->id(),
                'type'           => 'in',
                'quantity'       => $qty,
                'unit_cost'      => $unitCost,
                'lot_number'     => $lot,
                'expiry_date'    => $expiry,
                'reference_type' => 'purchase_order_items',
                'reference_id'   => $purchaseOrderItemId,
                'moved_at'       => now(),
            ]);
        });
    }
}
