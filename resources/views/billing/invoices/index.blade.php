@extends('layouts.app')
@section('title', 'Factures')
@section('page-title', 'Gestion des factures')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm text-gray-500">{{ $invoices->total() }} facture(s) au total</p>
        </div>
        <a href="{{ route('invoices.create') }}"
           class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded-xl text-sm text-white">
            <i class="fa-solid fa-plus"></i> Nouvelle facture
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-6 py-3 font-semibold text-gray-600">N° Facture</th>
                    <th class="text-left px-6 py-3 font-semibold text-gray-600">Patient</th>
                    <th class="text-left px-6 py-3 font-semibold text-gray-600">Date</th>
                    <th class="text-right px-6 py-3 font-semibold text-gray-600">Total</th>
                    <th class="text-right px-6 py-3 font-semibold text-gray-600">Payé</th>
                    <th class="text-center px-6 py-3 font-semibold text-gray-600">Statut</th>
                    <th class="text-center px-6 py-3 font-semibold text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($invoices as $invoice)
                <tr class="hover:bg-gray-50/50">
                    <td class="px-6 py-4 font-mono text-gray-800">{{ $invoice->invoice_number }}</td>
                    <td class="px-6 py-4 text-gray-700">{{ $invoice->patient->getFullName() }}</td>
                    <td class="px-6 py-4 text-gray-500">{{ $invoice->invoice_date->format('d/m/Y') }}</td>
                    <td class="px-6 py-4 text-right font-medium text-gray-800">{{ number_format($invoice->total_amount, 0, ',', ' ') }} XAF</td>
                    <td class="px-6 py-4 text-right text-gray-600">{{ number_format($invoice->amount_paid, 0, ',', ' ') }} XAF</td>
                    <td class="px-6 py-4 text-center">
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold
                            {{ $invoice->isPaid() ? 'bg-green-100 text-green-700' : ($invoice->isOverdue() ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                            {{ ucfirst($invoice->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <a href="{{ route('invoices.show', $invoice) }}" class="text-blue-600 hover:text-blue-800">
                            <i class="fa-solid fa-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                        <i class="fa-solid fa-file-invoice text-3xl mb-3 block"></i>
                        Aucune facture enregistrée.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $invoices->links() }}
</div>
@endsection
