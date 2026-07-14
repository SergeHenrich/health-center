@extends('layouts.app')
@section('title', 'RDV ' . $appointment->id)
@section('page-title', 'Détail rendez-vous')

@section('content')
<div class="space-y-6">

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-start justify-between">
            <div>
                <h3 class="text-lg font-bold text-gray-800">Rendez-vous #{{ $appointment->id }}</h3>
                <p class="text-sm text-gray-500 mt-1">
                    {{ $appointment->appointment_date->format('d/m/Y') }} à {{ $appointment->appointment_time }}
                    · Durée : {{ $appointment->duration_minutes }} min
                </p>
            </div>
            @php $statusColors = ['scheduled' => 'bg-blue-100 text-blue-700', 'confirmed' => 'bg-indigo-100 text-indigo-700', 'arrived' => 'bg-purple-100 text-purple-700', 'in_progress' => 'bg-orange-100 text-orange-700', 'done' => 'bg-green-100 text-green-700', 'cancelled' => 'bg-red-100 text-red-700', 'no_show' => 'bg-gray-100 text-gray-600']; @endphp
            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusColors[$appointment->status] ?? '' }}">{{ ucfirst(str_replace('_', ' ', $appointment->status)) }}</span>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs text-gray-400 uppercase tracking-wide mb-1">Patient</div>
            <a href="{{ route('patients.show', $appointment->patient) }}" class="font-semibold text-blue-600 hover:text-blue-800">
                {{ $appointment->patient->getFullName() }}
            </a>
            <div class="text-sm text-gray-500">{{ $appointment->patient->phone ?? '' }}</div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs text-gray-400 uppercase tracking-wide mb-1">Médecin</div>
            <div class="font-semibold text-gray-800">Dr. {{ $appointment->doctor->last_name }}</div>
            <div class="text-sm text-gray-500 capitalize">{{ str_replace('_', ' ', $appointment->type) }}</div>
        </div>
    </div>

    @if($appointment->reason)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h4 class="font-semibold text-gray-800 mb-2">Motif</h4>
        <p class="text-sm text-gray-600">{{ $appointment->reason }}</p>
    </div>
    @endif

    @if($appointment->notes)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h4 class="font-semibold text-gray-800 mb-2">Notes</h4>
        <p class="text-sm text-gray-600">{{ $appointment->notes }}</p>
    </div>
    @endif

    <div class="flex gap-3">
        <a href="{{ route('appointments.edit', $appointment) }}"
           class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 rounded-xl text-sm text-gray-700">
            <i class="fa-solid fa-pen mr-1"></i> Modifier
        </a>
        <a href="{{ route('patients.show', $appointment->patient) }}"
           class="px-5 py-2.5 bg-blue-100 hover:bg-blue-200 rounded-xl text-sm text-blue-700">
            <i class="fa-solid fa-user mr-1"></i> Dossier patient
        </a>
    </div>
</div>
@endsection
