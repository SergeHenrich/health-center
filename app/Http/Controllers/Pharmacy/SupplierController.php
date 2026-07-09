<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\SupplierContract;
use App\Models\SupplierEvaluation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        $suppliers = Supplier::withCount('contracts', 'purchaseOrders')
            ->when($request->q, fn($q) => $q->where(function ($query) use ($request) {
                $query->where('name', 'like', "%{$request->q}%")
                    ->orWhere('code', 'like', "%{$request->q}%");
            }))
            ->when($request->filter === 'active', fn($q) => $q->active())
            ->orderBy('name')
            ->paginate(20);

        return view('pharmacy.suppliers.index', compact('suppliers'));
    }

    public function create(): View
    {
        return view('pharmacy.suppliers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:30|unique:suppliers,code',
            'name' => 'required|string|max:200',
            'contact_name' => 'nullable|string|max:200',
            'email' => 'nullable|email|max:200',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'tax_id' => 'nullable|string|max:50',
            'category' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
        ], [
            'code.required' => 'Le code fournisseur est obligatoire.',
            'code.unique' => 'Ce code est déjà utilisé.',
            'name.required' => 'Le nom du fournisseur est obligatoire.',
        ]);

        Supplier::create($validated + ['is_active' => true]);

        return redirect()->route('suppliers.index')
            ->with('success', 'Fournisseur ajouté avec succès.');
    }

    public function show(Supplier $supplier): View
    {
        $supplier->load(['contracts', 'evaluations.evaluator', 'purchaseOrders' => function ($q) {
            $q->latest('ordered_at')->limit(10);
        }]);

        return view('pharmacy.suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier): View
    {
        return view('pharmacy.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'contact_name' => 'nullable|string|max:200',
            'email' => 'nullable|email|max:200',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'tax_id' => 'nullable|string|max:50',
            'category' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        $supplier->update($validated);

        return redirect()->route('suppliers.index')
            ->with('success', 'Fournisseur mis à jour.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        $supplier->delete();
        return redirect()->route('suppliers.index')
            ->with('success', 'Fournisseur archivé.');
    }

    public function storeContract(Request $request, Supplier $supplier): RedirectResponse
    {
        $validated = $request->validate([
            'contract_number' => 'required|string|max:50|unique:supplier_contracts,contract_number',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'terms' => 'nullable|string',
        ]);

        $supplier->contracts()->create($validated + ['status' => 'active']);

        return back()->with('success', 'Contrat ajouté.');
    }

    public function storeEvaluation(Request $request, Supplier $supplier): RedirectResponse
    {
        $validated = $request->validate([
            'quality_score' => 'nullable|integer|min:1|max:10',
            'delivery_score' => 'nullable|integer|min:1|max:10',
            'price_score' => 'nullable|integer|min:1|max:10',
            'comments' => 'nullable|string|max:1000',
        ]);

        $scores = collect($validated)->only(['quality_score', 'delivery_score', 'price_score'])->filter();
        $validated['overall_score'] = $scores->count() > 0 ? round($scores->avg(), 1) : null;
        $validated['evaluation_date'] = now();
        $validated['evaluated_by'] = auth()->id();

        $supplier->evaluations()->create($validated);

        return back()->with('success', 'Évaluation enregistrée.');
    }
}
