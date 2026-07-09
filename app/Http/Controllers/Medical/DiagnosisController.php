<?php

namespace App\Http\Controllers\Medical;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Models\Diagnosis;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DiagnosisController extends Controller
{
    public function store(Request $request, Consultation $consultation): RedirectResponse
    {
        $validated = $request->validate([
            'icd10_code'  => 'nullable|string|max:20',
            'description' => 'required|string|min:3|max:1000',
            'type'        => 'required|in:primary,secondary,differential',
            'is_chronic'  => 'boolean',
        ], [
            'description.required' => 'La description du diagnostic est obligatoire.',
        ]);

        $consultation->diagnoses()->create($validated);

        return back()->with('success', 'Diagnostic enregistré.');
    }

    public function destroy(Diagnosis $diagnosis): RedirectResponse
    {
        $consultationId = $diagnosis->consultation_id;
        $diagnosis->delete();

        return back()->with('success', 'Diagnostic supprimé.');
    }
}
