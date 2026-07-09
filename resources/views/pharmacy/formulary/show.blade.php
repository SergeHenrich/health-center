@extends('layouts.app')
@section('title', $medicine->name)
@section('page-title', 'Livret - ' . $medicine->name)

@section('content')
<div class="space-y-6">

    {{-- Medicine info --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6 text-sm">
            <div><span class="text-gray-500">Code</span><p class="font-mono mt-1">{{ $medicine->code }}</p></div>
            <div><span class="text-gray-500">ATC</span><p class="mt-1">{{ $medicine->atc_code ?? '-' }}</p></div>
            <div><span class="text-gray-500">Classe thérapeutique</span><p class="mt-1">{{ $medicine->therapeutic_class ?? '-' }}</p></div>
            <div><span class="text-gray-500">Forme</span><p class="mt-1 capitalize">{{ $medicine->form }}</p></div>
            <div><span class="text-gray-500">Dosage</span><p class="mt-1">{{ $medicine->strength ?? '-' }}</p></div>
            <div><span class="text-gray-500">Stock central</span><p class="mt-1">{{ $medicine->getCurrentStock() }} unités</p></div>
            <div><span class="text-gray-500">Prix unitaire</span><p class="mt-1">{{ number_format($medicine->unit_price, 0, ',', ' ') }} XAF</p></div>
            <div>
                <span class="text-gray-500">Statut livret</span>
                <p class="mt-1">
                    @switch($medicine->formulary_status)
                    @case('inscrit') <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">Inscrit</span> @break
                    @case('substituable') <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">Substituable</span> @break
                    @case('non_inscrit') <span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs font-medium">Hors livret</span> @break
                    @case('retire') <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-medium">Retiré</span> @break
                    @endswitch
                </p>
            </div>
        </div>
        @if($medicine->is_narcotic || $medicine->is_psychotropic || $medicine->is_cold_chain)
        <div class="mt-4 flex gap-2">
            @if($medicine->is_narcotic) <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs font-medium">Stupéfiant</span> @endif
            @if($medicine->is_psychotropic) <span class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-medium">Psychotrope</span> @endif
            @if($medicine->is_cold_chain) <span class="px-3 py-1 bg-cyan-100 text-cyan-700 rounded-full text-xs font-medium">Chaîne du froid (max {{ $medicine->max_temperature }}°C)</span> @endif
        </div>
        @endif
    </div>

    {{-- Update status --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h3 class="font-semibold text-gray-800 mb-4">Mettre à jour le statut du livret</h3>
        <form method="POST" action="{{ route('formulary.update-status', $medicine) }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">
            @csrf @method('PUT')
            <div>
                <label class="text-xs text-gray-500">Statut</label>
                <select name="formulary_status" class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm">
                    <option value="inscrit" {{ $medicine->formulary_status === 'inscrit' ? 'selected' : '' }}>Inscrit</option>
                    <option value="substituable" {{ $medicine->formulary_status === 'substituable' ? 'selected' : '' }}>Substituable</option>
                    <option value="non_inscrit" {{ $medicine->formulary_status === 'non_inscrit' ? 'selected' : '' }}>Non inscrit</option>
                    <option value="retire" {{ $medicine->formulary_status === 'retire' ? 'selected' : '' }}>Retiré</option>
                </select>
            </div>
            <div>
                <label class="text-xs text-gray-500">Code ATC</label>
                <input type="text" name="atc_code" value="{{ $medicine->atc_code }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm">
            </div>
            <div>
                <label class="text-xs text-gray-500">Classe thérapeutique</label>
                <input type="text" name="therapeutic_class" value="{{ $medicine->therapeutic_class }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm">
            </div>
            <div class="flex items-end pb-1">
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="hidden" name="is_narcotic" value="0">
                    <input type="checkbox" name="is_narcotic" value="1" class="sr-only peer"
                           {{ $medicine->is_narcotic ? 'checked' : '' }}>
                    <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-red-500"></div>
                    <span class="ml-2 text-xs text-gray-700">Stupéfiant</span>
                </label>
            </div>
            <div class="flex items-end pb-1">
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="hidden" name="is_psychotropic" value="0">
                    <input type="checkbox" name="is_psychotropic" value="1" class="sr-only peer"
                           {{ $medicine->is_psychotropic ? 'checked' : '' }}>
                    <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-purple-500"></div>
                    <span class="ml-2 text-xs text-gray-700">Psychotrope</span>
                </label>
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm">Mettre à jour</button>
        </form>
    </div>

    {{-- Commission decisions --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-800">Décisions de la Commission du Livret</h3>
            <button @click="document.getElementById('commission-form').classList.toggle('hidden')"
                    class="text-blue-600 text-sm hover:underline">+ Nouvelle décision</button>
        </div>
        <form id="commission-form" method="POST" action="{{ route('formulary.commission-decision.store', $medicine) }}" class="hidden mb-6 p-4 bg-gray-50 rounded-xl space-y-3">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <select name="decision" required class="px-3 py-2 border border-gray-300 rounded-xl text-sm">
                    <option value="admission">Admission</option>
                    <option value="renewal">Renouvellement</option>
                    <option value="rejection">Rejet</option>
                    <option value="removal">Retrait</option>
                    <option value="modification">Modification</option>
                </select>
                <input type="date" name="decision_date" required class="px-3 py-2 border border-gray-300 rounded-xl text-sm">
                <input type="date" name="review_date" class="px-3 py-2 border border-gray-300 rounded-xl text-sm" placeholder="Date révision">
            </div>
            <textarea name="justification" rows="2" required placeholder="Justification..."
                      class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm"></textarea>
            <input type="text" name="reference_document" placeholder="Document de référence"
                   class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm">
            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-xl text-sm">Enregistrer</button>
        </form>
        @if($commissionHistory->count())
        <table class="w-full text-sm">
            <thead><tr class="text-left text-gray-500"><th class="pb-2">Date</th><th class="pb-2">Décision</th><th class="pb-2">Justification</th><th class="pb-2">Décideur</th><th class="pb-2">Révision</th></tr></thead>
            <tbody>
                @foreach($commissionHistory as $dec)
                <tr class="border-t border-gray-50">
                    <td class="py-2">{{ $dec->decision_date->format('d/m/Y') }}</td>
                    <td class="py-2"><span class="px-2 py-0.5 bg-gray-100 rounded-full text-xs capitalize">{{ $dec->decision }}</span></td>
                    <td class="py-2 text-gray-600 max-w-xs truncate">{{ $dec->justification }}</td>
                    <td class="py-2">{{ $dec->decidedBy?->getFullName() ?? '-' }}</td>
                    <td class="py-2">{{ $dec->review_date?->format('d/m/Y') ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else <p class="text-gray-400 text-sm">Aucune décision enregistrée.</p> @endif
    </div>

    {{-- Substitutions --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-800">Substitutions thérapeutiques</h3>
            <button @click="document.getElementById('sub-form').classList.toggle('hidden')"
                    class="text-blue-600 text-sm hover:underline">+ Ajouter</button>
        </div>
        <form id="sub-form" method="POST" action="{{ route('formulary.substitutions.store', $medicine) }}" class="hidden mb-6 p-4 bg-gray-50 rounded-xl space-y-3">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <select name="substitute_medicine_id" required class="px-3 py-2 border border-gray-300 rounded-xl text-sm">
                    <option value="">Substitut...</option>
                    @foreach(\App\Models\Medicine::where('id', '!=', $medicine->id)->active()->orderBy('name')->get() as $sub)
                    <option value="{{ $sub->id }}">{{ $sub->name }} ({{ $sub->code }})</option>
                    @endforeach
                </select>
                <select name="substitution_type" required class="px-3 py-2 border border-gray-300 rounded-xl text-sm">
                    <option value="generic">Générique</option>
                    <option value="therapeutic">Thérapeutique</option>
                    <option value="alternative">Alternative</option>
                </select>
                <input type="text" name="reason" placeholder="Motif" class="px-3 py-2 border border-gray-300 rounded-xl text-sm">
            </div>
            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-xl text-sm">Enregistrer</button>
        </form>
        @if($substitutions->count())
        <div class="space-y-2">
            @foreach($substitutions as $sub)
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                <div class="text-sm">
                    @if($sub->medicine_id === $medicine->id)
                    <span class="text-gray-500">→ Substitut :</span>
                    <span class="font-medium">{{ $sub->substitute->name }}</span>
                    @else
                    <span class="text-gray-500">Substitut de :</span>
                    <span class="font-medium">{{ $sub->medicine->name }}</span>
                    @endif
                    <span class="ml-2 px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full text-xs">{{ $sub->substitution_type }}</span>
                    @if(!$sub->is_active) <span class="ml-1 text-red-500 text-xs">(Inactif)</span> @endif
                </div>
                @if($sub->is_active)
                <form method="POST" action="{{ route('formulary.substitutions.destroy', $sub) }}">
                    @csrf @method('DELETE')
                    <button class="text-red-500 text-xs hover:underline">Désactiver</button>
                </form>
                @endif
            </div>
            @endforeach
        </div>
        @else <p class="text-gray-400 text-sm">Aucune substitution enregistrée.</p> @endif
    </div>
</div>
@endsection
