@extends('layouts.app')
@section('title', 'Ordonnance')
@section('page-title', 'Ordonnance ' . $prescription->prescription_number)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    {{-- Info --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <span class="text-gray-500">N° ordonnance :</span>
                <span class="font-mono font-medium text-gray-800 ml-2">{{ $prescription->prescription_number }}</span>
            </div>
            <div>
                <span class="text-gray-500">Patient :</span>
                <span class="font-medium text-gray-800 ml-2">{{ $prescription->patient?->getFullName() ?? '-' }}</span>
            </div>
            <div>
                <span class="text-gray-500">Médecin :</span>
                <span class="font-medium text-gray-800 ml-2">{{ $prescription->doctor?->getFullName() ?? '-' }}</span>
            </div>
            <div>
                <span class="text-gray-500">Date :</span>
                <span class="font-medium text-gray-800 ml-2">{{ $prescription->issued_at?->format('d/m/Y H:i') ?? '-' }}</span>
            </div>
            <div>
                <span class="text-gray-500">Valable jusqu'au :</span>
                <span class="font-medium text-gray-800 ml-2">{{ $prescription->valid_until?->format('d/m/Y') ?? '-' }}</span>
            </div>
            <div>
                <span class="text-gray-500">Statut :</span>
                <span class="ml-2 px-2 py-0.5 rounded-full text-xs font-medium
                    @if($prescription->status === 'dispensed') bg-green-100 text-green-700
                    @elseif($prescription->status === 'partially_dispensed') bg-amber-100 text-amber-700
                    @elseif($prescription->status === 'pending') bg-blue-100 text-blue-700
                    @else bg-gray-100 text-gray-600 @endif">
                    {{ $prescription->status }}
                </span>
            </div>
            @if($prescription->notes)
            <div class="col-span-2">
                <span class="text-gray-500">Notes :</span>
                <p class="text-gray-700 mt-1">{{ $prescription->notes }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Items --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 font-semibold text-gray-700">Prescription</div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-6 py-3 font-semibold text-gray-600">Médicament</th>
                    <th class="text-left px-6 py-3 font-semibold text-gray-600">Dosage</th>
                    <th class="text-left px-6 py-3 font-semibold text-gray-600">Fréquence</th>
                    <th class="text-right px-6 py-3 font-semibold text-gray-600">Quantité</th>
                    <th class="text-right px-6 py-3 font-semibold text-gray-600">Dispensé</th>
                    <th class="text-left px-6 py-3 font-semibold text-gray-600">Voie</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($prescription->items as $item)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-3 font-medium text-gray-800">{{ $item->medicine_name }}</td>
                    <td class="px-6 py-3 text-gray-600">{{ $item->dosage }}</td>
                    <td class="px-6 py-3 text-gray-600">{{ $item->frequency }}</td>
                    <td class="px-6 py-3 text-right text-gray-700">{{ $item->quantity_prescribed }}</td>
                    <td class="px-6 py-3 text-right @if($item->quantity_dispensed > 0) text-green-600 @else text-gray-400 @endif">
                        {{ $item->quantity_dispensed }}
                    </td>
                    <td class="px-6 py-3 text-gray-600 capitalize">{{ $item->route }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-400">Aucun article.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Dispensations --}}
    @if($prescription->dispensations->isNotEmpty())
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 font-semibold text-gray-700">Dispensations</div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-6 py-3 font-semibold text-gray-600">Date</th>
                    <th class="text-left px-6 py-3 font-semibold text-gray-600">Pharmacien</th>
                    <th class="text-right px-6 py-3 font-semibold text-gray-600">Détails</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($prescription->dispensations as $d)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-3 text-gray-600">{{ $d->dispensed_at?->format('d/m/Y H:i') ?? '-' }}</td>
                    <td class="px-6 py-3 text-gray-800">{{ $d->pharmacist?->getFullName() ?? '-' }}</td>
                    <td class="px-6 py-3 text-right">
                        <a href="{{ route('dispensations.show', $d) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                            <i class="fa-solid fa-eye"></i> Voir
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="flex gap-3">
        <a href="{{ url()->previous() }}" class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-sm">
            <i class="fa-solid fa-arrow-left mr-1"></i> Retour
        </a>
    </div>
</div>
@endsection
