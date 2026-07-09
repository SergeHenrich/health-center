<?php

namespace App\Services\Pharmacy;

use App\Models\Medicine;
use App\Models\NarcoticRegister;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;

class NarcoticService
{
    public function getRegister(array $filters = [])
    {
        return NarcoticRegister::with(['medicine', 'pharmacist', 'patient'])
            ->when($filters['medicine_id'] ?? null, fn($q, $v) => $q->where('medicine_id', $v))
            ->when($filters['date_from'] ?? null, fn($q, $v) => $q->whereDate('recorded_at', '>=', $v))
            ->when($filters['date_to'] ?? null, fn($q, $v) => $q->whereDate('recorded_at', '<=', $v))
            ->latest('recorded_at')
            ->paginate(25);
    }

    public function recordEntry(Medicine $medicine, array $data): NarcoticRegister
    {
        return DB::transaction(function () use ($medicine, $data) {
            $lastBalance = NarcoticRegister::where('medicine_id', $medicine->id)
                ->orderBy('recorded_at', 'desc')
                ->orderBy('id', 'desc')
                ->value('balance_after') ?? 0;

            $quantityIn = $data['quantity_in'] ?? 0;
            $quantityOut = $data['quantity_out'] ?? 0;
            $balanceAfter = $lastBalance + $quantityIn - $quantityOut;

            // Lock central stock to prevent race condition
            $stock = Stock::where('medicine_id', $medicine->id)
                ->lockForUpdate()
                ->first();

            if ($stock) {
                if ($quantityIn > 0) {
                    $stock->update(['quantity_available' => $stock->quantity_available + $quantityIn]);
                }
                if ($quantityOut > 0) {
                    if ($stock->quantity_available < $quantityOut) {
                        throw new \App\Exceptions\InsufficientStockException(
                            "Stock insuffisant pour le stupéfiant {$medicine->name}."
                        );
                    }
                    $stock->update(['quantity_available' => $stock->quantity_available - $quantityOut]);
                }
            }

            return NarcoticRegister::create([
                'medicine_id' => $medicine->id,
                'pharmacist_id' => auth()->id(),
                'patient_id' => $data['patient_id'] ?? null,
                'lot_number' => $data['lot_number'] ?? null,
                'quantity_in' => $quantityIn,
                'quantity_out' => $quantityOut,
                'balance_after' => $balanceAfter,
                'prescriber_name' => $data['prescriber_name'] ?? null,
                'prescription_number' => $data['prescription_number'] ?? null,
                'notes' => $data['notes'] ?? null,
                'recorded_at' => now(),
            ]);
        });
    }

    public function getCurrentBalance(Medicine $medicine): int
    {
        $lastEntry = NarcoticRegister::where('medicine_id', $medicine->id)
            ->orderBy('recorded_at', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        return $lastEntry?->balance_after ?? $medicine->getCurrentStock();
    }

    public function getNarcoticMedicines()
    {
        return Medicine::narcotic()->active()->with('stock')->orderBy('name')->get();
    }
}
