@extends('layouts.app')
@section('title', 'Commande')
@section('page-title', 'Bon de commande : ' . $purchaseOrder->order_number)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    {{-- Info --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 text-sm">
            <div>
                <span class="text-gray-500">Fournisseur :</span>
                <span class="font-medium text-gray-800 ml-2">{{ $purchaseOrder->supplier_name }}</span>
            </div>
            <div>
                <span class="text-gray-500">Contact :</span>
                <span class="font-medium text-gray-800 ml-2">{{ $purchaseOrder->supplier_contact ?? '-' }}</span>
            </div>
            <div>
                <span class="text-gray-500">Pharmacien :</span>
                <span class="font-medium text-gray-800 ml-2">{{ $purchaseOrder->pharmacist?->getFullName() ?? '-' }}</span>
            </div>
            <div>
                <span class="text-gray-500">Approuvé par :</span>
                <span class="font-medium text-gray-800 ml-2">{{ $purchaseOrder->approvedBy?->getFullName() ?? '-' }}</span>
            </div>
            <div>
                <span class="text-gray-500">Date commande :</span>
                <span class="font-medium text-gray-800 ml-2">{{ $purchaseOrder->ordered_at?->format('d/m/Y') ?? '-' }}</span>
            </div>
            <div>
                <span class="text-gray-500">Livraison prévue :</span>
                <span class="font-medium text-gray-800 ml-2">{{ $purchaseOrder->expected_delivery ? $purchaseOrder->expected_delivery->format('d/m/Y') : '-' }}</span>
            </div>
            <div>
                <span class="text-gray-500">Statut :</span>
                <span class="ml-2 px-2 py-0.5 rounded-full text-xs font-medium
                    @if($purchaseOrder->status === 'draft') bg-gray-100 text-gray-600
                    @elseif($purchaseOrder->status === 'approved') bg-blue-100 text-blue-700
                    @elseif($purchaseOrder->status === 'received') bg-green-100 text-green-700 @endif">
                    {{ $purchaseOrder->status }}
                </span>
            </div>
            @if($purchaseOrder->notes)
            <div class="col-span-2">
                <span class="text-gray-500">Notes :</span>
                <p class="text-gray-700 mt-1">{{ $purchaseOrder->notes }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Items --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-x-auto">
        <div class="px-6 py-4 border-b border-gray-100 font-semibold text-gray-700">Articles commandés</div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-6 py-3 font-semibold text-gray-600">Médicament</th>
                    <th class="text-right px-6 py-3 font-semibold text-gray-600">Qté</th>
                    <th class="text-right px-6 py-3 font-semibold text-gray-600">Prix unitaire</th>
                    <th class="text-right px-6 py-3 font-semibold text-gray-600">Total</th>
                    <th class="text-right px-6 py-3 font-semibold text-gray-600">Reçu</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($purchaseOrder->items as $item)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-3 font-medium text-gray-800">{{ $item->medicine?->name ?? '-' }}</td>
                    <td class="px-6 py-3 text-right text-gray-700">{{ $item->quantity_ordered }}</td>
                    <td class="px-6 py-3 text-right text-gray-700">{{ number_format($item->unit_cost, 0, ',', ' ') }} XAF</td>
                    <td class="px-6 py-3 text-right text-gray-800">{{ number_format($item->quantity_ordered * $item->unit_cost, 0, ',', ' ') }} XAF</td>
                    <td class="px-6 py-3 text-right @if($item->quantity_received > 0) text-green-600 @else text-gray-400 @endif">
                        {{ $item->quantity_received }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-400">Aucun article.</td>
                </tr>
                @endforelse
            </tbody>
            <tfoot class="bg-gray-50 border-t border-gray-100">
                <tr>
                    <td colspan="3" class="px-6 py-3 text-right font-semibold text-gray-700">Total</td>
                    <td class="px-6 py-3 text-right font-bold text-gray-800">{{ number_format($purchaseOrder->total_amount, 0, ',', ' ') }} XAF</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- Actions --}}
    <div class="flex gap-3">
        @if($purchaseOrder->status === 'draft')
        <form method="POST" action="{{ route('purchase-orders.approve', $purchaseOrder) }}">
            @csrf
            <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium"
                    onclick="return confirm('Approuver cette commande ?')">
                <i class="fa-solid fa-check mr-1"></i> Approuver
            </button>
        </form>
        @endif

        @if(in_array($purchaseOrder->status, ['approved', 'draft']))
        <form method="POST" action="{{ route('purchase-orders.receive', $purchaseOrder) }}">
            @csrf
            <button type="submit" class="px-6 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-xl text-sm font-medium"
                    onclick="return confirm('Enregistrer la réception de cette commande ?')">
                <i class="fa-solid fa-warehouse mr-1"></i> Réceptionner
            </button>
        </form>
        @endif

        <a href="{{ route('purchase-orders.index') }}" class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-sm">
            <i class="fa-solid fa-arrow-left mr-1"></i> Retour
        </a>
    </div>

</div>
@endsection
