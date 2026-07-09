@extends('layouts.app')
@section('title', 'File d\'attente')
@section('page-title', 'File d\'attente')

@section('content')
<div x-data="{ showAdd: false }" class="space-y-6">

    <div class="flex justify-end">
        <button @click="showAdd = true" class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded-xl text-sm text-white">
            <i class="fa-solid fa-plus"></i> Ajouter à la file
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach(['consultation' => 'Consultation', 'lab' => 'Laboratoire', 'pharmacy' => 'Pharmacie', 'cashier' => 'Caisse'] as $service => $label)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
            <div class="px-5 py-4 border-b border-gray-100 font-semibold text-gray-700 flex items-center justify-between">
                {{ $label }}
                <span class="text-xs bg-gray-100 px-2 py-1 rounded-full">
                    {{ ($queues[$service] ?? collect())->where('status', 'waiting')->count() }} en attente
                </span>
            </div>
            <div class="divide-y divide-gray-50 max-h-96 overflow-y-auto">
                @forelse(($queues[$service] ?? collect())->sortBy('queue_number') as $q)
                <div class="px-5 py-3 flex items-center justify-between text-sm">
                    <div>
                        <span class="font-bold text-gray-700">#{{ $q->queue_number }}</span>
                        <span class="ml-2 text-gray-600">{{ $q->patient->getFullName() }}</span>
                        @if($q->status === 'waiting')
                        <div class="text-xs text-gray-400 mt-0.5">{{ $q->getWaitingTimeInMinutes() }} min d'attente</div>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        @if($q->status === 'waiting')
                        <form method="POST" action="{{ route('queue.call', $q) }}">
                            @csrf
                            <button class="text-blue-600 hover:text-blue-800 text-xs font-medium">Appeler</button>
                        </form>
                        @elseif($q->status === 'called')
                        <span class="text-xs px-2 py-1 bg-orange-100 text-orange-700 rounded-full">Appelé</span>
                        <form method="POST" action="{{ route('queue.complete', $q) }}">
                            @csrf
                            <button class="text-green-600 hover:text-green-800 text-xs font-medium">Terminer</button>
                        </form>
                        @else
                        <span class="text-xs px-2 py-1 bg-green-100 text-green-700 rounded-full">Servi</span>
                        @endif
                    </div>
                </div>
                @empty
                <div class="px-5 py-8 text-center text-gray-400 text-sm">Aucun patient</div>
                @endforelse
            </div>
        </div>
        @endforeach
    </div>

    {{-- Add to queue modal --}}
    <div x-show="showAdd" x-cloak class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6" @click.away="showAdd = false">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Ajouter à la file d'attente</h3>
            <form method="POST" action="{{ route('queue.index') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Patient</label>
                    <select name="patient_id" required class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        @foreach($patients as $p)
                        <option value="{{ $p->id }}">{{ $p->first_name }} {{ $p->last_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Service</label>
                    <select name="service" required class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="consultation">Consultation</option>
                        <option value="lab">Laboratoire</option>
                        <option value="pharmacy">Pharmacie</option>
                        <option value="cashier">Caisse</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Médecin (optionnel)</label>
                    <select name="doctor_id" class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="">-</option>
                        @foreach($doctors as $d)
                        <option value="{{ $d->id }}">Dr. {{ $d->first_name }} {{ $d->last_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" @click="showAdd = false" class="flex-1 px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-xl text-sm">
                        Annuler
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded-xl text-sm text-white font-medium">
                        Ajouter
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
