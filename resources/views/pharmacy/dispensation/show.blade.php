@extends('layouts.app')
@section('title', 'Dispensation')
@section('page-title', 'Détails de la dispensation')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    {{-- Info --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 text-sm">
            <div>
                <span class="text-gray-500">Ordonnance :</span>
                <span class="font-mono font-medium text-gray-800 ml-2">{{ $dispensation->prescription?->prescription_number ?? '-' }}</span>
            </div>
            <div>
                <span class="text-gray-500">Patient :</span>
                <span class="font-medium text-gray-800 ml-2">{{ $dispensation->prescription?->patient?->getFullName() ?? '-' }}</span>
            </div>
            <div>
                <span class="text-gray-500">Médecin :</span>
                <span class="font-medium text-gray-800 ml-2">{{ $dispensation->prescription?->doctor?->getFullName() ?? '-' }}</span>
            </div>
            <div>
                <span class="text-gray-500">Pharmacien :</span>
                <span class="font-medium text-gray-800 ml-2">{{ $dispensation->pharmacist?->getFullName() ?? '-' }}</span>
            </div>
            <div>
                <span class="text-gray-500">Date :</span>
                <span class="font-medium text-gray-800 ml-2">{{ $dispensation->dispensed_at?->format('d/m/Y H:i') ?? '-' }}</span>
            </div>
            <div>
                <span class="text-gray-500">Statut ordonnance :</span>
                <span class="ml-2 px-2 py-0.5 rounded-full text-xs font-medium
                    @if($dispensation->prescription?->status === 'dispensed') bg-green-100 text-green-700
                    @elseif($dispensation->prescription?->status === 'partially_dispensed') bg-amber-100 text-amber-700
                    @else bg-gray-100 text-gray-600 @endif">
                    {{ $dispensation->prescription?->status ?? '-' }}
                </span>
            </div>
        </div>
    </div>

    {{-- Items --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-x-auto">
        <div class="px-6 py-4 border-b border-gray-100 font-semibold text-gray-700">Articles dispensés</div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-6 py-3 font-semibold text-gray-600">Médicament</th>
                    <th class="text-right px-6 py-3 font-semibold text-gray-600">Quantité</th>
                    <th class="text-right px-6 py-3 font-semibold text-gray-600">Prix unitaire</th>
                    <th class="text-right px-6 py-3 font-semibold text-gray-600">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($dispensation->items as $item)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-3 font-medium text-gray-800">{{ $item->medicine?->name ?? '-' }}</td>
                    <td class="px-6 py-3 text-right text-gray-700">{{ $item->quantity_dispensed }}</td>
                    <td class="px-6 py-3 text-right text-gray-700">{{ number_format($item->unit_price, 0, ',', ' ') }} XAF</td>
                    <td class="px-6 py-3 text-right font-medium text-gray-800">{{ number_format($item->quantity_dispensed * $item->unit_price, 0, ',', ' ') }} XAF</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-8 text-center text-gray-400">Aucun article.</td>
                </tr>
                @endforelse
            </tbody>
            <tfoot class="bg-gray-50 border-t border-gray-100">
                <tr>
                    <td colspan="3" class="px-6 py-3 text-right font-semibold text-gray-700">Total</td>
                    <td class="px-6 py-3 text-right font-bold text-gray-800">
                        {{ number_format($dispensation->items->sum(fn($i) => $i->quantity_dispensed * $i->unit_price), 0, ',', ' ') }} XAF
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- Invoice --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="font-semibold text-gray-700">Facture</h3>
                @if($dispensation->relationLoaded('invoice') && $dispensation->invoice)
                    <p class="text-sm text-gray-500 mt-1">
                        Facture <span class="font-mono font-medium">{{ $dispensation->invoice->invoice_number }}</span>
                        <span class="ml-2 px-2 py-0.5 rounded-full text-xs font-medium
                            @if($dispensation->invoice->isPaid()) bg-green-100 text-green-700
                            @elseif($dispensation->invoice->isOverdue()) bg-red-100 text-red-700
                            @else bg-yellow-100 text-yellow-700 @endif">
                            {{ strtoupper($dispensation->invoice->status) }}
                        </span>
                    </p>
                @else
                    <p class="text-sm text-gray-400 mt-1">Aucune facture associée.</p>
                @endif
            </div>
            <div class="flex gap-2">
                @if($dispensation->relationLoaded('invoice') && $dispensation->invoice)
                    <a href="{{ route('invoices.show', $dispensation->invoice) }}"
                       class="px-4 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-xl text-sm">
                        <i class="fa-solid fa-file-invoice mr-1"></i> Voir la facture
                    </a>
                    @if(!$dispensation->invoice->isPaid())
                        <button onclick="document.getElementById('pay-modal').classList.remove('hidden')"
                                class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-xl text-sm">
                            <i class="fa-solid fa-money-bill-wave mr-1"></i> Payer
                        </button>
                    @endif
                @endif
            </div>
        </div>
    </div>

    <div class="flex gap-3">
        <a href="{{ route('dispensations.index') }}" class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-sm">
            <i class="fa-solid fa-arrow-left mr-1"></i> Retour
        </a>
    </div>
</div>

{{-- Payment Modal --}}
@if($dispensation->relationLoaded('invoice') && $dispensation->invoice && !$dispensation->invoice->isPaid())
<div id="pay-modal" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Paiement de la facture</h3>
        <form method="POST" action="{{ route('payments.store') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="invoice_id" value="{{ $dispensation->invoice->id }}">
            <div class="flex justify-between text-sm border-b border-gray-100 pb-2">
                <span class="text-gray-500">Total facture</span>
                <span class="font-bold text-gray-800">{{ number_format($dispensation->invoice->total_amount, 0, ',', ' ') }} XAF</span>
            </div>
            @if($dispensation->invoice->amount_paid > 0)
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Déjà payé</span>
                <span class="text-green-600">{{ number_format($dispensation->invoice->amount_paid, 0, ',', ' ') }} XAF</span>
            </div>
            @endif
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Montant (XAF)</label>
                <input type="number" name="amount" value="{{ $dispensation->invoice->getBalanceDue() }}"
                       min="0.01" step="0.01" required
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
                <label class="block text-sm font-medium text-gray-700 mb-1">Référence (optionnel)</label>
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
                    Confirmer le paiement
                </button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
