@extends('layouts.app')
@section('title', 'Admission ' . $hospitalization->admission_number)
@section('page-title', 'Détail admission')

@section('content')
<div class="space-y-6">

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-start justify-between">
            <div>
                <h3 class="text-lg font-bold text-gray-800 font-mono">{{ $hospitalization->admission_number }}</h3>
                <p class="text-sm text-gray-500 mt-1">{{ $hospitalization->patient->getFullName() }}</p>
                <p class="text-sm text-gray-500">Admis le {{ $hospitalization->admission_date->format('d/m/Y à H:i') }} par Dr. {{ $hospitalization->admittingDoctor->last_name }}</p>
            </div>
            <div class="flex gap-2">
                @php $sColors = ['admitted' => 'bg-blue-100 text-blue-700', 'in_care' => 'bg-purple-100 text-purple-700', 'discharged' => 'bg-green-100 text-green-700']; @endphp
                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $sColors[$hospitalization->status] ?? '' }}">{{ ucfirst(str_replace('_', ' ', $hospitalization->status)) }}</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs text-gray-400 uppercase tracking-wide mb-1">Chambre</div>
            <div class="font-semibold text-gray-800">{{ $hospitalization->room->room_number }}</div>
            <div class="text-sm text-gray-500">{{ $hospitalization->room->type }} · Étage {{ $hospitalization->room->floor ?? '—' }}</div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs text-gray-400 uppercase tracking-wide mb-1">Lit</div>
            <div class="font-semibold text-gray-800">{{ $hospitalization->bed->bed_number }}</div>
            <div class="text-sm text-gray-500">{{ $hospitalization->bed->type }}</div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs text-gray-400 uppercase tracking-wide mb-1">Infirmier(ère)</div>
            <div class="font-semibold text-gray-800">{{ $hospitalization->attendingNurse ? $hospitalization->attendingNurse->last_name : '—' }}</div>
            <div class="text-sm text-gray-500">Assigné(e) à l'admission</div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h4 class="font-semibold text-gray-800 mb-2">Motif d'admission</h4>
        <p class="text-sm text-gray-600">{{ $hospitalization->reason_for_admission }}</p>
    </div>

    @if($hospitalization->status !== 'discharged')
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6" x-data="{ showDischarge: false }">
        <button @click="showDischarge = !showDischarge"
                class="flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 rounded-xl text-sm text-white">
            <i class="fa-solid fa-right-from-bracket"></i> Sortir le patient
        </button>
        <div x-show="showDischarge" x-transition class="mt-4 space-y-4">
            <form method="POST" action="{{ route('hospitalizations.discharge', $hospitalization) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Résumé de sortie *</label>
                    <textarea name="discharge_summary" rows="4" required class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
                              placeholder="Résumé médical de l'hospitalisation..."></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">État de sortie *</label>
                    <select name="discharge_condition" required class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="recovered">Guéri</option>
                        <option value="improved">Amélioré</option>
                        <option value="unchanged">Stationnaire</option>
                        <option value="worsened">Aggravé</option>
                        <option value="deceased">Décédé</option>
                    </select>
                </div>
                <button type="submit" class="px-5 py-2.5 bg-green-600 hover:bg-green-700 rounded-xl text-sm text-white font-medium">
                    Confirmer la sortie
                </button>
            </form>
        </div>
    </div>
    @endif

    @if($hospitalization->careRecords->isNotEmpty())
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h4 class="font-semibold text-gray-800">Historique des soins</h4>
        </div>
        <div class="divide-y divide-gray-50">
            @foreach($hospitalization->careRecords as $care)
            <div class="px-6 py-4">
                <div class="flex items-center justify-between">
                    <span class="font-medium text-gray-800 text-sm">{{ $care->care_type }}</span>
                    <span class="text-xs text-gray-400">{{ $care->performed_at->format('d/m/Y H:i') }}</span>
                </div>
                <p class="text-sm text-gray-600 mt-1">{{ $care->description }}</p>
                <p class="text-xs text-gray-400 mt-1">Par {{ $care->nurse->last_name }}</p>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @if($hospitalization->discharge_summary)
    <div class="bg-green-50 rounded-2xl border border-green-200 p-6">
        <h4 class="font-semibold text-green-800 mb-2">Résumé de sortie</h4>
        <p class="text-sm text-green-700">{{ $hospitalization->discharge_summary }}</p>
        <p class="text-xs text-green-600 mt-2">État : {{ ucfirst($hospitalization->discharge_condition) }} · Sorti le {{ $hospitalization->discharge_date?->format('d/m/Y H:i') }}</p>
    </div>
    @endif
</div>
@endsection
