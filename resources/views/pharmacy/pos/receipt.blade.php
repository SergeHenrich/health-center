@extends('layouts.app')
@section('title', 'Reçu #' . $invoice->invoice_number)
@section('page-title', 'Reçu de vente')

@section('content')
<div class="space-y-4">

    <div class="flex gap-2 justify-end print:hidden">
        <a href="{{ route('pharmacy.pos.index') }}"
           class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded-xl text-sm text-white">
            <i class="fa-solid fa-cash-register"></i> Nouvelle vente
        </a>
        <button onclick="window.print()"
                class="flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-xl text-sm text-gray-700">
            <i class="fa-solid fa-print"></i> Imprimer
        </button>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 max-w-3xl mx-auto" id="receipt-print">
        {{-- Header --}}
        <div class="text-center mb-8">
            <div class="flex items-center justify-center gap-2 mb-1">
                <i class="fa-solid fa-hospital-user text-blue-600 text-xl"></i>
                <span class="text-lg font-bold text-gray-800">HealthCenter</span>
            </div>
            <p class="text-xs text-gray-500">Pharmacie - Centre de santé de référence</p>
            <p class="text-xs text-gray-500">Douala, Cameroun</p>
            <div class="mt-3 text-sm font-mono font-bold text-gray-800">{{ $invoice->invoice_number }}</div>
            <div class="text-xs text-gray-500">{{ $invoice->invoice_date->format('d/m/Y H:i') }}</div>
        </div>

        {{-- Patient --}}
        <div class="bg-gray-50 rounded-xl p-3 mb-6 flex items-center justify-between">
            <div>
                <div class="text-xs text-gray-400 uppercase tracking-wide">Patient</div>
                <div class="font-semibold text-gray-800">{{ $invoice->patient->getFullName() }}</div>
            </div>
            <div class="text-xs text-gray-400 font-mono">{{ $invoice->patient->patient_code }}</div>
        </div>

        {{-- Items --}}
        <table class="w-full text-sm mb-6">
            <thead>
                <tr class="border-b-2 border-gray-200">
                    <th class="text-left py-2 text-xs text-gray-500 uppercase">Produit</th>
                    <th class="text-center py-2 text-xs text-gray-500 uppercase">Qté</th>
                    <th class="text-right py-2 text-xs text-gray-500 uppercase">Prix unit.</th>
                    <th class="text-right py-2 text-xs text-gray-500 uppercase">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($invoice->items as $item)
                <tr>
                    <td class="py-2 text-gray-800 text-sm">{{ $item->description }}</td>
                    <td class="py-2 text-center text-gray-600">{{ $item->quantity }}</td>
                    <td class="py-2 text-right text-gray-600">{{ number_format($item->unit_price, 0, ',', ' ') }}</td>
                    <td class="py-2 text-right font-medium text-gray-800">
                        {{ number_format($item->total_price, 0, ',', ' ') }} XAF
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Totals --}}
        <div class="flex justify-end border-t border-gray-200 pt-4">
            <div class="w-56 space-y-1">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Sous-total</span>
                    <span class="text-gray-800">{{ number_format($invoice->subtotal, 0, ',', ' ') }} XAF</span>
                </div>
                @if($invoice->tax_amount > 0)
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">TVA</span>
                    <span class="text-gray-800">{{ number_format($invoice->tax_amount, 0, ',', ' ') }} XAF</span>
                </div>
                @endif
                <div class="flex justify-between border-t border-gray-200 pt-1 font-bold text-base">
                    <span class="text-gray-800">Total</span>
                    <span class="text-gray-900">{{ number_format($invoice->total_amount, 0, ',', ' ') }} XAF</span>
                </div>
                <div class="flex justify-between text-green-600 text-sm">
                    <span>Payé</span>
                    <span>{{ number_format($invoice->amount_paid, 0, ',', ' ') }} XAF</span>
                </div>
                @if($invoice->payments->isNotEmpty())
                <div class="text-xs text-gray-400 pt-1">
                    Paiement : {{ str_replace('_', ' ', $invoice->payments->first()->method) }}
                    @if($invoice->payments->first()->reference_code)
                    · Ref: {{ $invoice->payments->first()->reference_code }}
                    @endif
                </div>
                @endif
            </div>
        </div>

        {{-- Footer --}}
        <div class="mt-10 pt-6 border-t border-gray-100 text-center text-xs text-gray-400">
            <p>Vente effectuée par {{ $invoice->createdBy?->name ?? 'N/A' }}</p>
            <p class="mt-1">Merci pour votre confiance.</p>
        </div>
    </div>

</div>

@push('scripts')
<style>
@media print {
    body * { visibility: hidden; }
    #receipt-print, #receipt-print * { visibility: visible; }
    #receipt-print { position: absolute; left: 0; top: 0; width: 100%; }
}
</style>
@endpush
@endsection
