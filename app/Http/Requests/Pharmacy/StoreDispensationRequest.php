<?php

namespace App\Http\Requests\Pharmacy;

use Illuminate\Foundation\Http\FormRequest;

class StoreDispensationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('pharmacy.dispense');
    }

    public function rules(): array
    {
        return [
            'prescription_id' => 'required|integer|exists:prescriptions,id',
            'notes'           => 'nullable|string|max:1000',
            'items'           => 'nullable|array',
            'items.*.prescription_item_id' => 'required_with:items|integer|exists:prescription_items,id',
            'items.*.medicine_id' => 'required_with:items|integer|exists:medicines,id',
            'items.*.quantity' => 'required_with:items|integer|min:1',
            'items.*.picks'    => 'nullable|array',
            'items.*.picks.*.batch_id' => 'required_with:items.*.picks|integer|exists:stock_batches,id',
            'items.*.picks.*.quantity' => 'required_with:items.*.picks|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'prescription_id.required' => 'L\'ordonnance est obligatoire.',
            'prescription_id.exists'   => 'Cette ordonnance n\'existe pas.',
        ];
    }
}
