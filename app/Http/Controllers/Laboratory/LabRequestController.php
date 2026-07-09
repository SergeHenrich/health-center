<?php

namespace App\Http\Controllers\Laboratory;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Models\LabExam;
use App\Models\LabRequest;
use App\Services\LabService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LabRequestController extends Controller
{
    public function __construct(private readonly LabService $labService) {}

    public function index(): View
    {
        $requests = LabRequest::with(['patient', 'doctor', 'items.labExam'])
            ->when(
                auth()->user()->hasRole('general_practitioner') || auth()->user()->hasRole('specialist'),
                fn($q) => $q->where('doctor_id', auth()->id())
            )
            ->latest('requested_at')
            ->paginate(20);

        return view('laboratory.requests.index', compact('requests'));
    }

    public function create(Request $request): View
    {
        $consultation = Consultation::with('medicalRecord.patient')->findOrFail($request->consultation_id);
        $exams        = LabExam::active()->orderBy('category')->orderBy('name')->get();

        return view('laboratory.requests.create', compact('consultation', 'exams'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'consultation_id' => 'required|exists:consultations,id',
            'patient_id'      => 'required|exists:patients,id',
            'doctor_id'       => 'required|exists:users,id',
            'urgency'         => 'required|in:normal,urgent,critical',
            'clinical_info'   => 'nullable|string|max:1000',
            'exam_ids'        => 'required|array|min:1',
            'exam_ids.*'      => 'exists:lab_exams,id',
        ], [
            'exam_ids.required' => 'Sélectionnez au moins un examen.',
        ]);

        $labRequest = $this->labService->submitRequest($validated);

        return redirect()
            ->route('consultations.show', $validated['consultation_id'])
            ->with('success', "Demande d'analyse {$labRequest->request_number} créée.");
    }

    public function show(LabRequest $labRequest): View
    {
        $labRequest->load(['patient', 'doctor', 'items.labExam', 'items.result.technician']);
        return view('laboratory.requests.show', compact('labRequest'));
    }
}
