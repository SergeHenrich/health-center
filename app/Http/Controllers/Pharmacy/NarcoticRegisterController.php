<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Services\Pharmacy\NarcoticService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class NarcoticRegisterController extends Controller
{
    public function __construct(private readonly NarcoticService $narcoticService) {}

    public function index(Request $request): View
    {
        Gate::authorize('pharmacy.narcotics');

        $registers = $this->narcoticService->getRegister($request->only(['medicine_id', 'date_from', 'date_to']));
        $medicines = $this->narcoticService->getNarcoticMedicines();

        return view('pharmacy.narcotics.index', compact('registers', 'medicines'));
    }

    public function show(Medicine $medicine): View
    {
        Gate::authorize('pharmacy.narcotics');

        $medicine->load('stock');

        return view('pharmacy.narcotics.show', compact('medicine', 'balance', 'entries'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'medicine_id' => 'required|exists:medicines,id',
            'quantity_in' => 'required_without:quantity_out|integer|min:0',
            'quantity_out' => 'required_without:quantity_in|integer|min:0',
            'patient_id' => 'nullable|exists:patients,id',
            'prescriber_name' => 'nullable|string|max:200',
            'prescription_number' => 'nullable|string|max:50',
            'lot_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        $medicine = Medicine::findOrFail($validated['medicine_id']);

        try {
            $this->narcoticService->recordEntry($medicine, $validated);

            return redirect()->route('narcotics.index')
                ->with('success', 'Entrée enregistrée dans le registre des stupéfiants.');
        } catch (\App\Exceptions\InsufficientStockException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
