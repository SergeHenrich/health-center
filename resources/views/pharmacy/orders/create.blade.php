@extends('layouts.app')
@section('title', 'Nouvelle commande')
@section('page-title', 'Bon de commande fournisseur')

@section('content')
<div class="max-w-3xl mx-auto">
    <form method="POST" action="{{ route('purchase-orders.store') }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
        @csrf

        @php $suppliersJson = $suppliers->map(fn($s) => ['id' => $s->id, 'name' => $s->name, 'contact' => $s->phone ?? $s->email ?? ''])->values(); @endphp
        <div x-data="{ suppliers: @json($suppliersJson), selectedId: '', contact: '' }" class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Fournisseur *</label>
                <select name="supplier_id" x-model="selectedId" required
                        x-on:change="contact = suppliers.find(s => s.id == selectedId)?.contact ?? ''"
                        class="w-full px-4 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">- Sélectionner un fournisseur -</option>
                    @foreach($suppliers as $s)
                    <option value="{{ $s->id }}" data-contact="{{ $s->phone ?? $s->email ?? '' }}">{{ $s->name }} ({{ $s->code }})</option>
                    @endforeach
                </select>
                @error('supplier_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Contact fournisseur</label>
                <input type="text" name="supplier_contact" x-model="contact" readonly
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl text-sm bg-gray-50 text-gray-600 focus:outline-none">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Livraison prévue</label>
            <input type="date" name="expected_delivery" value="{{ old('expected_delivery') }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea name="notes" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('notes') }}</textarea>
        </div>

        {{-- Items --}}
        <div x-data="{ items: [{ medicine_id: '', quantity_ordered: 1, unit_cost: 0 }] }">
            <div class="flex items-center justify-between mb-2">
                <label class="block text-sm font-medium text-gray-700">Articles *</label>
                <button type="button" @click="items.push({ medicine_id: '', quantity_ordered: 1, unit_cost: 0 })"
                        class="text-sm text-blue-600 hover:text-blue-800">
                    <i class="fa-solid fa-plus"></i> Ajouter une ligne
                </button>
            </div>

            <template x-for="(item, i) in items" :key="i">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-3 items-end">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Médicament</label>
                        <select :name="'items['+i+'][medicine_id]'" x-model="item.medicine_id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-</option>
                            @foreach($medicines as $m)
                            <option value="{{ $m->id }}" data-price="{{ $m->unit_price }}">{{ $m->code }} - {{ $m->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Quantité</label>
                        <input type="number" :name="'items['+i+'][quantity_ordered]'" x-model="item.quantity_ordered" min="1" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Prix unitaire</label>
                        <input type="number" step="0.01" :name="'items['+i+'][unit_cost]'" x-model="item.unit_cost" min="0" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <button type="button" @click="items.splice(i, 1)" x-show="items.length > 1"
                                class="px-3 py-2 bg-red-50 text-red-600 rounded-xl text-sm hover:bg-red-100">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </div>
            </template>
            @error('items') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex gap-3 pt-4">
            <button type="submit" class="px-6 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-xl text-sm font-medium">
                <i class="fa-solid fa-save mr-1"></i> Créer la commande
            </button>
            <a href="{{ route('purchase-orders.index') }}" class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-sm">
                Annuler
            </a>
        </div>
    </form>
</div>
@endsection
