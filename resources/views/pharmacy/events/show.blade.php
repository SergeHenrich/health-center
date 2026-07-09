@extends('layouts.app')
@section('title', 'Détail événement')
@section('page-title', 'Événement #' . $medicationEvent->id)

@section('content')
<div class="space-y-6">

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6 text-sm">
            <div>
                <span class="text-gray-500">Type</span>
                <p class="mt-1 font-medium">
                    @switch($medicationEvent->type)
                        @case('medication_error') Erreur médicamenteuse @break
                        @case('adverse_drug_reaction') Effet indésirable @break
                        @case('near_miss') Presque-accident @break
                        @case('quality_incident') Incident qualité @break
                    @endswitch
                </p>
            </div>
            <div>
                <span class="text-gray-500">Sévérité</span>
                <p class="mt-1">
                    @switch($medicationEvent->severity)
                        @case('critical') <span class="text-red-600 font-bold">Critique</span> @break
                        @case('high') <span class="text-orange-600 font-medium">Haute</span> @break
                        @case('medium') <span class="text-amber-600">Moyenne</span> @break
                        @default <span class="text-gray-500">Faible</span>
                    @endswitch
                </p>
            </div>
            <div>
                <span class="text-gray-500">Statut</span>
                <p class="mt-1">
                    @if($medicationEvent->status === 'open')
                    <span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs font-medium">Ouvert</span>
                    @else
                    <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">Résolu le {{ $medicationEvent->resolved_at?->format('d/m/Y') }}</span>
                    @endif
                </p>
            </div>
            <div>
                <span class="text-gray-500">Signalé par</span>
                <p class="mt-1">{{ $medicationEvent->reporter?->getFullName() ?? '-' }}</p>
            </div>
            <div>
                <span class="text-gray-500">Assigné à</span>
                <p class="mt-1">{{ $medicationEvent->assignee?->getFullName() ?? 'Non assigné' }}</p>
            </div>
            <div>
                <span class="text-gray-500">Date de l'événement</span>
                <p class="mt-1">{{ $medicationEvent->occurred_at->format('d/m/Y H:i') }}</p>
            </div>
            @if($medicationEvent->medicine)
            <div>
                <span class="text-gray-500">Médicament</span>
                <p class="mt-1">{{ $medicationEvent->medicine->name }}</p>
            </div>
            @endif
            @if($medicationEvent->patient)
            <div>
                <span class="text-gray-500">Patient</span>
                <p class="mt-1">{{ $medicationEvent->patient->getFullName() }}</p>
            </div>
            @endif
        </div>

        <div class="mt-6">
            <h4 class="text-sm font-semibold text-gray-700 mb-2">Description</h4>
            <p class="text-sm text-gray-600 bg-gray-50 rounded-xl p-4">{{ $medicationEvent->description }}</p>
        </div>

        @if($medicationEvent->cause)
        <div class="mt-4">
            <h4 class="text-sm font-semibold text-gray-700 mb-2">Cause identifiée</h4>
            <p class="text-sm text-gray-600 bg-gray-50 rounded-xl p-4">{{ $medicationEvent->cause }}</p>
        </div>
        @endif

        @if($medicationEvent->action_taken)
        <div class="mt-4">
            <h4 class="text-sm font-semibold text-gray-700 mb-2">Action entreprise</h4>
            <p class="text-sm text-gray-600 bg-gray-50 rounded-xl p-4">{{ $medicationEvent->action_taken }}</p>
        </div>
        @endif

        @if($medicationEvent->status === 'open')
        <div class="mt-6 flex gap-3 border-t border-gray-100 pt-6">
            <form method="POST" action="{{ route('medication-events.resolve', $medicationEvent) }}" class="flex-1">
                @csrf
                <div class="space-y-3">
                    <textarea name="action_taken" required rows="2" placeholder="Décrivez la résolution..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm"></textarea>
                    <textarea name="corrective_actions" rows="2" placeholder="Actions correctives..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm"></textarea>
                    <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-xl text-sm">
                        <i class="fa-solid fa-check mr-1"></i> Résoudre
                    </button>
                </div>
            </form>
        </div>
        @endif

        @if($medicationEvent->corrective_actions)
        <div class="mt-4">
            <h4 class="text-sm font-semibold text-gray-700 mb-2">Actions correctives</h4>
            <p class="text-sm text-gray-600 bg-gray-50 rounded-xl p-4">{{ $medicationEvent->corrective_actions }}</p>
        </div>
        @endif
    </div>

</div>
@endsection
