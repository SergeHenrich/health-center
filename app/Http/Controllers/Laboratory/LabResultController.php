<?php

namespace App\Http\Controllers\Laboratory;

use App\Http\Controllers\Controller;
use App\Models\LabRequestItem;
use App\Models\LabResult;
use App\Services\LabService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LabResultController extends Controller
{
    public function __construct(private readonly LabService $labService) {}

    public function index(): View
    {
        $results = LabResult::with(['labRequestItem.labExam', 'labRequest.patient', 'technician'])
            ->latest('performed_at')
            ->paginate(20);

        return view('laboratory.results.index', compact('results'));
    }

    public function create(Request $request): View
    {
        $item = LabRequestItem::with(['labRequest.patient', 'labExam'])->findOrFail($request->item_id);
        return view('laboratory.results.create', compact('item'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'lab_request_item_id' => 'required|exists:lab_request_items,id',
            'result_value'        => 'required|string|max:500',
            'unit'                => 'nullable|string|max:50',
            'reference_range'     => 'nullable|string|max:100',
            'interpretation'      => 'required|in:normal,low,high,critical',
            'notes'               => 'nullable|string|max:1000',
        ], [
            'result_value.required'   => 'La valeur du résultat est obligatoire.',
            'interpretation.required' => 'L\'interprétation est obligatoire.',
        ]);

        $result = $this->labService->recordResult($validated);

        return redirect()
            ->route('lab-requests.show', $result->lab_request_id)
            ->with('success', 'Résultat enregistré avec succès.');
    }

    public function validate(LabResult $labResult): RedirectResponse
    {
        $this->labService->validateResult($labResult->id);

        return back()->with('success', 'Résultat validé.');
    }
}
