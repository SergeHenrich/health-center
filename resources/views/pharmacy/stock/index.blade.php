@extends('layouts.app')
@section('title', 'Stock pharmacie')
@section('page-title', 'Gestion du stock')

@section('content')
<div class="space-y-4">

    {{-- Stats bar --}}
    @php
        $totalMeds    = $medicines->total();
        $lowStock     = $medicines->getCollection()->filter(fn($m) => $m->stock?->isLow())->count();
        $outOfStock   = $medicines->getCollection()->filter(fn($m) => $m->isOutOfStock())->count();
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 text-center">
            <div class="text-2xl font-bold text-gray-800">{{ $totalMeds }}</div>
            <div class="text-xs text-gray-500 mt-1">Médicaments</div>
        </div>
        <div class="bg-amber-50 rounded-2xl p-4 shadow-sm border border-amber-100 text-center">
            <div class="text-2xl font-bold text-amber-600">{{ $lowStock }}</div>
            <div class="text-xs text-amber-600 mt-1">Stock bas</div>
        </div>
        <div class="bg-red-50 rounded-2xl p-4 shadow-sm border border-red-100 text-center">
            <div class="text-2xl font-bold text-red-600">{{ $outOfStock }}</div>
            <div class="text-xs text-red-600 mt-1">Rupture de stock</div>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="flex flex-wrap gap-3 items-center justify-between">
        <form method="GET" action="{{ route('stock.index') }}" class="flex flex-wrap gap-2">
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                <input type="text" name="q" value="{{ request('q') }}"
                       placeholder="Rechercher un médicament..."
                       class="pl-9 pr-4 py-2 border border-gray-300 rounded-xl text-sm w-full sm:w-64 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <select name="filter" class="border border-gray-300 rounded-xl text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Tous</option>
                <option value="low" {{ request('filter') === 'low' ? 'selected' : '' }}>Stock bas</option>
                <option value="out" {{ request('filter') === 'out' ? 'selected' : '' }}>Rupture</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-xl text-sm">
                Filtrer
            </button>
        </form>

        <div class="flex gap-2">
            <a href="{{ route('purchase-orders.create') }}"
               class="flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 rounded-xl text-sm text-white">
                <i class="fa-solid fa-cart-plus"></i> Nouvelle commande
            </a>
            <a href="{{ route('medicines.create') }}"
               class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded-xl text-sm text-white">
                <i class="fa-solid fa-plus"></i> Ajouter médicament
            </a>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Code</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Médicament</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Forme</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Catégorie</th>
                    <th class="text-right px-6 py-4 font-semibold text-gray-600">Disponible</th>
                    <th class="text-right px-6 py-4 font-semibold text-gray-600">Minimum</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Statut</th>
                    <th class="text-right px-6 py-4 font-semibold text-gray-600">Prix unitaire</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($medicines as $medicine)
                @php $available = $medicine->getCurrentStock(); $isLow = $medicine->isLowStock(); $isOut = $medicine->isOutOfStock(); @endphp
                <tr class="hover:bg-gray-50 transition {{ $isOut ? 'bg-red-50' : ($isLow ? 'bg-amber-50' : '') }}">
                    <td class="px-6 py-4 font-mono text-xs text-gray-500">{{ $medicine->code }}</td>
                    <td class="px-6 py-4">
                        <div class="font-medium text-gray-800">{{ $medicine->name }}</div>
                        @if($medicine->generic_name)
                        <div class="text-xs text-gray-400">{{ $medicine->generic_name }}</div>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-gray-600 capitalize">{{ $medicine->form }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ $medicine->category ?? '-' }}</td>
                    <td class="px-6 py-4 text-right font-bold
                        {{ $isOut ? 'text-red-600' : ($isLow ? 'text-amber-600' : 'text-gray-800') }}">
                        {{ $available }}
                    </td>
                    <td class="px-6 py-4 text-right text-gray-500">
                        {{ $medicine->stock?->minimum_quantity ?? 10 }}
                    </td>
                    <td class="px-6 py-4">
                        @if($isOut)
                        <span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs font-medium">
                            Rupture
                        </span>
                        @elseif($isLow)
                        <span class="px-2 py-1 bg-amber-100 text-amber-700 rounded-full text-xs font-medium">
                            Stock bas
                        </span>
                        @else
                        <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">
                            Normal
                        </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right text-gray-700">
                        {{ number_format($medicine->unit_price, 0, ',', ' ') }} XAF
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                        <i class="fa-solid fa-pills text-3xl mb-3 block"></i>
                        Aucun médicament trouvé.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($medicines->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $medicines->withQueryString()->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
