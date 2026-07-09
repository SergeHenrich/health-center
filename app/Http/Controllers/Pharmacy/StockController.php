<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Models\Stock;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockController extends Controller
{
    public function index(Request $request): View
    {
        $medicines = Medicine::with('stock')
            ->when($request->q, fn($q) => $q->search($request->q))
            ->when($request->filter === 'low', fn($q) => $q->whereHas('stock', fn($s) => $s->lowStock()))
            ->when($request->filter === 'out', fn($q) => $q->whereHas('stock', fn($s) => $s->where('quantity_available', 0)))
            ->active()
            ->orderBy('name')
            ->paginate(25);

        return view('pharmacy.stock.index', compact('medicines'));
    }

    public function adjust(Request $request, Stock $stock): RedirectResponse
    {
        $validated = $request->validate([
            'type'       => 'required|in:in,out,adjustment',
            'quantity'   => 'required|integer|min:1',
            'reason'     => 'required|string|max:255',
            'lot_number' => 'nullable|string|max:100',
            'expiry_date'=> 'nullable|date|after:today',
        ]);

        if ($validated['type'] === 'in') {
            $stock->addStock($validated['quantity']);
        } elseif ($validated['type'] === 'out') {
            if ($stock->quantity_available < $validated['quantity']) {
                return back()->with('error', 'Stock insuffisant pour cette sortie.');
            }
            $stock->removeStock($validated['quantity']);
        } else {
            $stock->update(['quantity_available' => $validated['quantity']]);
        }

        \App\Models\StockMovement::create([
            'stock_id'    => $stock->id,
            'medicine_id' => $stock->medicine_id,
            'user_id'     => auth()->id(),
            'type'        => $validated['type'],
            'quantity'    => $validated['quantity'],
            'lot_number'  => $validated['lot_number'] ?? null,
            'expiry_date' => $validated['expiry_date'] ?? null,
            'reason'      => $validated['reason'],
            'moved_at'    => now(),
        ]);

        return back()->with('success', 'Stock ajusté avec succès.');
    }
}
