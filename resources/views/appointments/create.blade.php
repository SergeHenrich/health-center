@extends('layouts.app')
@section('title', 'Nouveau rendez-vous')
@section('page-title', 'Planifier un rendez-vous')

@section('content')
<form method="POST" action="{{ route('appointments.store') }}" class="max-w-2xl mx-auto">
    @csrf
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Patient *</label>
            <select name="patient_id" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <option value="">Sélectionner un patient...</option>
                @foreach($patients as $p)
                <option value="{{ $p->id }}">{{ $p->first_name }} {{ $p->last_name }} ({{ $p->patient_code }})</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Médecin *</label>
            <select name="doctor_id" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <option value="">Sélectionner un médecin...</option>
                @foreach($doctors as $d)
                <option value="{{ $d->id }}">Dr. {{ $d->first_name }} {{ $d->last_name }}</option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date *</label>
                <input type="date" name="appointment_date" min="{{ date('Y-m-d') }}" required
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Heure *</label>
                <input type="time" name="appointment_time" required
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Type de rendez-vous *</label>
            <select name="type" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <option value="consultation">Consultation</option>
                <option value="followup">Suivi</option>
                <option value="specialist">Spécialiste</option>
                <option value="lab">Laboratoire</option>
                <option value="emergency">Urgence</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Motif</label>
            <textarea name="reason" rows="3"
                      class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
                      placeholder="Motif du rendez-vous..."></textarea>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('appointments.index') }}" class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 rounded-xl text-sm text-gray-700">
                Annuler
            </a>
            <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 rounded-xl text-sm text-white font-medium">
                Planifier le rendez-vous
            </button>
        </div>
    </div>
</form>
@endsection
