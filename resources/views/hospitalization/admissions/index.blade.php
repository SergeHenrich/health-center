@extends('layouts.app')
@section('title', 'Admissions')
@section('page-title', 'Hospitalisation — Admissions')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">{{ $hospitalizations->total() }} admission(s) active(s)</p>
        <a href="{{ route('hospitalizations.create') }}"
           class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded-xl text-sm text-white">
            <i class="fa-solid fa-plus"></i> Nouvelle admission
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">N° Admission</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Patient</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Chambre / Lit</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Médecin</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Date admission</th>
                    <th class="text-center px-6 py-4 font-semibold text-gray-600">Statut</th>
                    <th class="text-center px-6 py-4 font-semibold text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($hospitalizations as $hosp)
                <tr class="hover:bg-gray-50/50">
                    <td class="px-6 py-4 font-mono text-gray-800">{{ $hosp->admission_number }}</td>
                    <td class="px-6 py-4 text-gray-700">{{ $hosp->patient->getFullName() }}</td>
                    <td class="px-6 py-4 text-gray-600">
                        {{ $hosp->room->room_number }} / {{ $hosp->bed->bed_number }}
                    </td>
                    <td class="px-6 py-4 text-gray-600">Dr. {{ $hosp->admittingDoctor->last_name }}</td>
                    <td class="px-6 py-4 text-gray-500">{{ $hosp->admission_date->format('d/m/Y H:i') }}</td>
                    <td class="px-6 py-4 text-center">
                        @php $sColors = ['admitted' => 'bg-blue-100 text-blue-700', 'in_care' => 'bg-purple-100 text-purple-700', 'discharged' => 'bg-green-100 text-green-700']; @endphp
                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $sColors[$hosp->status] ?? 'bg-gray-100' }}">
                            {{ ucfirst(str_replace('_', ' ', $hosp->status)) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <a href="{{ route('hospitalizations.show', $hosp) }}" class="text-blue-600 hover:text-blue-800">
                            <i class="fa-solid fa-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                        <i class="fa-solid fa-bed-pulse text-3xl mb-3 block"></i>
                        Aucune admission active.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $hospitalizations->links() }}
</div>
@endsection
