@extends('layouts.app')
@section('title', 'Facture ' . $invoice->invoice_number)
@section('page-title', 'Détail facture')

@section('content')
<div class="space-y-4">

    {{-- Actions --}}
    <div class="flex gap-2 justify-end print:hidden">
        @if(!$invoice->isPaid())
        <button onclick="document.getElementById('pay-modal').classList.remove('hidden')"
                class="flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 rounded-xl text-sm text-white">
            <i class="fa-solid fa-money-bill-wave"></i> Enregistrer un paiement
        </button>
        @endif
        <button onclick="window.print()"
                class="flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-xl text-sm text-gray-700">
            <i class="fa-solid fa-print"></i> Imprimer
        </button>
    </div>

    {{-- Invoice card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 max-w-3xl mx-auto" id="invoice-print">

        {{-- Header --}}
        <div class="flex items-start justify-between mb-8">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <i class="fa-solid fa-hospital-user text-blue-600 text-2xl"></i>
                    <span class="text-xl font-bold text-gray-800">HealthCenter</span>
                </div>
                <p class="text-xs text-gray-500">Centre de santé de référence</p>
                <p class="text-xs text-gray-500">Douala, Cameroun</p>
            </div>
            <div class="text-right">
                <div class="text-2xl font-bold text-gray-800 font-mono">{{ $invoice->invoice_number }}</div>
                <div class="text-sm text-gray-500 mt-1">Date : {{ $invoice->invoice_date->format('d/m/Y') }}</div>
                @if($invoice->due_date)
                <div class="text-sm text-gray-500">Échéance : {{ $invoice->due_date->format('d/m/Y') }}</div>
                @endif
                <span class="inline-block mt-2 px-3 py-1 rounded-full text-xs font-semibold
                    {{ $invoice->isPaid() ? 'bg-green-100 text-green-700' : ($invoice->isOverdue() ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                    {{ strtoupper($invoice->status) }}
                </span>
            </div>
        </div>

        {{-- Patient info --}}
        <div class="bg-gray-50 rounded-xl p-4 mb-8">
            <div class="text-xs text-gray-400 mb-1 uppercase tracking-wide">Facturé à</div>
            <div class="font-semibold text-gray-800">{{ $invoice->patient->getFullName() }}</div>
            <div class="text-sm text-gray-500">{{ $invoice->patient->phone ?? '' }}</div>
            <div class="text-xs text-gray-400 font-mono mt-1">{{ $invoice->patient->patient_code }}</div>
        </div>

        {{-- Items table --}}
        <table class="w-full text-sm mb-8">
            <thead>
                <tr class="border-b-2 border-gray-200">
                    <th class="text-left py-3 font-semibold text-gray-600">Description</th>
                    <th class="text-right py-3 font-semibold text-gray-600">Qté</th>
                    <th class="text-right py-3 font-semibold text-gray-600">Prix unit.</th>
                    <th class="text-right py-3 font-semibold text-gray-600">Remise</th>
                    <th class="text-right py-3 font-semibold text-gray-600">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($invoice->items as $item)
                <tr>
                    <td class="py-3 text-gray-800">
                        {{ $item->description }}
                        <span class="ml-2 text-xs text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded">
                            {{ $item->item_type }}
                        </span>
                    </td>
                    <td class="py-3 text-right text-gray-600">{{ $item->quantity }}</td>
                    <td class="py-3 text-right text-gray-600">{{ number_format($item->unit_price, 0, ',', ' ') }}</td>
                    <td class="py-3 text-right text-gray-600">
                        {{ $item->discount_percent > 0 ? $item->discount_percent . '%' : '—' }}
                    </td>
                    <td class="py-3 text-right font-medium text-gray-800">
                        {{ number_format($item->total_price, 0, ',', ' ') }} XAF
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Totals --}}
        <div class="flex justify-end">
            <div class="w-64 space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Sous-total</span>
                    <span class="text-gray-800">{{ number_format($invoice->subtotal, 0, ',', ' ') }} XAF</span>
                </div>
                @if($invoice->discount_amount > 0)
                <div class="flex justify-between text-green-600">
                    <span>Remise</span>
                    <span>- {{ number_format($invoice->discount_amount, 0, ',', ' ') }} XAF</span>
                </div>
                @endif
                @if($invoice->tax_amount > 0)
                <div class="flex justify-between">
                    <span class="text-gray-500">TVA</span>
                    <span class="text-gray-800">{{ number_format($invoice->tax_amount, 0, ',', ' ') }} XAF</span>
                </div>
                @endif
                <div class="flex justify-between border-t border-gray-200 pt-2 font-bold text-base">
                    <span class="text-gray-800">Total</span>
                    <span class="text-gray-900">{{ number_format($invoice->total_amount, 0, ',', ' ') }} XAF</span>
                </div>
                @if($invoice->amount_paid > 0)
                <div class="flex justify-between text-green-600">
                    <span>Payé</span>
                    <span>{{ number_format($invoice->amount_paid, 0, ',', ' ') }} XAF</span>
                </div>
                <div class="flex justify-between border-t border-gray-200 pt-2 font-bold text-red-600">
                    <span>Reste à payer</span>
                    <span>{{ number_format($invoice->getBalanceDue(), 0, ',', ' ') }} XAF</span>
                </div>
                @endif
            </div>
        </div>

        {{-- Payments history --}}
        @if($invoice->payments->isNotEmpty())
        <div class="mt-8 border-t border-gray-100 pt-6">
            <h3 class="font-semibold text-gray-700 mb-3">Historique des paiements</h3>
            <table class="w-full text-xs">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left px-3 py-2">N° paiement</th>
                        <th class="text-left px-3 py-2">Date</th>
                        <th class="text-left px-3 py-2">Mode</th>
                        <th class="text-right px-3 py-2">Montant</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($invoice->payments as $pay)
                    <tr>
                        <td class="px-3 py-2 font-mono text-gray-500">{{ $pay->payment_number }}</td>
                        <td class="px-3 py-2 text-gray-600">{{ $pay->paid_at->format('d/m/Y H:i') }}</td>
                        <td class="px-3 py-2 capitalize text-gray-600">{{ str_replace('_', ' ', $pay->method) }}</td>
                        <td class="px-3 py-2 text-right font-semibold text-gray-800">
                            {{ number_format($pay->amount, 0, ',', ' ') }} XAF
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- Footer --}}
        <div class="mt-10 pt-6 border-t border-gray-100 text-center text-xs text-gray-400">
            Merci pour votre confiance. Ce document est généré automatiquement par HealthCenter.
        </div>

    </div>

</div>

{{-- Payment Modal --}}
@if(!$invoice->isPaid())
<div id="pay-modal" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Enregistrer un paiement</h3>
        <form method="POST" action="{{ route('payments.store') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Montant (XAF)</label>
                <input type="number" name="amount" value="{{ $invoice->getBalanceDue() }}"
                       min="1" required
                       class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Mode de paiement</label>
                <select name="method" required class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="cash">Espèces</option>
                    <option value="mobile_money">Mobile Money</option>
                    <option value="bank_transfer">Virement bancaire</option>
                    <option value="card">Carte bancaire</option>
                    <option value="insurance">Assurance</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Code de référence (optionnel)</label>
                <input type="text" name="reference_code"
                       class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button"
                        onclick="document.getElementById('pay-modal').classList.add('hidden')"
                        class="flex-1 px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-xl text-sm text-gray-700">
                    Annuler
                </button>
                <button type="submit"
                        class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 rounded-xl text-sm text-white font-medium">
                    Confirmer
                </button>
            </div>
        </form>
    </div>
</div>
@endif

@push('scripts')
<style>
@media print {
    body * { visibility: hidden; }
    #invoice-print, #invoice-print * { visibility: visible; }
    #invoice-print { position: absolute; left: 0; top: 0; width: 100%; }
}
</style>
@endpush
@endsection
