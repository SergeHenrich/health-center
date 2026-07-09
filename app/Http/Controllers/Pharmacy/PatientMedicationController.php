<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\View\View;

class PatientMedicationController extends Controller
{
    public function __invoke(Patient $patient): View
    {
        $patient->load(['prescriptions.items.medicine', 'prescriptions.doctor']);

        $activePrescriptions = $patient->prescriptions
            ->whereIn('status', ['active', 'pending', 'partially_dispensed']);

        $dispensations = $patient->dispensations()
            ->with('pharmacist', 'prescription', 'items')
            ->latest('dispensed_at')
            ->get();

        $medicationEvents = $patient->medicationEvents()
            ->latest('occurred_at')
            ->get();

        return view('pharmacy.patient.medication_profile', compact(
            'patient', 'activePrescriptions', 'dispensations', 'medicationEvents'
        ));
    }
}
