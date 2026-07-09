<?php

namespace App\Http\Controllers\Medical;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Models\Medicine;
use App\Models\Prescription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PrescriptionController extends Controller
{
    public function create(Consultation $consultation): View
    {
        $medicines = Medicine::active()->orderBy('name')->get(['id', 'name', 'generic_name', 'form', 'strength', 'unit_price']);
        return view('prescriptions.create', compact('consultation', 'medicines'));
    }

    public function store(Request $request, Consultation $consultation): RedirectResponse
    {
        $validated = $request->validate([
            'valid_until'        => 'nullable|date|after:today',
            'notes'              => 'nullable|string|max:1000',
            'items'              => 'required|array|min:1',
            'items.*.medicine_id'  => 'required|exists:medicines,id',
            'items.*.dosage'       => 'required|string|max:100',
            'items.*.frequency'    => 'required|string|max:100',
            'items.*.duration'     => 'nullable|string|max:100',
            'items.*.quantity_prescribed' => 'required|integer|min:1',
            'items.*.route'        => 'required|in:oral,injectable,topical,inhaled,other',
            'items.*.instructions' => 'nullable|string|max:500',
        ], [
            'items.required'     => 'Ajoutez au moins un médicament.',
            'items.*.medicine_id.required' => 'Sélectionnez un médicament.',
            'items.*.dosage.required'      => 'Le dosage est obligatoire.',
            'items.*.frequency.required'   => 'La fréquence est obligatoire.',
            'items.*.quantity_prescribed.required' => 'La quantité est obligatoire.',
        ]);

        DB::transaction(function () use ($validated, $consultation) {
            $year = now()->year;
            $base = "RX-{$year}-";
            $last = Prescription::where('prescription_number', 'like', "{$base}%")
                ->orderByDesc('prescription_number')->value('prescription_number');
            $next = $last ? ((int) substr($last, -5)) + 1 : 1;
            $number = $base . str_pad($next, 5, '0', STR_PAD_LEFT);

            $prescription = Prescription::create([
                'consultation_id'       => $consultation->id,
                'doctor_id'             => auth()->id(),
                'patient_id'            => $consultation->medicalRecord->patient_id,
                'prescription_number'   => $number,
                'issued_at'             => now(),
                'valid_until'           => $validated['valid_until'] ?? null,
                'notes'                 => $validated['notes'] ?? null,
                'status'                => 'pending',
            ]);

            foreach ($validated['items'] as $item) {
                $medicine = Medicine::find($item['medicine_id']);
                $prescription->items()->create([
                    ...$item,
                    'medicine_name'     => $medicine->name,
                    'quantity_dispensed'=> 0,
                ]);
            }
        });

        return redirect()
            ->route('consultations.show', $consultation)
            ->with('success', 'Ordonnance créée avec succès.');
    }

    public function show(Prescription $prescription): View
    {
        $prescription->load(['doctor', 'patient', 'items.medicine', 'dispensations.items']);
        return view('prescriptions.show', compact('prescription'));
    }
}
