<?php

namespace App\Http\Controllers\Medical;

use App\Http\Controllers\Controller;
use App\Models\VitalSign;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VitalSignController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'medical_record_id'          => 'required|exists:medical_records,id',
            'consultation_id'            => 'nullable|exists:consultations,id',
            'temperature_c'              => 'nullable|numeric|between:30,45',
            'blood_pressure_systolic'    => 'nullable|integer|between:50,250',
            'blood_pressure_diastolic'   => 'nullable|integer|between:30,150',
            'heart_rate'                 => 'nullable|integer|between:30,250',
            'respiratory_rate'           => 'nullable|integer|between:5,60',
            'oxygen_saturation'          => 'nullable|numeric|between:50,100',
            'weight_kg'                  => 'nullable|numeric|between:1,300',
            'height_cm'                  => 'nullable|numeric|between:30,250',
            'notes'                      => 'nullable|string|max:500',
        ]);

        VitalSign::create([
            ...$validated,
            'nurse_id'    => auth()->id(),
            'recorded_at' => now(),
        ]);

        return back()->with('success', 'Constantes enregistrées.');
    }

    public function update(Request $request, VitalSign $vitalSign): RedirectResponse
    {
        $validated = $request->validate([
            'temperature_c'            => 'nullable|numeric|between:30,45',
            'blood_pressure_systolic'  => 'nullable|integer|between:50,250',
            'blood_pressure_diastolic' => 'nullable|integer|between:30,150',
            'heart_rate'               => 'nullable|integer|between:30,250',
            'oxygen_saturation'        => 'nullable|numeric|between:50,100',
            'notes'                    => 'nullable|string|max:500',
        ]);

        $vitalSign->update($validated);

        return back()->with('success', 'Constantes mises à jour.');
    }
}
