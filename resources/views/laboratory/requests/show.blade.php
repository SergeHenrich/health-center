@extends('layouts.app')
@section('title', 'Demande ' . $labRequest->request_number)
@section('page-title', 'Détail demande d\'analyse')

@section('content')
<div class="space-y-6">

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-start justify-between">
            <div>
                <h3 class="text-lg font-bold text-gray-800 font-mono">{{ $labRequest->request_number }}</h3>
                <p class="text-sm text-gray-500 mt-1">Patient : {{ $labRequest->patient->getFullName() }} · Dr. {{ $labRequest->doctor->last_name }}</p>
                <p class="text-sm text-gray-500">Demandé le {{ $labRequest->requested_at->format('d/m/Y à H:i') }}</p>
            </div>
            <div class="flex gap-2">
                @php $urgencyColors = ['normal' => 'bg-gray-100 text-gray-600', 'urgent' => 'bg-orange-100 text-orange-700', 'critical' => 'bg-red-100 text-red-700']; @endphp
                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $urgencyColors[$labRequest->urgency] ?? '' }}">{{ ucfirst($labRequest->urgency) }}</span>
                @php $statusColors = ['pending' => 'bg-yellow-100 text-yellow-700', 'in_progress' => 'bg-blue-100 text-blue-700', 'completed' => 'bg-green-100 text-green-700']; @endphp
                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusColors[$labRequest->status] ?? '' }}">{{ ucfirst(str_replace('_', ' ', $labRequest->status)) }}</span>
            </div>
        </div>
        @if($labRequest->clinical_info)
        <div class="mt-4 bg-blue-50 rounded-xl p-4 text-sm text-blue-800">
            <span class="font-semibold">Info clinique :</span> {{ $labRequest->clinical_info }}
        </div>
        @endif
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h4 class="font-semibold text-gray-800">Examens demandés</h4>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-6 py-3 font-semibold text-gray-600">Examen</th>
                    <th class="text-left px-6 py-3 font-semibold text-gray-600">Catégorie</th>
                    <th class="text-center px-6 py-3 font-semibold text-gray-600">Statut</th>
                    <th class="text-left px-6 py-3 font-semibold text-gray-600">Résultat</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($labRequest->items as $item)
                <tr class="hover:bg-gray-50/50">
                    <td class="px-6 py-4 font-medium text-gray-800">{{ $item->labExam->name }}</td>
                    <td class="px-6 py-4 text-gray-500">{{ $item->labExam->category }}</td>
                    <td class="px-6 py-4 text-center">
                        @php $sColors = ['pending' => 'bg-yellow-100 text-yellow-700', 'done' => 'bg-green-100 text-green-700']; @endphp
                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $sColors[$item->status] ?? 'bg-gray-100' }}">{{ ucfirst($item->status) }}</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-700">
                        @if($item->result)
                            <span class="font-medium">{{ $item->result->result_value }}</span>
                            @if($item->result->unit) <span class="text-gray-400">{{ $item->result->unit }}</span> @endif
                            <span class="ml-2 text-xs {{ $item->result->is_validated ? 'text-green-600' : 'text-orange-500' }}">
                                {{ $item->result->is_validated ? '✓ Validé' : 'En attente validation' }}
                            </span>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-8 text-center text-gray-400">Aucun examen.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
