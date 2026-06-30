<?php

namespace App\Http\Requests\Billing;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('billing.payments.create');
    }

    public function rules(): array
    {
        return [
            'invoice_id'     => 'required|integer|exists:invoices,id',
            'amount'         => 'required|numeric|min:0.01',
            'method'         => 'required|in:cash,mobile_money,bank_transfer,card,insurance,other',
            'reference_code' => 'nullable|string|max:100',
            'notes'          => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'invoice_id.required' => 'La facture est obligatoire.',
            'amount.required'     => 'Le montant est obligatoire.',
            'amount.min'          => 'Le montant doit être supérieur à 0.',
            'method.required'     => 'Le mode de paiement est obligatoire.',
            'method.in'           => 'Mode de paiement invalide.',
        ];
    }
}
