<?php

namespace App\Http\Controllers\Medical;

use App\Http\Controllers\Controller;
use App\Http\Requests\Medical\StoreConsultationRequest;
use App\Models\Consultation;
use App\Models\Patient;
use App\Services\ConsultationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ConsultationController extends Controller
{
    public function __construct(private readonly ConsultationService $consultationService) {}

    public function index(): View
    {
        $consultations = Consultation::with(['medicalRecord.patient', 'doctor'])
            ->byDoctor(auth()->id())
            ->latest('consultation_date')
            ->paginate(20);

        return view('consultations.index', compact('consultations'));
    }

    public function create(int $patientId): View
    {
        $patient = Patient::with('medicalRecord')->findOrFail($patientId);

        return view('consultations.create', compact('patient'));
    }

    public function store(StoreConsultationRequest $request): RedirectResponse
    {
        $consultation = $this->consultationService->open($request->validated());

        return redirect()
            ->route('consultations.show', $consultation)
            ->with('success', 'Consultation ouverte.');
    }

    public function show(Consultation $consultation): View
    {
        $consultation->load([
            'medicalRecord.patient',
            'doctor',
            'diagnoses',
            'prescriptions.items.medicine',
            'labRequests.items.labExam',
            'labRequests.results',
            'vitalSign',
        ]);

        return view('consultations.show', compact('consultation'));
    }

    public function update(StoreConsultationRequest $request, Consultation $consultation): RedirectResponse
    {
        $this->consultationService->updateNotes($consultation->id, $request->validated());

        return back()->with('success', 'Consultation mise à jour.');
    }

    public function close(Consultation $consultation): RedirectResponse
    {
        $this->consultationService->close($consultation->id);

        return redirect()
            ->route('patients.show', $consultation->medicalRecord->patient_id)
            ->with('success', 'Consultation clôturée.');
    }
}
