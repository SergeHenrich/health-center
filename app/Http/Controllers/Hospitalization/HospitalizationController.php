<?php

namespace App\Http\Controllers\Hospitalization;

use App\Http\Controllers\Controller;
use App\Models\Bed;
use App\Models\Hospitalization;
use App\Models\Patient;
use App\Models\Room;
use App\Models\User;
use App\Services\HospitalizationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HospitalizationController extends Controller
{
    public function __construct(private readonly HospitalizationService $hospitalizationService) {}

    public function index(): View
    {
        $hospitalizations = Hospitalization::with(['patient', 'room', 'bed', 'admittingDoctor'])
            ->active()
            ->latest('admission_date')
            ->paginate(20);

        return view('hospitalization.admissions.index', compact('hospitalizations'));
    }

    public function create(): View
    {
        $patients = Patient::active()->orderBy('last_name')->get(['id', 'first_name', 'last_name', 'patient_code']);
        $rooms    = Room::with('beds')->active()->get();
        $doctors  = User::role(['general_practitioner', 'specialist'])->active()->get(['id', 'first_name', 'last_name']);
        $nurses   = User::role('nurse')->active()->get(['id', 'first_name', 'last_name']);

        return view('hospitalization.admissions.create', compact('patients', 'rooms', 'doctors', 'nurses'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_id'   => 'required|exists:patients,id',
            'room_id'      => 'required|exists:rooms,id',
            'bed_id'       => 'required|exists:beds,id',
            'doctor_id'    => 'required|exists:users,id',
            'nurse_id'     => 'nullable|exists:users,id',
            'reason'       => 'required|string|min:5|max:1000',
        ]);

        try {
            $hosp = $this->hospitalizationService->admit($validated);

            return redirect()
                ->route('hospitalizations.show', $hosp)
                ->with('success', "Patient admis — {$hosp->admission_number}");

        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function show(Hospitalization $hospitalization): View
    {
        $hospitalization->load(['patient', 'room', 'bed', 'admittingDoctor', 'attendingNurse', 'careRecords.nurse']);
        return view('hospitalization.admissions.show', compact('hospitalization'));
    }

    public function discharge(Request $request, Hospitalization $hospitalization): RedirectResponse
    {
        $validated = $request->validate([
            'discharge_summary'   => 'required|string|min:10|max:5000',
            'discharge_condition' => 'required|in:recovered,improved,unchanged,worsened,deceased',
        ], [
            'discharge_summary.required'   => 'Le résumé de sortie est obligatoire.',
            'discharge_condition.required' => 'L\'état de sortie est obligatoire.',
        ]);

        $this->hospitalizationService->discharge($hospitalization->id, $validated);

        return redirect()
            ->route('patients.show', $hospitalization->patient_id)
            ->with('success', 'Patient sorti avec succès.');
    }
}
