@extends('layouts.app')
@section('title', 'Commandes')
@section('page-title', 'Bons de commande')

@section('content')
<div class="space-y-4">
    <div class="flex justify-between items-center">
        <p class="text-sm text-gray-500">{{ $orders->total() }} commande(s)</p>
        <a href="{{ route('purchase-orders.create') }}"
           class="flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 rounded-xl text-sm text-white">
            <i class="fa-solid fa-plus"></i> Nouvelle commande
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">N° commande</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Fournisseur</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Pharmacien</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Date</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Statut</th>
                    <th class="text-right px-6 py-4 font-semibold text-gray-600">Total</th>
                    <th class="text-right px-6 py-4 font-semibold text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($orders as $o)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 font-mono text-xs text-gray-500">{{ $o->order_number }}</td>
                    <td class="px-6 py-4 font-medium text-gray-800">{{ $o->supplier_name }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ $o->pharmacist?->getFullName() ?? '-' }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ $o->ordered_at?->format('d/m/Y') ?? '-' }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 rounded-full text-xs font-medium
                            @if($o->status === 'draft') bg-gray-100 text-gray-600
                            @elseif($o->status === 'approved') bg-blue-100 text-blue-700
                            @elseif($o->status === 'received') bg-green-100 text-green-700
                            @else bg-red-100 text-red-700 @endif">
                            {{ $o->status }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right text-gray-700">{{ number_format($o->total_amount, 0, ',', ' ') }} XAF</td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('purchase-orders.show', $o) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                            <i class="fa-solid fa-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                        <i class="fa-solid fa-file-invoice text-3xl mb-3 block"></i>
                        Aucune commande.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($orders->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $orders->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
