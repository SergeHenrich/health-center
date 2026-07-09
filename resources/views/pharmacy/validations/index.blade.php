@extends('layouts.app')
@section('title', 'Validations pharmaceutiques')
@section('page-title', 'Validation des ordonnances')

@section('content')
<div class="space-y-4">
    @if($pending->count() === 0)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
        <i class="fa-solid fa-check-circle text-4xl text-green-400 mb-4"></i>
        <p class="text-gray-500">Aucune ordonnance en attente de validation pharmaceutique.</p>
    </div>
    @else
    <div class="space-y-3">
        @foreach($pending as $prescription)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition">
            <div class="flex items-start justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-mono text-sm text-gray-500">{{ $prescription->prescription_number }}</span>
                        <span class="px-2 py-0.5 text-xs rounded-full
                            {{ $prescription->status === 'pending' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700' }}">
                            {{ $prescription->status === 'pending' ? 'En attente' : 'Avec interventions' }}
                        </span>
                    </div>
                    <p class="font-medium text-gray-800 mt-1">
                        {{ $prescription->patient?->first_name }} {{ $prescription->patient?->last_name }}
                    </p>
                    <p class="text-xs text-gray-400 mt-1">
                        Dr. {{ $prescription->doctor?->getFullName() }} -
                        {{ $prescription->issued_at?->format('d/m/Y H:i') }}
                    </p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('validations.show', $prescription) }}"
                       class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm">
                        Valider
                    </a>
                </div>
            </div>
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach($prescription->items as $item)
                <span class="px-2 py-1 bg-gray-100 rounded-lg text-xs">
                    {{ $item->medicine_name }} × {{ $item->quantity_prescribed }}
                    @if($item->medicine && $item->medicine->is_narcotic)
                    <span class="text-red-500 ml-1">⚠️</span>
                    @endif
                </span>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
