@extends('layouts.app')
@section('title', 'Consultation')
@section('page-title', 'Détail consultation')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-bold text-gray-800">{{ $consultation->medicalRecord->patient->getFullName() }}</h2>
            <div class="text-sm text-gray-500 mt-1">
                {{ $consultation->consultation_date->format('d/m/Y') }} · Dr. {{ $consultation->doctor->getFullName() }}
            </div>
        </div>
        <div class="flex items-center gap-3">
            <span class="px-3 py-1 rounded-full text-sm font-medium
                {{ $consultation->status === 'closed' ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700' }}">
                {{ ucfirst($consultation->status) }}
            </span>
            @if($consultation->isOpen())
            <form method="POST" action="{{ route('consultations.close', $consultation) }}">
                @csrf
                <button class="px-4 py-2 bg-gray-800 hover:bg-gray-900 text-white rounded-xl text-sm">
                    Clôturer
                </button>
            </form>
            @endif
        </div>
    </div>

    {{-- Clinical info --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
        <div>
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Motif</h3>
            <p class="text-gray-800">{{ $consultation->chief_complaint }}</p>
        </div>
        @if($consultation->history_of_illness)
        <div>
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Histoire de la maladie</h3>
            <p class="text-gray-700 text-sm">{{ $consultation->history_of_illness }}</p>
        </div>
        @endif
        @if($consultation->physical_examination)
        <div>
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Examen physique</h3>
            <p class="text-gray-700 text-sm">{{ $consultation->physical_examination }}</p>
        </div>
        @endif
    </div>

    {{-- Diagnoses --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-700">Diagnostics</h3>
        </div>
        @forelse($consultation->diagnoses as $diag)
        <div class="border border-gray-100 rounded-xl p-3 mb-2 flex items-center justify-between">
            <div>
                <span class="text-sm text-gray-800">{{ $diag->description }}</span>
                @if($diag->icd10_code)
                <span class="text-xs text-gray-400 ml-2 font-mono">{{ $diag->icd10_code }}</span>
                @endif
            </div>
            <span class="text-xs px-2 py-1 bg-gray-100 rounded-full capitalize">{{ $diag->type }}</span>
        </div>
        @empty
        <p class="text-gray-400 text-sm">Aucun diagnostic enregistré.</p>
        @endforelse

        @if($consultation->isOpen())
        <form method="POST" action="{{ route('consultations.diagnoses.store', $consultation) }}" class="mt-3 flex gap-2">
            @csrf
            <input type="text" name="description" placeholder="Ajouter un diagnostic..." required
                   class="flex-1 border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            <select name="type" class="border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <option value="primary">Principal</option>
                <option value="secondary">Secondaire</option>
                <option value="differential">Différentiel</option>
            </select>
            <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm">Ajouter</button>
        </form>
        @endif
    </div>

    {{-- Prescriptions --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-700">Ordonnances</h3>
            @if($consultation->isOpen())
            <a href="{{ route('consultations.prescriptions.create', $consultation) }}"
               class="text-sm text-blue-600 hover:text-blue-800">+ Nouvelle ordonnance</a>
            @endif
        </div>
        @forelse($consultation->prescriptions as $rx)
        <div class="border border-gray-100 rounded-xl p-4 mb-2">
            <div class="flex items-center justify-between mb-2">
                <span class="font-mono text-sm text-gray-600">{{ $rx->prescription_number }}</span>
                <span class="text-xs px-2 py-1 rounded-full
                    {{ $rx->status === 'dispensed' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                    {{ ucfirst($rx->status) }}
                </span>
            </div>
            @foreach($rx->items as $item)
            <div class="text-sm text-gray-700">• {{ $item->medicine_name }} - {{ $item->dosage }} - {{ $item->frequency }}</div>
            @endforeach
        </div>
        @empty
        <p class="text-gray-400 text-sm">Aucune ordonnance.</p>
        @endforelse
    </div>

    {{-- Lab requests --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-700">Analyses de laboratoire</h3>
            @if($consultation->isOpen())
            <a href="{{ route('lab-requests.create', ['consultation_id' => $consultation->id]) }}"
               class="text-sm text-blue-600 hover:text-blue-800">+ Demander une analyse</a>
            @endif
        </div>
        @forelse($consultation->labRequests as $lr)
        <div class="border border-gray-100 rounded-xl p-4 mb-2">
            <div class="flex items-center justify-between mb-2">
                <span class="font-mono text-sm text-gray-600">{{ $lr->request_number }}</span>
                <span class="text-xs px-2 py-1 rounded-full
                    {{ $lr->isCompleted() ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700' }}">
                    {{ ucfirst($lr->status) }}
                </span>
            </div>
            @foreach($lr->items as $item)
            <div class="text-sm text-gray-700 flex items-center justify-between">
                <span>{{ $item->labExam->name }}</span>
                @if($item->result)
                <span class="font-medium {{ $item->result->isCritical() ? 'text-red-600' : 'text-gray-700' }}">
                    {{ $item->result->result_value }} {{ $item->result->unit }}
                </span>
                @endif
            </div>
            @endforeach
        </div>
        @empty
        <p class="text-gray-400 text-sm">Aucune demande d'analyse.</p>
        @endforelse
    </div>

</div>
@endsection
