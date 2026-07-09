@extends('layouts.app')
@section('title', 'Gestion des événements')
@section('page-title', 'Gestion des événements')

@section('content')
<div class="space-y-4">

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
        <div class="flex items-center justify-between">
            <form method="GET" class="flex flex-wrap gap-2">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Rechercher..."
                       class="px-4 py-2 border border-gray-300 rounded-xl text-sm w-full sm:w-48 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <select name="type" class="border border-gray-300 rounded-xl text-sm px-3 py-2">
                    <option value="">Tous types</option>
                    <option value="medication_error" {{ request('type') === 'medication_error' ? 'selected' : '' }}>Erreur médicamenteuse</option>
                    <option value="adverse_drug_reaction" {{ request('type') === 'adverse_drug_reaction' ? 'selected' : '' }}>Effet indésirable</option>
                    <option value="near_miss" {{ request('type') === 'near_miss' ? 'selected' : '' }}>Presque-accident</option>
                    <option value="quality_incident" {{ request('type') === 'quality_incident' ? 'selected' : '' }}>Incident qualité</option>
                </select>
                <select name="severity" class="border border-gray-300 rounded-xl text-sm px-3 py-2">
                    <option value="">Toute sévérité</option>
                    <option value="low" {{ request('severity') === 'low' ? 'selected' : '' }}>Faible</option>
                    <option value="medium" {{ request('severity') === 'medium' ? 'selected' : '' }}>Moyenne</option>
                    <option value="high" {{ request('severity') === 'high' ? 'selected' : '' }}>Haute</option>
                    <option value="critical" {{ request('severity') === 'critical' ? 'selected' : '' }}>Critique</option>
                </select>
                <select name="status" class="border border-gray-300 rounded-xl text-sm px-3 py-2">
                    <option value="">Tous statuts</option>
                    <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Ouvert</option>
                    <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Résolu</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-xl text-sm">Filtrer</button>
            </form>
            <a href="{{ route('medication-events.create') }}"
               class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm">
                <i class="fa-solid fa-plus mr-1"></i> Nouvel événement
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Date</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Type</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Description</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Sévérité</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Signalé par</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Statut</th>
                    <th class="text-center px-6 py-4 font-semibold text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($events as $event)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 whitespace-nowrap">{{ $event->occurred_at->format('d/m/Y') }}</td>
                    <td class="px-6 py-4">
                        @switch($event->type)
                            @case('medication_error') <span class="text-xs font-medium px-2 py-1 bg-red-50 text-red-700 rounded-full">Erreur méd.</span> @break
                            @case('adverse_drug_reaction') <span class="text-xs font-medium px-2 py-1 bg-orange-50 text-orange-700 rounded-full">Effet indés.</span> @break
                            @case('near_miss') <span class="text-xs font-medium px-2 py-1 bg-yellow-50 text-yellow-700 rounded-full">Presque-accident</span> @break
                            @case('quality_incident') <span class="text-xs font-medium px-2 py-1 bg-purple-50 text-purple-700 rounded-full">Incident qualité</span> @break
                        @endswitch
                    </td>
                    <td class="px-6 py-4 max-w-xs truncate">
                        <a href="{{ route('medication-events.show', $event) }}" class="text-blue-600 hover:underline">
                            {{ $event->description }}
                        </a>
                    </td>
                    <td class="px-6 py-4">
                        @switch($event->severity)
                            @case('critical') <span class="text-xs font-bold text-red-600">Critique</span> @break
                            @case('high') <span class="text-xs font-medium text-orange-600">Haute</span> @break
                            @case('medium') <span class="text-xs font-medium text-amber-600">Moyenne</span> @break
                            @default <span class="text-xs text-gray-500">Faible</span>
                        @endswitch
                    </td>
                    <td class="px-6 py-4">{{ $event->reporter?->getFullName() }}</td>
                    <td class="px-6 py-4">
                        @if($event->status === 'open')
                        <span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs font-medium">Ouvert</span>
                        @else
                        <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">Résolu</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        <a href="{{ route('medication-events.show', $event) }}" class="text-blue-600 hover:underline text-xs">Voir</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-6 py-12 text-center text-gray-400">Aucun événement signalé.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($events->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">{{ $events->links() }}</div>
        @endif
    </div>
</div>
@endsection
