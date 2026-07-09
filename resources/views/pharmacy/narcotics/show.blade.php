@extends('layouts.app')
@section('title', $medicine->name)
@section('page-title', 'Stupéfiant : ' . $medicine->name)

@section('content')
<div class="space-y-4">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 text-sm">
            <div>
                <span class="text-gray-500">Stock actuel</span>
                <p class="text-3xl font-bold mt-1 {{ $balance === 0 ? 'text-red-600' : 'text-gray-800' }}">
                    {{ $balance }}
                </p>
            </div>
            <div>
                <span class="text-gray-500">Code</span>
                <p class="font-mono mt-1">{{ $medicine->code }}</p>
            </div>
            <div>
                <span class="text-gray-500">Forme / Dosage</span>
                <p class="mt-1 capitalize">{{ $medicine->form }} - {{ $medicine->strength ?? '-' }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-x-auto">
        <div class="p-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Registre</h3>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Date</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">N° lot</th>
                    <th class="text-right px-6 py-4 font-semibold text-gray-600">Entrée</th>
                    <th class="text-right px-6 py-4 font-semibold text-gray-600">Sortie</th>
                    <th class="text-right px-6 py-4 font-semibold text-gray-600">Solde</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Pharmacien</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Patient</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($entries as $entry)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4">{{ $entry->recorded_at->format('d/m/Y H:i') }}</td>
                    <td class="px-6 py-4 font-mono text-xs text-gray-500">{{ $entry->lot_number ?? '-' }}</td>
                    <td class="px-6 py-4 text-right text-green-600">{{ $entry->quantity_in ?: '-' }}</td>
                    <td class="px-6 py-4 text-right text-red-600">{{ $entry->quantity_out ?: '-' }}</td>
                    <td class="px-6 py-4 text-right font-bold">{{ $entry->balance_after }}</td>
                    <td class="px-6 py-4">{{ $entry->pharmacist?->getFullName() }}</td>
                    <td class="px-6 py-4">{{ $entry->patient?->first_name ?? '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-6 py-12 text-center text-gray-400">Aucune entrée.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($entries->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">{{ $entries->links() }}</div>
        @endif
    </div>
</div>
@endsection
