<?php

namespace App\Http\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;

class StorePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('patients.create');
    }

    public function rules(): array
    {
        return [
            'first_name'               => 'required|string|max:100',
            'last_name'                => 'required|string|max:100',
            'date_of_birth'            => 'required|date|before:today',
            'gender'                   => 'required|in:male,female,other',
            'blood_type'               => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'address'                  => 'nullable|string|max:500',
            'city'                     => 'nullable|string|max:100',
            'phone'                    => 'nullable|string|max:20',
            'email'                    => 'nullable|email|max:191',
            'emergency_contact_name'   => 'nullable|string|max:100',
            'emergency_contact_phone'  => 'nullable|string|max:20',
            'insurance_number'         => 'nullable|string|max:100',
            'insurance_provider'       => 'nullable|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required'    => 'Le prénom est obligatoire.',
            'last_name.required'     => 'Le nom est obligatoire.',
            'date_of_birth.required' => 'La date de naissance est obligatoire.',
            'date_of_birth.before'   => 'La date de naissance doit être dans le passé.',
            'gender.required'        => 'Le sexe est obligatoire.',
            'gender.in'              => 'Le sexe doit être : masculin, féminin ou autre.',
        ];
    }
}
