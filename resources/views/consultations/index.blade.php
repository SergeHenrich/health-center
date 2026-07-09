@extends('layouts.app')
@section('title', 'Consultations')
@section('page-title', 'Mes consultations')

@section('content')
<div class="space-y-4">

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Date</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Patient</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Motif</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Statut</th>
                    <th class="px-6 py-4"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($consultations as $c)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 text-gray-600">{{ $c->consultation_date->format('d/m/Y') }}</td>
                    <td class="px-6 py-4 font-medium text-gray-800">{{ $c->medicalRecord->patient->getFullName() }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ \Illuminate\Support\Str::limit($c->chief_complaint, 50) }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 rounded-full text-xs font-medium
                            {{ $c->status === 'closed' ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700' }}">
                            {{ ucfirst($c->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('consultations.show', $c) }}" class="text-blue-600 hover:text-blue-800 text-xs font-medium">
                            Voir →
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                        <i class="fa-solid fa-stethoscope text-3xl mb-3 block"></i>
                        Aucune consultation.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($consultations->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $consultations->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
