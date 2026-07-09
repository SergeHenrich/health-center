@extends('layouts.app')
@section('title', 'Dépôts')
@section('page-title', 'Gestion des dépôts')

@section('content')
<div class="space-y-4">
    <div class="flex justify-between items-center">
        <p class="text-sm text-gray-500">Gérez les dépôts de stock (pharmacie centrale, unités de soins, urgences, bloc).</p>
        <a href="{{ route('warehouses.create') }}"
           class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm">
            <i class="fa-solid fa-plus mr-1"></i> Nouveau dépôt
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($warehouses as $warehouse)
        @php $stats = $warehouse->stocks->first(); @endphp
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition">
            <div class="flex items-start justify-between">
                <div>
                    <h3 class="font-semibold text-gray-800">
                        <a href="{{ route('warehouses.show', $warehouse) }}" class="hover:text-blue-600">{{ $warehouse->name }}</a>
                    </h3>
                    <p class="text-xs text-gray-400 mt-1">
                        {{ $warehouse->code }} •
                        @switch($warehouse->type)
                        @case('central') Pharmacie centrale @break
                        @case('unit_care') Unité de soins @break
                        @case('emergency') Urgences @break
                        @case('bloc') Bloc opératoire @break
                        @default Autre
                        @endswitch
                    </p>
                </div>
                @if($warehouse->is_active)
                <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-xs">Actif</span>
                @else
                <span class="px-2 py-0.5 bg-red-100 text-red-700 rounded-full text-xs">Inactif</span>
                @endif
            </div>
            <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                <div>
                    <span class="text-gray-500">Médicaments</span>
                    <p class="font-bold">{{ $stats->total_medicines ?? 0 }}</p>
                </div>
                <div>
                    <span class="text-gray-500">Unités totales</span>
                    <p class="font-bold">{{ number_format($stats->total_qty ?? 0, 0, ',', ' ') }}</p>
                </div>
            </div>
            <div class="mt-4 flex gap-2">
                <a href="{{ route('warehouses.show', $warehouse) }}" class="text-blue-600 text-xs hover:underline">Voir le stock</a>
                <a href="{{ route('warehouses.edit', $warehouse) }}" class="text-gray-500 text-xs hover:underline">Modifier</a>
            </div>
        </div>
        @empty
        <div class="col-span-3 text-center py-12 text-gray-400">
            <i class="fa-solid fa-warehouse text-3xl mb-3 block"></i>
            Aucun dépôt configuré. Créez le dépôt principal de la pharmacie.
        </div>
        @endforelse
    </div>
</div>
@endsection
