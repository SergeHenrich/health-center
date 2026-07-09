<?php

namespace App\Http\Controllers\Pharmacy;

use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Pharmacy\StoreDispensationRequest;
use App\Models\Dispensation;
use App\Models\Prescription;
use App\Services\PharmacyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DispensationController extends Controller
{
    public function __construct(
        private readonly PharmacyService $pharmacyService,
    ) {}

    public function index(): View
    {
        Gate::authorize('pharmacy.dispense');

        $dispensations = Dispensation::with(['prescription.patient', 'pharmacist', 'invoice'])
            ->latest('dispensed_at')
            ->paginate(20);

        return view('pharmacy.dispensation.index', compact('dispensations'));
    }

    public function create(Request $request): View
    {
        Gate::authorize('pharmacy.dispense');

        $prescriptions = Prescription::with(['patient', 'items.medicine', 'latestValidation'])
            ->whereIn('status', ['pending', 'validated', 'validated_with_interventions', 'partially_dispensed'])
            ->latest()
            ->get();

        $prescriptions = $prescriptions->filter(function ($p) {
            return $p->latestValidation && in_array($p->latestValidation->status, ['approved', 'approved_with_interventions']);
        });

        return view('pharmacy.dispensation.create', compact('prescriptions'));
    }

    public function store(StoreDispensationRequest $request): RedirectResponse
    {
        try {
            $prescription = Prescription::with('items.medicine')->findOrFail($request->prescription_id);
            $itemsData = $request->input('items', []);

            $result = $this->pharmacyService->dispenseAndInvoice($prescription, $itemsData);

            return redirect()
                ->route('dispensations.show', $result['dispensation'])
                ->with('success', "Dispensation effectuée et facture générée.");
        } catch (InsufficientStockException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function show(Dispensation $dispensation): View
    {
        Gate::authorize('pharmacy.dispense');

        $dispensation->load(['prescription.patient', 'prescription.doctor', 'pharmacist', 'items.medicine', 'invoice']);
        return view('pharmacy.dispensation.show', compact('dispensation'));
    }

    public function destroy(Dispensation $dispensation): RedirectResponse
    {
        Gate::authorize('pharmacy.dispense');

        $error = $this->guardCannotReverse($dispensation)
              ?? $this->guardAlreadyReversed($dispensation)
              ?? $this->guardPrescriptionNotReversible($dispensation);

        if ($error) {
            return $error;
        }

        try {
            $this->pharmacyService->reverseDispensation($dispensation);
            return redirect()->route('dispensations.index')
                ->with('success', 'Dispensation annulée et stock restitué.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'annulation : ' . $e->getMessage());
        }
    }

    private function guardCannotReverse(Dispensation $dispensation): ?RedirectResponse
    {
        if ($dispensation->pharmacist_id !== auth()->id() && !auth()->user()->can('pharmacy.dispense')) {
            return back()->with('error', 'Vous n\'êtes pas autorisé à annuler cette dispensation.');
        }
        return null;
    }

    private function guardAlreadyReversed(Dispensation $dispensation): ?RedirectResponse
    {
        if (!$dispensation->exists) {
            return back()->with('error', 'Cette dispensation a déjà été annulée.');
        }
        return null;
    }

    private function guardPrescriptionNotReversible(Dispensation $dispensation): ?RedirectResponse
    {
        $prescription = $dispensation->prescription;
        if ($prescription->status === 'pending') {
            return back()->with('error', 'Cette dispensation ne peut pas être annulée : l\'ordonnance est déjà en état pending.');
        }
        return null;
    }
}
