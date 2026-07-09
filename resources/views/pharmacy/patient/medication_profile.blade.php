@extends('layouts.app')
@section('title', 'Profil médicamenteux - ' . $patient->getFullName())
@section('page-title', 'Profil médicamenteux - ' . $patient->getFullName())

@section('content')
<div class="space-y-6">

    {{-- Patient Info --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
            <div><span class="text-gray-500">N° dossier</span><p class="mt-1 font-medium">{{ $patient->medical_record_number ?? '-' }}</p></div>
            <div><span class="text-gray-500">Âge</span><p class="mt-1">{{ $patient->date_of_birth ? $patient->getAge() . ' ans' : '-' }}</p></div>
            <div><span class="text-gray-500">Poids</span><p class="mt-1">{{ $patient->weight ?? '-' }} kg</p></div>
            <div><span class="text-gray-500">Allergies</span><p class="mt-1">{{ $patient->allergies ?? 'Aucune' }}</p></div>
        </div>
    </div>

    {{-- Current Prescriptions --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h3 class="font-semibold text-gray-800 mb-4">Ordonnances actives</h3>
        @if($activePrescriptions->count())
        @foreach($activePrescriptions as $prescription)
        <div class="border border-gray-100 rounded-xl p-4 mb-3">
            <div class="flex justify-between items-start">
                <div>
                    <span class="font-medium">{{ $prescription->prescription_number }}</span>
                    <span class="text-xs text-gray-500 ml-2">Dr {{ $prescription->doctor->name }} - {{ $prescription->issued_at?->format('d/m/Y') ?? $prescription->created_at->format('d/m/Y') }}</span>
                    <span class="{{ $prescription->status === 'active' ? 'text-green-600' : 'text-amber-600' }} text-xs ml-2">
                        {{ $prescription->status === 'active' ? 'En cours' : 'Partiellement dispensé' }}
                    </span>
                </div>
                <a href="{{ route('prescriptions.show', $prescription) }}" class="text-blue-600 text-xs hover:underline">Voir</a>
            </div>
            <div class="mt-2 space-y-1">
                @foreach($prescription->items as $item)
                <div class="flex justify-between text-sm">
                    <span>{{ $item->medicine->name }} - {{ $item->dosage }}</span>
                    <span class="text-gray-500">{{ $item->quantity_prescribed }} / {{ $item->quantity_dispensed ?? 0 }} dispensé{{ $item->quantity_dispensed > 0 ? 's' : '' }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
        @else
        <p class="text-gray-400 text-center py-4">Aucune ordonnance active.</p>
        @endif
    </div>

    {{-- Dispensation History --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-x-auto">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Historique des dispensations</h3>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-6 py-3 font-semibold text-gray-600">Date</th>
                    <th class="text-left px-6 py-3 font-semibold text-gray-600">Ordonnance</th>
                    <th class="text-left px-6 py-3 font-semibold text-gray-600">Pharmacien</th>
                    <th class="text-right px-6 py-3 font-semibold text-gray-600">Articles</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($dispensations as $d)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-3">{{ $d->dispensed_at->format('d/m/Y H:i') }}</td>
                    <td class="px-6 py-3">
                        <a href="{{ route('dispensations.show', $d) }}" class="text-blue-600 hover:underline">{{ $d->prescription->prescription_number }}</a>
                    </td>
                    <td class="px-6 py-3">{{ $d->pharmacist->name }}</td>
                    <td class="px-6 py-3 text-right">{{ $d->items_count ?? $d->items->count() }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-6 py-12 text-center text-gray-400">Aucune dispensation.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Medication Events --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-x-auto">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Événements médicamenteux</h3>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr><th class="text-left px-6 py-3 font-semibold text-gray-600">Date</th><th class="text-left px-6 py-3 font-semibold text-gray-600">Type</th><th class="text-left px-6 py-3 font-semibold text-gray-600">Sévérité</th><th class="text-left px-6 py-3 font-semibold text-gray-600">Statut</th></tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($medicationEvents as $event)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-3">{{ $event->occurred_at->format('d/m/Y') }}</td>
                    <td class="px-6 py-3">{{ __("pharmacy.events.type.{$event->type}") }}</td>
                    <td class="px-6 py-3">{{ $event->severity }}</td>
                    <td class="px-6 py-3">{{ $event->resolved_at ? 'Résolu' : 'Ouvert' }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-6 py-12 text-center text-gray-400">Aucun événement.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
