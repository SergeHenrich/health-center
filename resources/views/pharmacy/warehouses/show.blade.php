@extends('layouts.app')
@section('title', $warehouse->name)
@section('page-title', $warehouse->name)

@section('content')
<div class="space-y-4">

    <div class="flex items-center justify-between">
        <div class="text-sm text-gray-500">{{ $warehouse->location ?? 'Emplacement non défini' }}</div>
        <div class="flex gap-2">
            <button @click="document.getElementById('transfer-form').classList.toggle('hidden')"
                    class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-sm">
                <i class="fa-solid fa-right-left mr-1"></i> Transférer
            </button>
            <a href="{{ route('warehouses.edit', $warehouse) }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-xl text-sm">Modifier</a>
        </div>
    </div>

    {{-- Transfer form --}}
    <form id="transfer-form" method="POST" action="{{ route('warehouses.transfer') }}" class="hidden bg-white rounded-2xl shadow-sm border border-gray-100 p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
        @csrf
        <input type="hidden" name="from_warehouse_id" value="{{ $warehouse->id }}">
        <div>
            <label class="text-xs text-gray-500">Vers</label>
            <select name="to_warehouse_id" required class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm">
                @foreach($warehouses as $w)
                <option value="{{ $w->id }}">{{ $w->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-xs text-gray-500">Médicament</label>
            <select name="medicine_id" required class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm">
                @foreach($medicines as $m)
                <option value="{{ $m->id }}">{{ $m->name }} ({{ $m->code }})</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-xs text-gray-500">Quantité</label>
            <input type="number" name="quantity" min="1" required class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm">
        </div>
        <div>
            <label class="text-xs text-gray-500">Motif</label>
            <input type="text" name="reason" class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm">
        </div>
        <button type="submit" class="px-4 py-2 bg-amber-600 text-white rounded-xl text-sm">Transférer</button>
    </form>

    {{-- Stock table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-x-auto">
        <div class="p-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800">Stock du dépôt</h3>
            <form method="GET" class="flex flex-wrap gap-2">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Rechercher..."
                       class="px-3 py-1.5 border border-gray-300 rounded-xl text-sm">
                <label class="flex items-center gap-1 text-sm">
                    <input type="checkbox" name="low" value="1" {{ request('low') ? 'checked' : '' }}
                           class="rounded border-gray-300 text-amber-600">
                    Stock bas
                </label>
                <button type="submit" class="px-3 py-1.5 bg-gray-100 rounded-xl text-sm">Filtrer</button>
            </form>
        </div>

        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Médicament</th>
                    <th class="text-right px-6 py-4 font-semibold text-gray-600">Disponible</th>
                    <th class="text-right px-6 py-4 font-semibold text-gray-600">Minimum</th>
                    <th class="text-right px-6 py-4 font-semibold text-gray-600">Maximum</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Statut</th>
                    <th class="text-right px-6 py-4 font-semibold text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($stocks as $ws)
                @php $isLow = $ws->isLow(); @endphp
                <tr class="hover:bg-gray-50 transition {{ $isLow ? 'bg-amber-50' : '' }}">
                    <td class="px-6 py-4">
                        <div class="font-medium text-gray-800">{{ $ws->medicine->name }}</div>
                        <div class="text-xs text-gray-400">{{ $ws->medicine->code }}</div>
                    </td>
                    <td class="px-6 py-4 text-right font-bold {{ $isLow ? 'text-amber-600' : 'text-gray-800' }}">
                        {{ $ws->quantity_available }}
                    </td>
                    <td class="px-6 py-4 text-right text-gray-500">{{ $ws->minimum_quantity }}</td>
                    <td class="px-6 py-4 text-right text-gray-500">{{ $ws->maximum_quantity }}</td>
                    <td class="px-6 py-4">
                        @if($ws->quantity_available === 0)
                        <span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs font-medium">Rupture</span>
                        @elseif($isLow)
                        <span class="px-2 py-1 bg-amber-100 text-amber-700 rounded-full text-xs font-medium">Stock bas</span>
                        @else
                        <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">Normal</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <form method="POST" action="{{ route('warehouses.stock.adjust', $ws) }}" class="flex gap-1 items-center justify-end" x-data="{ open: false }">
                            @csrf
                            <select name="type" x-model="open" required class="text-xs border border-gray-300 rounded-lg px-2 py-1">
                                <option value="in">Entrée</option>
                                <option value="out">Sortie</option>
                                <option value="adjustment">Ajuster</option>
                            </select>
                            <input type="number" name="quantity" min="1" required placeholder="Qty"
                                   class="w-16 text-xs border border-gray-300 rounded-lg px-2 py-1">
                            <input type="text" name="reason" required placeholder="Motif"
                                   class="w-24 text-xs border border-gray-300 rounded-lg px-2 py-1">
                            <button class="text-blue-600 text-xs hover:underline">OK</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-12 text-center text-gray-400">Aucun stock dans ce dépôt.</td></tr>
                @endforelse
            </tbody>
        </table>

        @if($stocks->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">{{ $stocks->links() }}</div>
        @endif
    </div>
</div>
@endsection
