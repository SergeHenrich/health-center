@extends('layouts.app')
@section('title', 'Patients')
@section('page-title', 'Gestion des patients')

@section('content')
<div class="space-y-4">

    {{-- Toolbar --}}
    <div class="flex flex-wrap gap-3 items-center justify-between">
        <form method="GET" action="{{ route('patients.index') }}" class="flex gap-2">
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                <input type="text" name="q" value="{{ request('q') }}"
                       placeholder="Rechercher un patient..."
                       class="pl-9 pr-4 py-2 border border-gray-300 rounded-xl text-sm w-64
                              focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit"
                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-xl text-sm text-gray-700">
                Rechercher
            </button>
        </form>

        @hasanyrole('administrator|receptionist')
        <a href="{{ route('patients.create') }}"
           class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded-xl text-sm text-white">
            <i class="fa-solid fa-user-plus"></i> Nouveau patient
        </a>
        @endhasanyrole
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Code</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Nom complet</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Âge / Sexe</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Téléphone</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Groupe sanguin</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Statut</th>
                    <th class="px-6 py-4"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($patients as $patient)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 font-mono text-xs text-gray-500">{{ $patient->patient_code }}</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold">
                                {{ strtoupper(substr($patient->first_name,0,1).substr($patient->last_name,0,1)) }}
                            </div>
                            <span class="font-medium text-gray-800">{{ $patient->getFullName() }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-gray-600">
                        {{ $patient->getAge() }} ans ·
                        {{ $patient->gender === 'male' ? 'M' : ($patient->gender === 'female' ? 'F' : 'A') }}
                    </td>
                    <td class="px-6 py-4 text-gray-600">{{ $patient->phone ?? '-' }}</td>
                    <td class="px-6 py-4">
                        @if($patient->blood_type)
                        <span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs">
                            {{ $patient->blood_type }}
                        </span>
                        @else
                        <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($patient->isHospitalized())
                        <span class="px-2 py-1 bg-orange-100 text-orange-700 rounded-full text-xs">Hospitalisé</span>
                        @else
                        <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs">Actif</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('patients.show', $patient) }}"
                           class="text-blue-600 hover:text-blue-800 text-xs font-medium">
                            Voir →
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                        <i class="fa-solid fa-user-xmark text-3xl mb-3 block"></i>
                        Aucun patient trouvé.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($patients->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $patients->withQueryString()->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
