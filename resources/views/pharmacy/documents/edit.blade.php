@extends('layouts.app')
@section('title', 'Modifier ' . $pharmacyDocument->title)
@section('page-title', 'Modifier le document')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('pharmacy-documents.update', $pharmacyDocument) }}" enctype="multipart/form-data" class="space-y-4">
            @csrf @method('PUT')

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Titre *</label>
                    <input type="text" name="title" value="{{ $pharmacyDocument->title }}" required maxlength="200"
                           class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type *</label>
                    <select name="type" required class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm">
                        <option value="sop" {{ $pharmacyDocument->type === 'sop' ? 'selected' : '' }}>Procédure (SOP)</option>
                        <option value="contract" {{ $pharmacyDocument->type === 'contract' ? 'selected' : '' }}>Contrat</option>
                        <option value="regulatory" {{ $pharmacyDocument->type === 'regulatory' ? 'selected' : '' }}>Réglementaire</option>
                        <option value="reference" {{ $pharmacyDocument->type === 'reference' ? 'selected' : '' }}>Référence</option>
                        <option value="other" {{ $pharmacyDocument->type === 'other' ? 'selected' : '' }}>Autre</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Référence</label>
                <input type="text" name="reference" value="{{ $pharmacyDocument->reference }}" maxlength="100"
                       class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm">{{ $pharmacyDocument->description }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Fichier (laisser vide pour conserver l'actuel)</label>
                <input type="file" name="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.png,.jpg"
                       class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm">
                <p class="text-xs text-gray-400 mt-1">Actuel : {{ $pharmacyDocument->file_name }}</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Version</label>
                    <input type="text" name="version" value="{{ $pharmacyDocument->version }}" maxlength="20"
                           class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date d'expiration</label>
                    <input type="date" name="expiry_date" value="{{ $pharmacyDocument->expiry_date?->format('Y-m-d') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm">
                </div>
            </div>

            <div class="flex items-center gap-2">
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_active" value="1" {{ $pharmacyDocument->is_active ? 'checked' : '' }}
                           class="rounded border-gray-300 text-blue-600">
                    Document actif
                </label>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm">
                    <i class="fa-solid fa-save mr-1"></i> Enregistrer
                </button>
                <a href="{{ route('pharmacy-documents.show', $pharmacyDocument) }}" class="px-6 py-2 bg-gray-100 hover:bg-gray-200 rounded-xl text-sm">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
