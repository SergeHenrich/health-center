@extends('layouts.app')
@section('title', 'Nouvelle demande d\'analyse')
@section('page-title', 'Créer une demande')

@section('content')
<div class="max-w-3xl space-y-6">

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h3 class="font-semibold text-gray-800 mb-1">Consultation #{{ $consultation->id }}</h3>
        <p class="text-sm text-gray-500">Patient : {{ $consultation->medicalRecord->patient->getFullName() }}</p>
    </div>

    <form method="POST" action="{{ route('lab-requests.store') }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
        @csrf
        <input type="hidden" name="consultation_id" value="{{ $consultation->id }}">
        <input type="hidden" name="patient_id" value="{{ $consultation->medicalRecord->patient->id }}">
        <input type="hidden" name="doctor_id" value="{{ auth()->id() }}">

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Urgence</label>
            <select name="urgency" required class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <option value="normal">Normale</option>
                <option value="urgent">Urgente</option>
                <option value="critical">Critique</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Informations cliniques</label>
            <textarea name="clinical_info" rows="3" class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
                      placeholder="Contexte clinique pour le biologiste..."></textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Examens demandés</label>
            @error('exam_ids') <p class="text-red-500 text-xs mb-2">{{ $message }}</p> @enderror
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-64 overflow-y-auto border border-gray-200 rounded-xl p-3">
                @php $grouped = $exams->groupBy('category'); @endphp
                @foreach($grouped as $category => $catExams)
                <div class="col-span-2 text-xs font-semibold text-gray-500 uppercase tracking-wide pt-2 first:pt-0">{{ $category }}</div>
                @foreach($catExams as $exam)
                <label class="flex items-center gap-2 p-2 rounded-lg hover:bg-gray-50 cursor-pointer">
                    <input type="checkbox" name="exam_ids[]" value="{{ $exam->id }}"
                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-sm text-gray-700">{{ $exam->name }}</span>
                    <span class="text-xs text-gray-400 ml-auto">{{ number_format($exam->price, 0, ',', ' ') }} XAF</span>
                </label>
                @endforeach
                @endforeach
            </div>
        </div>

        <div class="flex gap-3 pt-2">
            <a href="{{ url()->previous() }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 rounded-xl text-sm text-gray-700">Annuler</a>
            <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 rounded-xl text-sm text-white font-medium">
                <i class="fa-solid fa-paper-plane mr-1"></i> Soumettre la demande
            </button>
        </div>
    </form>
</div>
@endsection
