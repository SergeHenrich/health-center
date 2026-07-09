@extends('layouts.app')
@section('title', 'Nouvelle ordonnance')
@section('page-title', 'Prescrire des médicaments')

@section('content')
<div class="max-w-3xl mx-auto">
    <form method="POST" action="{{ route('consultations.prescriptions.store', $consultation) }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
        @csrf

        <div class="bg-blue-50 rounded-xl p-4 text-sm text-blue-800">
            <i class="fa-solid fa-info-circle mr-1"></i>
            Consultation du {{ $consultation->created_at?->format('d/m/Y') ?? '-' }}
            -
            <strong>{{ $consultation->patient?->getFullName() ?? 'Patient' }}</strong>
        </div>

        <div x-data="{ items: [{ medicine_id: '', dosage: '', frequency: '', duration: '', quantity_prescribed: 1, route: 'oral', instructions: '' }] }">
            <div class="flex items-center justify-between mb-3">
                <label class="block text-sm font-medium text-gray-700">Médicaments *</label>
                <button type="button" @click="items.push({ medicine_id: '', dosage: '', frequency: '', duration: '', quantity_prescribed: 1, route: 'oral', instructions: '' })"
                        class="text-sm text-blue-600 hover:text-blue-800">
                    <i class="fa-solid fa-plus"></i> Ajouter
                </button>
            </div>

            <template x-for="(item, i) in items" :key="i">
                <div class="bg-gray-50 rounded-xl p-4 mb-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Médicament</label>
                            <select :name="'items['+i+'][medicine_id]'" x-model="item.medicine_id" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">-</option>
                                @foreach($medicines as $m)
                                <option value="{{ $m->id }}">{{ $m->name }} ({{ $m->form }}{{ $m->strength ? ', '.$m->strength : '' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Dosage</label>
                            <input type="text" :name="'items['+i+'][dosage]'" x-model="item.dosage" required placeholder="500mg"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Fréquence</label>
                            <input type="text" :name="'items['+i+'][frequency]'" x-model="item.frequency" required placeholder="3x/jour"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Durée</label>
                            <input type="text" :name="'items['+i+'][duration]'" x-model="item.duration" placeholder="7 jours"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Quantité</label>
                            <input type="number" :name="'items['+i+'][quantity_prescribed]'" x-model="item.quantity_prescribed" min="1" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Voie</label>
                            <select :name="'items['+i+'][route]'" x-model="item.route"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="oral">Orale</option>
                                <option value="injectable">Injectable</option>
                                <option value="topical">Topique</option>
                                <option value="inhaled">Inhalée</option>
                                <option value="other">Autre</option>
                            </select>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs text-gray-500 mb-1">Instructions</label>
                            <input type="text" :name="'items['+i+'][instructions]'" x-model="item.instructions"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <button type="button" @click="items.splice(i, 1)" x-show="items.length > 1"
                            class="mt-2 text-sm text-red-600 hover:text-red-800">
                        <i class="fa-solid fa-trash"></i> Retirer
                    </button>
                </div>
            </template>
            @error('items') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Valable jusqu'au</label>
                <input type="date" name="valid_until" value="{{ old('valid_until', now()->addDays(30)->format('Y-m-d')) }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <input type="text" name="notes" value="{{ old('notes') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <div class="flex gap-3 pt-4">
            <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium">
                <i class="fa-solid fa-save mr-1"></i> Enregistrer l'ordonnance
            </button>
            <a href="{{ route('consultations.show', $consultation) }}" class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-sm">
                Annuler
            </a>
        </div>
    </form>
</div>
@endsection
