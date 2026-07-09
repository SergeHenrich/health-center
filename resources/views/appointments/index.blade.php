@extends('layouts.app')
@section('title', 'Rendez-vous')
@section('page-title', 'Gestion des rendez-vous')

@section('content')
<div class="space-y-4">

    <div class="flex flex-wrap gap-3 items-center justify-between">
        <form method="GET" class="flex gap-2">
            <input type="date" name="date" value="{{ request('date') }}"
                   class="border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            <select name="status" class="border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <option value="">Tous statuts</option>
                @foreach(['scheduled'=>'Planifié','confirmed'=>'Confirmé','arrived'=>'Arrivé','in_progress'=>'En cours','done'=>'Terminé','cancelled'=>'Annulé','no_show'=>'Absent'] as $val => $label)
                <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-xl text-sm">Filtrer</button>
        </form>

        <a href="{{ route('appointments.create') }}"
           class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded-xl text-sm text-white">
            <i class="fa-solid fa-calendar-plus"></i> Nouveau RDV
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Date / Heure</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Patient</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Médecin</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Type</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Statut</th>
                    <th class="px-6 py-4"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($appointments as $appt)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4">
                        <div class="font-medium text-gray-800">{{ $appt->appointment_date->format('d/m/Y') }}</div>
                        <div class="text-xs text-gray-400">{{ $appt->appointment_time }}</div>
                    </td>
                    <td class="px-6 py-4">{{ $appt->patient->getFullName() }}</td>
                    <td class="px-6 py-4">Dr. {{ $appt->doctor->getFullName() }}</td>
                    <td class="px-6 py-4 capitalize text-gray-600">{{ $appt->type }}</td>
                    <td class="px-6 py-4">
                        @php
                        $statusColors = [
                            'scheduled' => 'bg-blue-100 text-blue-700', 'confirmed' => 'bg-indigo-100 text-indigo-700',
                            'arrived' => 'bg-purple-100 text-purple-700', 'in_progress' => 'bg-orange-100 text-orange-700',
                            'done' => 'bg-green-100 text-green-700', 'cancelled' => 'bg-red-100 text-red-700',
                            'no_show' => 'bg-gray-100 text-gray-600',
                        ];
                        @endphp
                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $statusColors[$appt->status] ?? 'bg-gray-100' }}">
                            {{ ucfirst($appt->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end gap-2">
                            @if($appt->status === 'scheduled')
                            <form method="POST" action="{{ route('appointments.update', $appt) }}" class="inline">
                                @csrf @method('PUT')
                                <input type="hidden" name="status" value="confirmed">
                                <input type="hidden" name="appointment_date" value="{{ $appt->appointment_date->format('Y-m-d') }}">
                                <input type="hidden" name="appointment_time" value="{{ $appt->appointment_time }}">
                                <input type="hidden" name="doctor_id" value="{{ $appt->doctor_id }}">
                                <button class="text-green-600 hover:text-green-800 text-xs">Confirmer</button>
                            </form>
                            @endif
                            <a href="{{ route('patients.show', $appt->patient_id) }}" class="text-blue-600 hover:text-blue-800 text-xs">
                                Dossier →
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                        <i class="fa-solid fa-calendar-xmark text-3xl mb-3 block"></i>
                        Aucun rendez-vous trouvé.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($appointments->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $appointments->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
