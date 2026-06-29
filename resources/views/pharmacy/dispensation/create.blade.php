@extends('layouts.app')
@section('title', 'Nouvelle dispensation')
@section('page-title', 'Dispenser une ordonnance')

@section('content')
<div class="max-w-3xl mx-auto">
    @if($prescriptions->isEmpty())
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
        <i class="fa-solid fa-check-circle text-5xl text-green-400 mb-4 block"></i>
        <p class="text-gray-500">Aucune ordonnance en attente de dispensation.</p>
        <p class="text-gray-400 text-sm mt-2">Toutes les ordonnances ont été traitées.</p>
    </div>
    @else
    <form method="POST" action="{{ route('dispensations.store') }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Sélectionner une ordonnance *</label>
            <select name="prescription_id" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">- Choisir -</option>
                @foreach($prescriptions as $p)
                <option value="{{ $p->id }}" {{ old('prescription_id') == $p->id ? 'selected' : '' }}>
                    {{ $p->prescription_number }} - {{ $p->patient?->getFullName() ?? 'N/A' }}
                    ({{ $p->items->count() }} article(s))
                </option>
                @endforeach
            </select>
            @error('prescription_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        @if(old('prescription_id'))
        @php $selected = $prescriptions->firstWhere('id', old('prescription_id')); @endphp
        @if($selected)
        <div class="bg-gray-50 rounded-xl p-4">
            <h4 class="font-medium text-gray-700 mb-2">Détails de l'ordonnance</h4>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500">
                        <th class="pb-2">Médicament</th>
                        <th class="pb-2">Dosage</th>
                        <th class="pb-2">Quantité</th>
                        <th class="pb-2">Déjà dispensé</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($selected->items as $item)
                    <tr>
                        <td class="py-2">{{ $item->medicine_name }}</td>
                        <td class="py-2">{{ $item->dosage }}</td>
                        <td class="py-2">{{ $item->quantity_prescribed }}</td>
                        <td class="py-2">{{ $item->quantity_dispensed }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
        @endif

        <div class="flex gap-3 pt-4">
            <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium">
                <i class="fa-solid fa-check mr-1"></i> Dispenser
            </button>
            <a href="{{ route('dispensations.index') }}" class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-sm">
                Annuler
            </a>
        </div>
    </form>
    @endif
</div>
@endsection
