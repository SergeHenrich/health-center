<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Dispensation;
use App\Models\Prescription;
use App\Services\Pharmacy\ClinicalPharmacyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PharmaceuticalValidationController extends Controller
{
    public function __construct(private readonly ClinicalPharmacyService $clinicalService) {}

    public function index(): View
    {
        Gate::authorize('pharmacy.validation');

        $pending = $this->clinicalService->getPendingValidations();

        return view('pharmacy.validations.index', compact('pending'));
    }

    public function show(Prescription $prescription): View
    {
        $prescription->load(['patient', 'doctor', 'items.medicine', 'pharmaceuticalValidations.pharmacist', 'pharmaceuticalValidations.interventions']);
        $validations = $this->clinicalService->getValidationHistory($prescription);

        return view('pharmacy.validations.show', compact('prescription', 'validations'));
    }

    public function validatePrescription(Request $request, Prescription $prescription): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected,modified',
            'notes' => 'nullable|string|max:1000',
        ]);

        $this->clinicalService->validatePrescription($prescription, $validated['status'], $validated['notes']);

        return redirect()->route('validations.index')
            ->with('success', 'Ordonnance validée avec succès.');
    }
}
