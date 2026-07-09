@extends('layouts.app')
@section('title', 'Signaler un événement')
@section('page-title', 'Signaler un événement')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('medication-events.store') }}" class="space-y-4">
            @csrf

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type d'événement *</label>
                    <select name="type" required class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Sélectionner...</option>
                        <option value="medication_error">Erreur médicamenteuse</option>
                        <option value="adverse_drug_reaction">Effet indésirable</option>
                        <option value="near_miss">Presque-accident</option>
                        <option value="quality_incident">Incident qualité</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sévérité *</label>
                    <select name="severity" required class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Sélectionner...</option>
                        <option value="low">Faible</option>
                        <option value="medium">Moyenne</option>
                        <option value="high">Haute</option>
                        <option value="critical">Critique</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Médicament concerné</label>
                    <select name="medicine_id" class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm">
                        <option value="">- Aucun -</option>
                        @foreach(\App\Models\Medicine::active()->orderBy('name')->get() as $med)
                        <option value="{{ $med->id }}">{{ $med->name }} ({{ $med->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Patient concerné</label>
                    <select name="patient_id" class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm">
                        <option value="">- Aucun -</option>
                        @foreach(\App\Models\Patient::orderBy('first_name')->get() as $pat)
                        <option value="{{ $pat->id }}">{{ $pat->getFullName() }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description détaillée *</label>
                <textarea name="description" required rows="4"
                          class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="Décrivez l'événement..."></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cause identifiée</label>
                <textarea name="cause" rows="2"
                          class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="Cause potentielle..."></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Action immédiate</label>
                <textarea name="action_taken" rows="2"
                          class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="Action entreprise..."></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date de l'événement</label>
                <input type="datetime-local" name="occurred_at"
                       class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes complémentaires</label>
                <textarea name="notes" rows="2"
                          class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm">
                    <i class="fa-solid fa-flag mr-1"></i> Signaler l'événement
                </button>
                <a href="{{ route('medication-events.index') }}" class="px-6 py-2 bg-gray-100 hover:bg-gray-200 rounded-xl text-sm">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
