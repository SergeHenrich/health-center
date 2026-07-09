@extends('layouts.app')
@section('title', 'Modifier dépôt')
@section('page-title', 'Modifier : ' . $warehouse->name)

@section('content')
<div class="max-w-xl mx-auto">
    <form method="POST" action="{{ route('warehouses.update', $warehouse) }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
        @csrf @method('PUT')
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Code</label>
                <input type="text" value="{{ $warehouse->code }}" disabled
                       class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-xl text-sm text-gray-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Type *</label>
                <select name="type" required class="w-full px-4 py-2 border border-gray-300 rounded-xl text-sm">
                    @foreach(['central'=>'Pharmacie centrale','unit_care'=>'Unité de soins','emergency'=>'Urgences','bloc'=>'Bloc opératoire','other'=>'Autre'] as $val => $label)
                    <option value="{{ $val }}" {{ $warehouse->type === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
            <input type="text" name="name" value="{{ old('name', $warehouse->name) }}" required
                   class="w-full px-4 py-2 border border-gray-300 rounded-xl text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Emplacement</label>
            <input type="text" name="location" value="{{ old('location', $warehouse->location) }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-xl text-sm">
        </div>
        <div class="flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" {{ $warehouse->is_active ? 'checked' : '' }}
                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
            <span class="text-sm text-gray-700">Dépôt actif</span>
        </div>
        <div class="flex gap-3 pt-4">
            <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium">Mettre à jour</button>
            <a href="{{ route('warehouses.index') }}" class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-sm">Annuler</a>
        </div>
    </form>
</div>
@endsection
