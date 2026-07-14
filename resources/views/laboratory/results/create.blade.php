@extends('layouts.app')
@section('title', 'Saisir un résultat')
@section('page-title', 'Enregistrer le résultat')

@section('content')
<div class="max-w-2xl space-y-6">

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h3 class="font-semibold text-gray-800">{{ $item->labExam->name }}</h3>
        <p class="text-sm text-gray-500">Patient : {{ $item->labRequest->patient->getFullName() }} · Demande {{ $item->labRequest->request_number }}</p>
        @if($item->labExam->normal_range)
        <p class="text-xs text-gray-400 mt-1">Valeurs normales : {{ $item->labExam->normal_range }} {{ $item->labExam->unit }}</p>
        @endif
    </div>

    <form method="POST" action="{{ route('lab-results.store') }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
        @csrf
        <input type="hidden" name="lab_request_item_id" value="{{ $item->id }}">

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Valeur du résultat *</label>
                <input type="text" name="result_value" value="{{ old('result_value') }}" required
                       class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                @error('result_value') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Unité</label>
                <input type="text" name="unit" value="{{ old('unit', $item->labExam->unit) }}"
                       class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Plage de référence</label>
                <input type="text" name="reference_range" value="{{ old('reference_range', $item->labExam->normal_range) }}"
                       class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Interprétation *</label>
                <select name="interpretation" required class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="normal">Normal</option>
                    <option value="low">Bas</option>
                    <option value="high">Élevé</option>
                    <option value="critical">Critique</option>
                </select>
                @error('interpretation') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea name="notes" rows="2" class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"></textarea>
        </div>

        <div class="flex gap-3 pt-2">
            <a href="{{ url()->previous() }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 rounded-xl text-sm text-gray-700">Annuler</a>
            <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 rounded-xl text-sm text-white font-medium">
                <i class="fa-solid fa-save mr-1"></i> Enregistrer
            </button>
        </div>
    </form>
</div>
@endsection
