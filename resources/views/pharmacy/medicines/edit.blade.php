@extends('layouts.app')
@section('title', 'Modifier un médicament')
@section('page-title', 'Modifier : ' . $medicine->name)

@section('content')
<div class="max-w-2xl mx-auto">
    <form method="POST" action="{{ route('medicines.update', $medicine) }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Code</label>
                <input type="text" value="{{ $medicine->code }}" disabled
                       class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-xl text-sm text-gray-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Forme *</label>
                <select name="form" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach(['tablet'=>'Comprimé','capsule'=>'Gélule','syrup'=>'Sirop','injection'=>'Injectable','cream'=>'Crème','drops'=>'Gouttes','other'=>'Autre'] as $val => $label)
                    <option value="{{ $val }}" {{ $medicine->form === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
            <input type="text" name="name" value="{{ old('name', $medicine->name) }}" required
                   class="w-full px-4 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nom générique</label>
                <input type="text" name="generic_name" value="{{ old('generic_name', $medicine->generic_name) }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Catégorie</label>
                <input type="text" name="category" value="{{ old('category', $medicine->category) }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Dosage</label>
                <input type="text" name="strength" value="{{ old('strength', $medicine->strength) }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Prix unitaire *</label>
                <input type="number" name="unit_price" value="{{ old('unit_price', $medicine->unit_price) }}" step="0.01" min="0" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <div class="flex items-center">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="requires_prescription" value="1" {{ $medicine->requires_prescription ? 'checked' : '' }}
                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <span class="text-sm text-gray-700">Prescription requise</span>
            </label>
        </div>

        <div class="flex gap-3 pt-4">
            <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium">
                <i class="fa-solid fa-save mr-1"></i> Mettre à jour
            </button>
            <a href="{{ route('medicines.index') }}" class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-sm">
                Annuler
            </a>
        </div>
    </form>
</div>
@endsection
