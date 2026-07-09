<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Http\Requests\Patient\StorePatientRequest;
use App\Http\Requests\Patient\UpdatePatientRequest;
use App\Services\PatientService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PatientController extends Controller
{
    public function __construct(private readonly PatientService $patientService) {}

    /**
     * List all patients with search.
     */
    public function index(Request $request): View
    {
        $patients = $this->patientService->search($request->get('q'));

        return view('patients.index', compact('patients'));
    }

    /**
     * Show patient registration form.
     */
    public function create(): View
    {
        return view('patients.create');
    }

    /**
     * Store a new patient.
     */
    public function store(StorePatientRequest $request): RedirectResponse
    {
        $patient = $this->patientService->register($request->validated());

        return redirect()
            ->route('patients.show', $patient)
            ->with('success', "Patient {$patient->getFullName()} enregistré avec succès.");
    }

    /**
     * Show full patient dossier.
     */
    public function show(int $id): View
    {
        $patient = $this->patientService->findOrFail($id);

        return view('patients.show', compact('patient'));
    }

    /**
     * Show edit form.
     */
    public function edit(int $id): View
    {
        $patient = $this->patientService->findOrFail($id);

        return view('patients.edit', compact('patient'));
    }

    /**
     * Update patient data.
     */
    public function update(UpdatePatientRequest $request, int $id): RedirectResponse
    {
        $patient = $this->patientService->update($id, $request->validated());

        return redirect()
            ->route('patients.show', $patient)
            ->with('success', 'Dossier patient mis à jour.');
    }

    /**
     * Soft-delete a patient.
     */
    public function destroy(int $id): RedirectResponse
    {
        $this->patientService->delete($id);

        return redirect()
            ->route('patients.index')
            ->with('success', 'Patient archivé.');
    }
}
