@extends('layouts.app')
@section('title', 'Ajouter un document')
@section('page-title', 'Ajouter un document')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('pharmacy-documents.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Titre *</label>
                    <input type="text" name="title" required maxlength="200"
                           class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type *</label>
                    <select name="type" required class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm">
                        <option value="">Sélectionner...</option>
                        <option value="sop">Procédure (SOP)</option>
                        <option value="contract">Contrat</option>
                        <option value="regulatory">Réglementaire</option>
                        <option value="reference">Référence</option>
                        <option value="other">Autre</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Référence</label>
                <input type="text" name="reference" maxlength="100"
                       class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm"></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Fichier * (PDF, DOC, XLS, max 20 Mo)</label>
                <input type="file" name="file" required accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.png,.jpg"
                       class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Version</label>
                    <input type="text" name="version" value="1.0" maxlength="20"
                           class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date d'expiration</label>
                    <input type="date" name="expiry_date"
                           class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Médicament lié</label>
                    <select name="medicine_id" class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm">
                        <option value="">- Aucun -</option>
                        @foreach(\App\Models\Medicine::active()->orderBy('name')->get() as $med)
                        <option value="{{ $med->id }}">{{ $med->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fournisseur lié</label>
                    <select name="supplier_id" class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm">
                        <option value="">- Aucun -</option>
                        @foreach(\App\Models\Supplier::active()->orderBy('name')->get() as $sup)
                        <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm">
                    <i class="fa-solid fa-upload mr-1"></i> Uploader
                </button>
                <a href="{{ route('pharmacy-documents.index') }}" class="px-6 py-2 bg-gray-100 hover:bg-gray-200 rounded-xl text-sm">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
