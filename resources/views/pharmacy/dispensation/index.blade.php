@extends('layouts.app')
@section('title', 'Dispensations')
@section('page-title', 'Historique des dispensations')

@section('content')
<div class="space-y-4">
    <div class="flex justify-between items-center">
        <p class="text-sm text-gray-500">{{ $dispensations->total() }} dispensation(s)</p>
        <a href="{{ route('dispensations.create') }}"
           class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded-xl text-sm text-white">
            <i class="fa-solid fa-plus"></i> Nouvelle dispensation
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Ordonnance</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Patient</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Pharmacien</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Date</th>
                    <th class="text-center px-6 py-4 font-semibold text-gray-600">Facture</th>
                    <th class="text-right px-6 py-4 font-semibold text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($dispensations as $d)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 font-mono text-xs text-gray-500">{{ $d->prescription?->prescription_number ?? '-' }}</td>
                    <td class="px-6 py-4 font-medium text-gray-800">{{ $d->prescription?->patient?->getFullName() ?? '-' }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ $d->pharmacist?->getFullName() ?? '-' }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ $d->dispensed_at?->format('d/m/Y H:i') ?? '-' }}</td>
                    <td class="px-6 py-4 text-center">
                        @if($d->relationLoaded('invoice') && $d->invoice)
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                @if($d->invoice->isPaid()) bg-green-100 text-green-700
                                @elseif($d->invoice->isOverdue()) bg-red-100 text-red-700
                                @else bg-yellow-100 text-yellow-700 @endif">
                                {{ strtoupper($d->invoice->status) }}
                            </span>
                        @else
                            <span class="text-gray-300">-</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('dispensations.show', $d) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                            <i class="fa-solid fa-eye"></i> Détails
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                        <i class="fa-solid fa-pills text-3xl mb-3 block"></i>
                        Aucune dispensation effectuée.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($dispensations->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $dispensations->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
