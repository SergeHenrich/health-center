<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Services\Pharmacy\StockDistributionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WarehouseController extends Controller
{
    public function __construct(private readonly StockDistributionService $stockService) {}

    public function index(): View
    {
        $warehouses = Warehouse::with(['stocks' => function ($q) {
            $q->selectRaw('warehouse_id, count(*) as total_medicines, sum(quantity_available) as total_qty')
                ->groupBy('warehouse_id');
        }])->orderBy('name')->get();

        return view('pharmacy.warehouses.index', compact('warehouses'));
    }

    public function create(): View
    {
        return view('pharmacy.warehouses.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:warehouses,code',
            'name' => 'required|string|max:200',
            'type' => 'required|in:central,unit_care,emergency,bloc,other',
            'location' => 'nullable|string|max:200',
        ]);

        Warehouse::create($validated + ['is_active' => true]);

        return redirect()->route('warehouses.index')
            ->with('success', 'Dépôt créé avec succès.');
    }

    public function edit(Warehouse $warehouse): View
    {
        return view('pharmacy.warehouses.edit', compact('warehouse'));
    }

    public function update(Request $request, Warehouse $warehouse): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'type' => 'required|in:central,unit_care,emergency,bloc,other',
            'location' => 'nullable|string|max:200',
            'is_active' => 'boolean',
        ]);

        $warehouse->update($validated);

        return redirect()->route('warehouses.index')
            ->with('success', 'Dépôt mis à jour.');
    }

    public function show(Warehouse $warehouse, Request $request): View
    {
        $stocks = $this->stockService->getWarehouseStock($warehouse, $request->only(['q', 'low']));
        $medicines = Medicine::active()->orderBy('name')->get(['id', 'name', 'code']);
        $warehouses = Warehouse::where('id', '!=', $warehouse->id)->orderBy('name')->get();

        return view('pharmacy.warehouses.show', compact('warehouse', 'stocks', 'medicines', 'warehouses'));
    }

    public function adjustStock(Request $request, WarehouseStock $warehouseStock): RedirectResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:in,out,adjustment',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string|max:255',
        ]);

        try {
            $this->stockService->adjustWarehouseStock(
                $warehouseStock,
                $validated['type'],
                $validated['quantity'],
                $validated['reason']
            );

            return back()->with('success', 'Stock ajusté dans le dépôt.');
        } catch (\App\Exceptions\InsufficientStockException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function transfer(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
            'medicine_id' => 'required|exists:medicines,id',
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:255',
        ]);

        $from = Warehouse::findOrFail($validated['from_warehouse_id']);
        $to = Warehouse::findOrFail($validated['to_warehouse_id']);
        $medicine = Medicine::findOrFail($validated['medicine_id']);

        try {
            $this->stockService->transferStock(
                $from, $to, $medicine, $validated['quantity'],
                null, $validated['reason']
            );

            return back()->with('success', 'Transfert effectué avec succès.');
        } catch (\App\Exceptions\InsufficientStockException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
