@extends('layouts.app')
@section('title', 'Validation ordonnance')
@section('page-title', 'Validation : ' . $prescription->prescription_number)

@section('content')
<div class="space-y-6">

    {{-- Prescription info --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
            <div><span class="text-gray-500">N° ordonnance</span><p class="font-mono mt-1">{{ $prescription->prescription_number }}</p></div>
            <div><span class="text-gray-500">Patient</span><p class="mt-1 font-medium">{{ $prescription->patient?->first_name }} {{ $prescription->patient?->last_name }}</p></div>
            <div><span class="text-gray-500">Prescripteur</span><p class="mt-1">Dr. {{ $prescription->doctor?->getFullName() }}</p></div>
            <div><span class="text-gray-500">Date</span><p class="mt-1">{{ $prescription->issued_at?->format('d/m/Y H:i') }}</p></div>
        </div>
    </div>

    {{-- Items --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h3 class="font-semibold text-gray-800 mb-4">Articles prescrits</h3>
        <table class="w-full text-sm">
            <thead><tr class="text-left text-gray-500"><th class="pb-2">Médicament</th><th class="pb-2">Dosage</th><th class="pb-2">Quantité</th><th class="pb-2">Stock</th><th class="pb-2">Statut livret</th><th class="pb-2">Alertes</th></tr></thead>
            <tbody>
                @foreach($prescription->items as $item)
                @php $med = $item->medicine; @endphp
                <tr class="border-t border-gray-50">
                    <td class="py-2">{{ $item->medicine_name }}</td>
                    <td class="py-2">{{ $item->dosage }}</td>
                    <td class="py-2">{{ $item->quantity_prescribed }}</td>
                    <td class="py-2">{{ $med?->getCurrentStock() ?? 'N/A' }}</td>
                    <td class="py-2">
                        @if($med)
                        <span class="px-2 py-0.5 text-xs rounded-full
                            {{ $med->formulary_status === 'inscrit' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $med->formulary_status === 'inscrit' ? 'Inscrit' : 'Hors livret' }}
                        </span>
                        @else
                        <span class="text-red-500">Inconnu</span>
                        @endif
                    </td>
                    <td class="py-2">
                        @if($med?->is_narcotic)
                        <span class="px-2 py-0.5 bg-red-100 text-red-700 rounded-full text-xs">Stupéfiant</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Validation form --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h3 class="font-semibold text-gray-800 mb-4">Validation pharmaceutique</h3>
        <form method="POST" action="{{ route('validations.validate', $prescription) }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Décision *</label>
                <select name="status" required class="w-full px-4 py-2 border border-gray-300 rounded-xl text-sm">
                    <option value="approved">Approuvée</option>
                    <option value="modified">Approuvée avec modifications</option>
                    <option value="rejected">Refusée</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes" rows="3"
                          class="w-full px-4 py-2 border border-gray-300 rounded-xl text-sm"
                          placeholder="Observations, modifications proposées..."></textarea>
            </div>
            <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium">
                <i class="fa-solid fa-check mr-1"></i> Valider l'ordonnance
            </button>
        </form>
    </div>

    {{-- Validation history --}}
    @if($validations->count())
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h3 class="font-semibold text-gray-800 mb-4">Historique des validations</h3>
        @foreach($validations as $validation)
        <div class="p-4 bg-gray-50 rounded-xl mb-3">
            <div class="flex items-center justify-between text-sm">
                <div>
                    <span class="font-medium">{{ $validation->pharmacist?->getFullName() }}</span>
                    <span class="text-gray-400 ml-2">{{ $validation->validated_at?->format('d/m/Y H:i') }}</span>
                </div>
                <span class="px-2 py-0.5 text-xs rounded-full
                    {{ $validation->status === 'approved' ? 'bg-green-100 text-green-700' : ($validation->status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
                    {{ $validation->status }}
                </span>
            </div>
            @if($validation->notes)
            <p class="text-sm text-gray-600 mt-2">{{ $validation->notes }}</p>
            @endif
            @if($validation->interventions->count())
            <div class="mt-2 space-y-1">
                @foreach($validation->interventions as $int)
                <div class="text-xs text-gray-500 flex items-center gap-2">
                    <span class="px-1.5 py-0.5 bg-blue-100 text-blue-700 rounded">{{ $int->intervention_type }}</span>
                    <span>{{ $int->description }}</span>
                </div>
                @endforeach
            </div>
            @endif
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
