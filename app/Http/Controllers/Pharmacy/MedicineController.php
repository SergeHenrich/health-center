<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Models\Stock;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MedicineController extends Controller
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

    public function create(): View
    {
        return view('pharmacy.medicines.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code'                  => 'required|string|max:50|unique:medicines,code',
            'name'                  => 'required|string|max:200',
            'generic_name'          => 'nullable|string|max:200',
            'category'              => 'nullable|string|max:100',
            'form'                  => 'required|in:tablet,capsule,syrup,injection,cream,drops,other',
            'strength'              => 'nullable|string|max:100',
            'manufacturer'          => 'nullable|string|max:200',
            'requires_prescription' => 'boolean',
            'unit_price'            => 'required|numeric|min:0',
            'min_stock'             => 'nullable|integer|min:0',
        ], [
            'code.required'       => 'Le code est obligatoire.',
            'code.unique'         => 'Ce code est déjà utilisé.',
            'name.required'       => 'Le nom est obligatoire.',
            'unit_price.required' => 'Le prix unitaire est obligatoire.',
        ]);

        $medicine = Medicine::create([
            'code'                  => $validated['code'],
            'name'                  => $validated['name'],
            'generic_name'          => $validated['generic_name'] ?? null,
            'category'              => $validated['category'] ?? null,
            'form'                  => $validated['form'],
            'strength'              => $validated['strength'] ?? null,
            'manufacturer'          => $validated['manufacturer'] ?? null,
            'requires_prescription' => $validated['requires_prescription'] ?? false,
            'unit_price'            => $validated['unit_price'],
            'is_active'             => true,
        ]);

        Stock::create([
            'medicine_id'       => $medicine->id,
            'quantity_available'=> 0,
            'minimum_quantity'  => $validated['min_stock'] ?? 10,
            'maximum_quantity'  => 500,
            'last_updated_at'   => now(),
        ]);

        return redirect()
            ->route('medicines.index')
            ->with('success', "Médicament {$medicine->name} ajouté.");
    }

    public function edit(Medicine $medicine): View
    {
        return view('pharmacy.medicines.edit', compact('medicine'));
    }

    public function update(Request $request, Medicine $medicine): RedirectResponse
    {
        $validated = $request->validate([
            'name'                  => 'required|string|max:200',
            'generic_name'          => 'nullable|string|max:200',
            'category'              => 'nullable|string|max:100',
            'form'                  => 'required|in:tablet,capsule,syrup,injection,cream,drops,other',
            'strength'              => 'nullable|string|max:100',
            'unit_price'            => 'required|numeric|min:0',
            'requires_prescription' => 'boolean',
        ]);

        $medicine->update($validated);

        return back()->with('success', 'Médicament mis à jour.');
    }

    public function destroy(Medicine $medicine): RedirectResponse
    {
        $medicine->delete();
        return redirect()->route('medicines.index')->with('success', 'Médicament archivé.');
    }
}
