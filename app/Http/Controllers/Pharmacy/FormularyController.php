<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Models\TherapeuticSubstitution;
use App\Services\Pharmacy\FormularyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FormularyController extends Controller
{
    public function __construct(private readonly FormularyService $formularyService) {}

    public function index(Request $request): View
    {
        $medicines = $this->formularyService->getFormulary($request->only(['status', 'therapeutic_class', 'q', 'narcotic']));

        return view('pharmacy.formulary.index', compact('medicines'));
    }

    public function show(Medicine $medicine): View
    {
        $medicine->load(['stock', 'commissionDecisions.decidedBy', 'therapeuticSubstitutions.substitute']);
        $substitutions = $this->formularyService->getSubstitutions($medicine);
        $commissionHistory = $this->formularyService->getCommissionHistory($medicine);

        return view('pharmacy.formulary.show', compact('medicine', 'substitutions', 'commissionHistory'));
    }

    public function updateStatus(Request $request, Medicine $medicine): RedirectResponse
    {
        $validated = $request->validate([
            'formulary_status' => 'required|in:inscrit,non_inscrit,substituable,retire',
            'atc_code' => 'nullable|string|max:10',
            'therapeutic_class' => 'nullable|string|max:100',
            'is_narcotic' => 'boolean',
            'is_psychotropic' => 'boolean',
        ]);

        $this->formularyService->addToFormulary($medicine, $validated);

        return back()->with('success', 'Statut du livret mis à jour.');
    }

    public function storeCommissionDecision(Request $request, Medicine $medicine): RedirectResponse
    {
        $validated = $request->validate([
            'decision' => 'required|in:admission,renewal,rejection,removal,modification',
            'justification' => 'required|string|max:1000',
            'decision_date' => 'required|date',
            'review_date' => 'nullable|date|after:decision_date',
            'reference_document' => 'nullable|string|max:200',
        ]);

        $this->formularyService->recordCommissionDecision($medicine, $validated);

        return back()->with('success', 'Décision de la commission enregistrée.');
    }

    public function storeSubstitution(Request $request, Medicine $medicine): RedirectResponse
    {
        $validated = $request->validate([
            'substitute_medicine_id' => 'required|exists:medicines,id|different:medicine_id',
            'substitution_type' => 'required|in:generic,therapeutic,alternative',
            'reason' => 'nullable|string|max:500',
        ]);

        $substitute = Medicine::findOrFail($validated['substitute_medicine_id']);

        $this->formularyService->registerSubstitution(
            $medicine,
            $substitute,
            $validated['substitution_type'],
            $validated['reason'] ?? null
        );

        return back()->with('success', 'Substitution enregistrée.');
    }

    public function destroySubstitution(TherapeuticSubstitution $substitution): RedirectResponse
    {
        $this->formularyService->deactivateSubstitution($substitution);

        return back()->with('success', 'Substitution désactivée.');
    }
}
