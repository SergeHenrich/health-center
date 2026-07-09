<?php

namespace App\Http\Requests\Medical;

use Illuminate\Foundation\Http\FormRequest;

class StoreConsultationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['general_practitioner', 'specialist', 'administrator']);
    }

    public function rules(): array
    {
        return [
            'patient_id'            => 'required|integer|exists:patients,id',
            'doctor_id'             => 'required|integer|exists:users,id',
            'appointment_id'        => 'nullable|integer|exists:appointments,id',
            'consultation_date'     => 'nullable|date',
            'chief_complaint'       => 'required|string|min:5|max:1000',
            'history_of_illness'    => 'nullable|string|max:5000',
            'physical_examination'  => 'nullable|string|max:5000',
            'clinical_notes'        => 'nullable|string|max:5000',
            'follow_up_date'        => 'nullable|date|after:today',
            'referred_to'           => 'nullable|integer|exists:users,id',
            'referral_reason'       => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'patient_id.required'      => 'Le patient est obligatoire.',
            'patient_id.exists'        => 'Ce patient n\'existe pas.',
            'chief_complaint.required' => 'Le motif de consultation est obligatoire.',
            'chief_complaint.min'      => 'Le motif doit contenir au moins 5 caractères.',
            'follow_up_date.after'     => 'La date de suivi doit être dans le futur.',
        ];
    }
}
