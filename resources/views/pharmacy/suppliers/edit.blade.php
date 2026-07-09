@extends('layouts.app')
@section('title', 'Modifier fournisseur')
@section('page-title', 'Modifier : ' . $supplier->name)

@section('content')
<div class="max-w-2xl mx-auto">
    <form method="POST" action="{{ route('suppliers.update', $supplier) }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
        @csrf @method('PUT')
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Code</label>
                <input type="text" value="{{ $supplier->code }}" disabled
                       class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-xl text-sm text-gray-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
                <input type="text" name="name" value="{{ old('name', $supplier->name) }}" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Contact</label>
                <input type="text" name="contact_name" value="{{ old('contact_name', $supplier->contact_name) }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-xl text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Catégorie</label>
                <input type="text" name="category" value="{{ old('category', $supplier->category) }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-xl text-sm">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email', $supplier->email) }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-xl text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                <input type="text" name="phone" value="{{ old('phone', $supplier->phone) }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-xl text-sm">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Adresse</label>
            <textarea name="address" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-xl text-sm">{{ old('address', $supplier->address) }}</textarea>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Ville</label>
                <input type="text" name="city" value="{{ old('city', $supplier->city) }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-xl text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">N° fiscal</label>
                <input type="text" name="tax_id" value="{{ old('tax_id', $supplier->tax_id) }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-xl text-sm">
            </div>
        </div>
        <div class="flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" {{ $supplier->is_active ? 'checked' : '' }}
                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
            <span class="text-sm text-gray-700">Fournisseur actif</span>
        </div>
        <div class="flex gap-3 pt-4">
            <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium">Mettre à jour</button>
            <a href="{{ route('suppliers.index') }}" class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-sm">Annuler</a>
        </div>
    </form>
</div>
@endsection
