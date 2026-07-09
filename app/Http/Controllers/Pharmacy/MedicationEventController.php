<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\MedicationEvent;
use App\Services\Pharmacy\MedicationEventService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MedicationEventController extends Controller
{
    public function __construct(private readonly MedicationEventService $eventService) {}

    public function index(Request $request): View
    {
        $events = $this->eventService->getEvents($request->only(['type', 'severity', 'status', 'q']));
        return view('pharmacy.events.index', compact('events'));
    }

    public function create(): View
    {
        return view('pharmacy.events.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:medication_error,adverse_drug_reaction,near_miss,quality_incident',
            'severity' => 'required|in:low,medium,high,critical',
            'medicine_id' => 'nullable|exists:medicines,id',
            'patient_id' => 'nullable|exists:patients,id',
            'prescription_id' => 'nullable|exists:prescriptions,id',
            'description' => 'required|string|max:2000',
            'cause' => 'nullable|string|max:2000',
            'action_taken' => 'nullable|string|max:2000',
            'occurred_at' => 'nullable|date',
            'notes' => 'nullable|string|max:2000',
        ]);

        $this->eventService->reportEvent($validated);

        return redirect()->route('medication-events.index')
            ->with('success', 'Événement signalé avec succès.');
    }

    public function show(MedicationEvent $medicationEvent): View
    {
        $medicationEvent->load(['medicine', 'patient', 'prescription', 'dispensation', 'reporter', 'assignee']);
        return view('pharmacy.events.show', compact('medicationEvent'));
    }

    public function resolve(Request $request, MedicationEvent $medicationEvent): RedirectResponse
    {
        $validated = $request->validate([
            'action_taken' => 'required|string|max:2000',
            'corrective_actions' => 'nullable|string|max:2000',
        ]);

        $this->eventService->resolveEvent($medicationEvent, $validated['action_taken'], $validated['corrective_actions'] ?? null);

        return redirect()->route('medication-events.show', $medicationEvent)
            ->with('success', 'Événement résolu.');
    }

    public function assign(Request $request, MedicationEvent $medicationEvent): RedirectResponse
    {
        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $this->eventService->assignEvent($medicationEvent, $validated['assigned_to']);

        return redirect()->route('medication-events.show', $medicationEvent)
            ->with('success', 'Événement assigné.');
    }
}
