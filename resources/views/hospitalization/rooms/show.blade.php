@extends('layouts.app')
@section('title', 'Chambre ' . $room->room_number)
@section('page-title', 'Détail chambre')

@section('content')
<div class="space-y-6">

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-blue-600 text-white flex items-center justify-center text-xl font-bold">
                <i class="fa-solid fa-bed"></i>
            </div>
            <div>
                <h3 class="text-xl font-bold text-gray-800">{{ $room->room_number }}</h3>
                <p class="text-sm text-gray-500">{{ ucfirst($room->type) }} · Étage {{ $room->floor ?? '—' }} · Capacité : {{ $room->capacity }} lit(s)</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h4 class="font-semibold text-gray-800">Lits</h4>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 p-6">
            @forelse($room->beds as $bed)
            <div class="border rounded-xl p-4 {{ $bed->status === 'available' ? 'border-green-200 bg-green-50/50' : 'border-red-200 bg-red-50/50' }}">
                <div class="flex items-center justify-between mb-2">
                    <span class="font-bold text-gray-800">{{ $bed->bed_number }}</span>
                    @if($bed->status === 'available')
                        <span class="px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">Libre</span>
                    @else
                        <span class="px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">Occupé</span>
                    @endif
                </div>
                <div class="text-xs text-gray-500 capitalize">{{ $bed->type }}</div>
                @if(isset($bed->currentHospitalization) && $bed->currentHospitalization)
                <div class="mt-2 pt-2 border-t border-gray-200 text-xs text-gray-600">
                    <i class="fa-solid fa-user mr-1"></i> {{ $bed->currentHospitalization->patient->getFullName() }}
                </div>
                @endif
            </div>
            @empty
            <div class="col-span-3 text-center py-8 text-gray-400">Aucun lit configuré.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
