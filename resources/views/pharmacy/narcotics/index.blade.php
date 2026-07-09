@extends('layouts.app')
@section('title', 'Registre des stupéfiants')
@section('page-title', 'Registre des stupéfiants')

@section('content')
<div class="space-y-4">

    {{-- Narcotic medicines summary --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
        <h3 class="font-semibold text-gray-800 mb-3">Médicaments stupéfiants ({{ $medicines->count() }})</h3>
        @if($medicines->count())
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            @foreach($medicines as $m)
            <a href="{{ route('narcotics.show', $m) }}"
               class="flex items-center justify-between p-3 bg-red-50 hover:bg-red-100 rounded-xl transition text-sm">
                <div>
                    <div class="font-medium text-gray-800">{{ $m->name }}</div>
                    <div class="text-xs text-gray-500">{{ $m->code }} - {{ $m->form }} {{ $m->strength }}</div>
                </div>
                <div class="text-right">
                    <div class="font-bold text-red-700">{{ $m->getCurrentStock() }}</div>
                    <div class="text-xs text-gray-500">unités</div>
                </div>
            </a>
            @endforeach
        </div>
        @else
        <p class="text-gray-400 text-sm">Aucun médicament marqué comme stupéfiant.
            <a href="{{ route('formulary.index') }}" class="text-blue-600 underline">Aller au livret</a>
            pour en marquer un.</p>
        @endif
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
        <div class="flex items-center justify-between">
            <form method="GET" class="flex flex-wrap gap-2">
                <select name="medicine_id" class="border border-gray-300 rounded-xl text-sm px-3 py-2">
                    <option value="">Tous les stupéfiants</option>
                    @foreach($medicines as $m)
                    <option value="{{ $m->id }}" {{ request('medicine_id') == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                    @endforeach
                </select>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                       class="border border-gray-300 rounded-xl text-sm px-3 py-2">
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                       class="border border-gray-300 rounded-xl text-sm px-3 py-2">
                <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-xl text-sm">Filtrer</button>
            </form>
            <button @click="document.getElementById('entry-form').classList.toggle('hidden')"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm">
                <i class="fa-solid fa-plus mr-1"></i> Nouvelle entrée
            </button>
        </div>

        {{-- Entry form --}}
        <form id="entry-form" method="POST" action="{{ route('narcotics.store') }}" class="hidden mt-4 p-4 bg-gray-50 rounded-xl grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">
            @csrf
            <div>
                <label class="text-xs text-gray-500">Médicament *</label>
                <select name="medicine_id" required class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm">
                    @foreach($medicines as $m)
                    <option value="{{ $m->id }}">{{ $m->name }} (stock: {{ $m->getCurrentStock() }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs text-gray-500">Entrée</label>
                <input type="number" name="quantity_in" min="0" value="0"
                       class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm">
            </div>
            <div>
                <label class="text-xs text-gray-500">Sortie</label>
                <input type="number" name="quantity_out" min="0" value="0"
                       class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm">
            </div>
            <div>
                <label class="text-xs text-gray-500">N° lot</label>
                <input type="text" name="lot_number"
                       class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm">
            </div>
            <div>
                <label class="text-xs text-gray-500">Prescripteur</label>
                <input type="text" name="prescriber_name"
                       class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm">
            </div>
            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-xl text-sm">Enregistrer</button>
        </form>
    </div>

    {{-- Register table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Date</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Médicament</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">N° lot</th>
                    <th class="text-right px-6 py-4 font-semibold text-gray-600">Entrée</th>
                    <th class="text-right px-6 py-4 font-semibold text-gray-600">Sortie</th>
                    <th class="text-right px-6 py-4 font-semibold text-gray-600">Solde</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Pharmacien</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($registers as $entry)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4">{{ $entry->recorded_at->format('d/m/Y H:i') }}</td>
                    <td class="px-6 py-4">
                        <a href="{{ route('narcotics.show', $entry->medicine) }}" class="font-medium text-blue-600 hover:underline">
                            {{ $entry->medicine->name }}
                        </a>
                    </td>
                    <td class="px-6 py-4 font-mono text-xs text-gray-500">{{ $entry->lot_number ?? '-' }}</td>
                    <td class="px-6 py-4 text-right text-green-600 font-medium">{{ $entry->quantity_in ?: '-' }}</td>
                    <td class="px-6 py-4 text-right text-red-600 font-medium">{{ $entry->quantity_out ?: '-' }}</td>
                    <td class="px-6 py-4 text-right font-bold">{{ $entry->balance_after }}</td>
                    <td class="px-6 py-4">{{ $entry->pharmacist?->getFullName() }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-6 py-12 text-center text-gray-400">Aucune entrée dans le registre.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($registers->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">{{ $registers->links() }}</div>
        @endif
    </div>
</div>
@endsection
