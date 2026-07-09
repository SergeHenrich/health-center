<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use App\Services\AppointmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function __construct(private readonly AppointmentService $appointmentService) {}

    public function index(Request $request): View
    {
        $appointments = Appointment::with(['patient', 'doctor'])
            ->when($request->date, fn($q) => $q->whereDate('appointment_date', $request->date))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when(
                auth()->user()->hasRole('general_practitioner') || auth()->user()->hasRole('specialist'),
                fn($q) => $q->byDoctor(auth()->id())
            )
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->paginate(20);

        $doctors = User::role(['general_practitioner', 'specialist'])->active()->get();

        return view('appointments.index', compact('appointments', 'doctors'));
    }

    public function create(): View
    {
        $patients = Patient::active()->orderBy('last_name')->get(['id', 'first_name', 'last_name', 'patient_code']);
        $doctors  = User::role(['general_practitioner', 'specialist'])->active()->get(['id', 'first_name', 'last_name']);

        return view('appointments.create', compact('patients', 'doctors'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_id'       => 'required|exists:patients,id',
            'doctor_id'        => 'required|exists:users,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required',
            'type'             => 'required|in:consultation,lab,followup,specialist,emergency',
            'reason'           => 'nullable|string|max:500',
            'duration_minutes' => 'nullable|integer|min:10|max:120',
        ], [
            'patient_id.required'       => 'Le patient est obligatoire.',
            'doctor_id.required'        => 'Le médecin est obligatoire.',
            'appointment_date.required' => 'La date est obligatoire.',
            'appointment_date.after_or_equal' => 'La date doit être aujourd\'hui ou dans le futur.',
            'appointment_time.required' => 'L\'heure est obligatoire.',
        ]);

        $validated['receptionist_id'] = auth()->id();
        $appointment = Appointment::create($validated);

        return redirect()
            ->route('appointments.index')
            ->with('success', 'Rendez-vous planifié avec succès.');
    }

    public function show(Appointment $appointment): View
    {
        $appointment->load(['patient', 'doctor', 'consultation']);
        return view('appointments.show', compact('appointment'));
    }

    public function edit(Appointment $appointment): View
    {
        $patients = Patient::active()->orderBy('last_name')->get(['id', 'first_name', 'last_name', 'patient_code']);
        $doctors  = User::role(['general_practitioner', 'specialist'])->active()->get(['id', 'first_name', 'last_name']);
        return view('appointments.edit', compact('appointment', 'patients', 'doctors'));
    }

    public function update(Request $request, Appointment $appointment): RedirectResponse
    {
        $validated = $request->validate([
            'appointment_date' => 'required|date',
            'appointment_time' => 'required',
            'doctor_id'        => 'required|exists:users,id',
            'status'           => 'required|in:scheduled,confirmed,arrived,in_progress,done,cancelled,no_show',
            'reason'           => 'nullable|string|max:500',
            'notes'            => 'nullable|string|max:1000',
        ]);

        $appointment->update($validated);

        return back()->with('success', 'Rendez-vous mis à jour.');
    }

    public function cancel(Request $request, Appointment $appointment): RedirectResponse
    {
        $appointment->cancel($request->input('reason', 'Annulé par le personnel'));
        return back()->with('success', 'Rendez-vous annulé.');
    }

    public function confirm(Appointment $appointment): RedirectResponse
    {
        $appointment->confirm();
        return back()->with('success', 'Rendez-vous confirmé.');
    }

    public function arrive(Appointment $appointment): RedirectResponse
    {
        $appointment->markAsArrived();
        return back()->with('success', 'Arrivée du patient enregistrée.');
    }
}
