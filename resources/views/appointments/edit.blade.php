@extends('layouts.app')
@section('title', 'Modifier RDV')
@section('page-title', 'Modifier le rendez-vous')

@section('content')
<div class="max-w-2xl">

    <form method="POST" action="{{ route('appointments.update', $appointment) }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date *</label>
                <input type="date" name="appointment_date" value="{{ $appointment->appointment_date->format('Y-m-d') }}" required
                       class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Heure *</label>
                <input type="time" name="appointment_time" value="{{ $appointment->appointment_time }}" required
                       class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Durée (minutes)</label>
                <input type="number" name="duration_minutes" value="{{ $appointment->duration_minutes }}" min="10" max="180"
                       class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Statut *</label>
                <select name="status" required class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    @foreach(['scheduled'=>'Planifié','confirmed'=>'Confirmé','arrived'=>'Arrivé','in_progress'=>'En cours','done'=>'Terminé','cancelled'=>'Annulé','no_show'=>'Absent'] as $val => $label)
                    <option value="{{ $val }}" {{ $appointment->status === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Motif</label>
            <textarea name="reason" rows="2" class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">{{ $appointment->reason }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea name="notes" rows="2" class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">{{ $appointment->notes }}</textarea>
        </div>

        <div class="flex gap-3 pt-2">
            <a href="{{ route('appointments.show', $appointment) }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 rounded-xl text-sm text-gray-700">Annuler</a>
            <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 rounded-xl text-sm text-white font-medium">
                <i class="fa-solid fa-save mr-1"></i> Enregistrer
            </button>
        </div>
    </form>
</div>
@endsection
