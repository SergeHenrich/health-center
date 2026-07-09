@extends('layouts.app')
@section('title', 'Livret Thérapeutique')
@section('page-title', 'Livret Thérapeutique')

@section('content')
<div class="space-y-4">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-center">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Rechercher..."
                   class="px-4 py-2 border border-gray-300 rounded-xl text-sm w-full sm:w-64 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <select name="status" class="border border-gray-300 rounded-xl text-sm px-3 py-2">
                <option value="">Tous les statuts</option>
                <option value="inscrit" {{ request('status') === 'inscrit' ? 'selected' : '' }}>Inscrit</option>
                <option value="substituable" {{ request('status') === 'substituable' ? 'selected' : '' }}>Substituable</option>
                <option value="non_inscrit" {{ request('status') === 'non_inscrit' ? 'selected' : '' }}>Non inscrit</option>
                <option value="retire" {{ request('status') === 'retire' ? 'selected' : '' }}>Retiré</option>
            </select>
            <select name="therapeutic_class" class="border border-gray-300 rounded-xl text-sm px-3 py-2">
                <option value="">Toutes classes</option>
                @foreach(\App\Models\Medicine::whereNotNull('therapeutic_class')->distinct()->pluck('therapeutic_class') as $class)
                <option value="{{ $class }}" {{ request('therapeutic_class') === $class ? 'selected' : '' }}>{{ $class }}</option>
                @endforeach
            </select>
            <label class="flex items-center gap-1 text-sm">
                <input type="checkbox" name="narcotic" value="1" {{ request('narcotic') ? 'checked' : '' }}
                       class="rounded border-gray-300 text-blue-600">
                Stupéfiants
            </label>
            <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-xl text-sm">Filtrer</button>
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Code</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">DCI / Spécialité</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">ATC</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Classe thérapeutique</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Statut livret</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Stock</th>
                    <th class="text-center px-6 py-4 font-semibold text-gray-600">Réglementé</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($medicines as $medicine)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 font-mono text-xs text-gray-500">{{ $medicine->code }}</td>
                    <td class="px-6 py-4">
                        <a href="{{ route('formulary.show', $medicine) }}" class="font-medium text-blue-600 hover:underline">
                            {{ $medicine->name }}
                        </a>
                        @if($medicine->generic_name)
                        <div class="text-xs text-gray-400">{{ $medicine->generic_name }}</div>
                        @endif
                    </td>
                    <td class="px-6 py-4 font-mono text-xs text-gray-500">{{ $medicine->atc_code ?? '-' }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ $medicine->therapeutic_class ?? '-' }}</td>
                    <td class="px-6 py-4">
                        @switch($medicine->formulary_status)
                        @case('inscrit')
                        <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">Inscrit</span>
                        @break
                        @case('substituable')
                        <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">Substituable</span>
                        @break
                        @case('non_inscrit')
                        <span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs font-medium">Hors livret</span>
                        @break
                        @case('retire')
                        <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-medium">Retiré</span>
                        @break
                        @endswitch
                    </td>
                    <td class="px-6 py-4">
                        @php $s = $medicine->getCurrentStock(); @endphp
                        <span class="{{ $s === 0 ? 'text-red-600 font-bold' : ($medicine->isLowStock() ? 'text-amber-600 font-bold' : 'text-gray-600') }}">
                            {{ $s }} unités
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if($medicine->is_narcotic)
                        <span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded-full">Stupéfiant</span>
                        @endif
                        @if($medicine->is_psychotropic)
                        <span class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full">Psychotrope</span>
                        @endif
                        @if($medicine->is_cold_chain)
                        <span class="text-xs bg-cyan-100 text-cyan-700 px-2 py-0.5 rounded-full">Chaîne du froid</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-6 py-12 text-center text-gray-400">Aucun médicament.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($medicines->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">{{ $medicines->withQueryString()->links() }}</div>
        @endif
    </div>
</div>
@endsection
