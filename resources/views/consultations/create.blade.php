@extends('layouts.app')
@section('title', 'Nouvelle consultation')
@section('page-title', 'Ouvrir une consultation')

@section('content')
<div class="max-w-2xl mx-auto">

    <div class="bg-blue-50 border border-blue-100 rounded-2xl p-4 mb-6 flex items-center gap-3">
        <div class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold">
            {{ strtoupper(substr($patient->first_name,0,1).substr($patient->last_name,0,1)) }}
        </div>
        <div>
            <div class="font-semibold text-gray-800">{{ $patient->getFullName() }}</div>
            <div class="text-xs text-gray-500">{{ $patient->patient_code }} · {{ $patient->getAge() }} ans</div>
        </div>
    </div>

    <form method="POST" action="{{ route('consultations.store') }}">
        @csrf
        <input type="hidden" name="patient_id" value="{{ $patient->id }}">
        <input type="hidden" name="doctor_id" value="{{ auth()->id() }}">

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Motif de consultation *</label>
                <textarea name="chief_complaint" rows="2" required
                          class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
                          placeholder="Motif principal de la visite..."></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Histoire de la maladie</label>
                <textarea name="history_of_illness" rows="3"
                          class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
                          placeholder="Évolution des symptômes..."></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Examen physique</label>
                <textarea name="physical_examination" rows="3"
                          class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
                          placeholder="Observations cliniques..."></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('patients.show', $patient) }}" class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 rounded-xl text-sm text-gray-700">
                    Annuler
                </a>
                <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 rounded-xl text-sm text-white font-medium">
                    <i class="fa-solid fa-stethoscope"></i> Ouvrir la consultation
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
