@extends('layouts.app')
@section('title', 'Lot ' . ($stockBatch->lot_number ?? 'N/A'))
@section('page-title', 'Lot ' . ($stockBatch->lot_number ?? 'N/A'))

@section('content')
<div class="space-y-6">

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 text-sm">
            <div><span class="text-gray-500">Médicament</span><p class="mt-1 font-medium">{{ $stockBatch->medicine->name }}</p></div>
            <div><span class="text-gray-500">N° lot</span><p class="mt-1 font-mono">{{ $stockBatch->lot_number ?? '-' }}</p></div>
            <div><span class="text-gray-500">Date de péremption</span>
                <p class="mt-1 {{ $stockBatch->isExpired() ? 'text-red-600 font-bold' : '' }}">
                    {{ $stockBatch->expiry_date?->format('d/m/Y') ?? '-' }}
                </p>
            </div>
            <div><span class="text-gray-500">Statut</span>
                <p class="mt-1">@switch($stockBatch->status)
                    @case('active') <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs">Actif</span> @break
                    @case('depleted') <span class="px-2 py-1 bg-gray-100 text-gray-500 rounded-full text-xs">Épuisé</span> @break
                    @case('expired') <span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs">Périmé</span> @break
                    @case('written_off') <span class="px-2 py-1 bg-orange-100 text-orange-700 rounded-full text-xs">Rebus</span> @break
                    @default {{ $stockBatch->status }}
                @endswitch</p>
            </div>
            <div><span class="text-gray-500">Quantité disponible</span><p class="mt-1 text-2xl font-bold">{{ $stockBatch->quantity_available }}</p></div>
            <div><span class="text-gray-500">Quantité initiale</span><p class="mt-1">{{ $stockBatch->initial_quantity }}</p></div>
            <div><span class="text-gray-500">Coût unitaire</span><p class="mt-1">{{ number_format($stockBatch->unit_cost ?? 0, 0, ',', ' ') }} F</p></div>
            <div><span class="text-gray-500">Reçu le</span><p class="mt-1">{{ $stockBatch->received_at->format('d/m/Y') }}</p></div>
        </div>

        @if($stockBatch->status === 'active' && $stockBatch->quantity_available > 0)
        <div class="mt-6 pt-6 border-t border-gray-100 flex gap-3">
            @if($stockBatch->isExpired())
            <form method="POST" action="{{ route('batches.write-off', $stockBatch) }}" class="flex gap-2 items-end">
                @csrf
                <input type="hidden" name="reason" value="expired">
                <div>
                    <label class="text-xs text-gray-500">Quantité à mettre au rebut</label>
                    <input type="number" name="quantity" value="{{ $stockBatch->quantity_available }}" max="{{ $stockBatch->quantity_available }}"
                           class="w-24 px-3 py-2 border border-gray-300 rounded-xl text-sm">
                </div>
                <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm">
                    <i class="fa-solid fa-trash mr-1"></i> Mettre au rebut
                </button>
            </form>
            @endif
            <form method="POST" action="{{ route('batches.return-supplier', $stockBatch) }}" class="flex gap-2 items-end">
                @csrf
                <div>
                    <label class="text-xs text-gray-500">Quantité à retourner</label>
                    <input type="number" name="quantity" value="{{ min($stockBatch->quantity_available, 1) }}" max="{{ $stockBatch->quantity_available }}"
                           class="w-24 px-3 py-2 border border-gray-300 rounded-xl text-sm">
                </div>
                <button type="submit" class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-xl text-sm">
                    <i class="fa-solid fa-rotate-left mr-1"></i> Retour fournisseur
                </button>
            </form>
        </div>
        @endif
    </div>

    {{-- Movements --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h3 class="font-semibold text-gray-800 mb-4">Mouvements du lot</h3>
        @if($stockBatch->stockMovements->count())
        <table class="w-full text-sm">
            <thead><tr class="text-left text-gray-500"><th class="pb-2">Date</th><th class="pb-2">Type</th><th class="pb-2 text-right">Qté</th><th class="pb-2">Raison</th></tr></thead>
            <tbody>
                @foreach($stockBatch->stockMovements as $mvt)
                <tr class="border-t border-gray-50">
                    <td class="py-2">{{ $mvt->moved_at->format('d/m/Y H:i') }}</td>
                    <td class="py-2">
                        @if($mvt->type === 'in') <span class="text-green-600">Entrée</span>
                        @else <span class="text-red-600">Sortie</span>
                        @endif
                    </td>
                    <td class="py-2 text-right font-mono">{{ $mvt->quantity }}</td>
                    <td class="py-2 text-gray-500">{{ $mvt->reason ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p class="text-gray-400 text-center py-4">Aucun mouvement.</p>
        @endif
    </div>
</div>
@endsection
