@extends('layouts.app')
@section('title', 'Paiements')
@section('page-title', 'Gestion des paiements')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm text-gray-500">{{ $payments->total() }} paiement(s) au total</p>
            <p class="text-sm font-semibold text-green-600">Total du jour : {{ number_format($todayTotal, 0, ',', ' ') }} XAF</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-6 py-3 font-semibold text-gray-600">N° Paiement</th>
                    <th class="text-left px-6 py-3 font-semibold text-gray-600">Facture</th>
                    <th class="text-left px-6 py-3 font-semibold text-gray-600">Date</th>
                    <th class="text-left px-6 py-3 font-semibold text-gray-600">Mode</th>
                    <th class="text-right px-6 py-3 font-semibold text-gray-600">Montant</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($payments as $payment)
                <tr class="hover:bg-gray-50/50">
                    <td class="px-6 py-4 font-mono text-gray-800">{{ $payment->payment_number }}</td>
                    <td class="px-6 py-4 text-gray-700">{{ $payment->invoice->invoice_number }}</td>
                    <td class="px-6 py-4 text-gray-500">{{ $payment->paid_at->format('d/m/Y H:i') }}</td>
                    <td class="px-6 py-4 text-gray-600 capitalize">{{ str_replace('_', ' ', $payment->method) }}</td>
                    <td class="px-6 py-4 text-right font-semibold text-green-600">{{ number_format($payment->amount, 0, ',', ' ') }} XAF</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                        <i class="fa-solid fa-money-bill-wave text-3xl mb-3 block"></i>
                        Aucun paiement enregistré.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $payments->links() }}
</div>
@endsection
