@extends('layouts.app')
@section('title', 'Demandes d\'analyses')
@section('page-title', 'Laboratoire — Demandes')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">{{ $requests->total() }} demande(s) au total</p>
        <a href="{{ route('lab-requests.create') }}"
           class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded-xl text-sm text-white">
            <i class="fa-solid fa-plus"></i> Nouvelle demande
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">N° Demande</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Patient</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Médecin</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Date</th>
                    <th class="text-center px-6 py-4 font-semibold text-gray-600">Urgence</th>
                    <th class="text-center px-6 py-4 font-semibold text-gray-600">Statut</th>
                    <th class="text-center px-6 py-4 font-semibold text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($requests as $req)
                <tr class="hover:bg-gray-50/50">
                    <td class="px-6 py-4 font-mono text-gray-800">{{ $req->request_number }}</td>
                    <td class="px-6 py-4 text-gray-700">{{ $req->patient->getFullName() }}</td>
                    <td class="px-6 py-4 text-gray-600">Dr. {{ $req->doctor->last_name }}</td>
                    <td class="px-6 py-4 text-gray-500">{{ $req->requested_at->format('d/m/Y H:i') }}</td>
                    <td class="px-6 py-4 text-center">
                        @php $urgencyColors = ['normal' => 'bg-gray-100 text-gray-600', 'urgent' => 'bg-orange-100 text-orange-700', 'critical' => 'bg-red-100 text-red-700']; @endphp
                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $urgencyColors[$req->urgency] ?? '' }}">
                            {{ ucfirst($req->urgency) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        @php $statusColors = ['pending' => 'bg-yellow-100 text-yellow-700', 'in_progress' => 'bg-blue-100 text-blue-700', 'completed' => 'bg-green-100 text-green-700']; @endphp
                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $statusColors[$req->status] ?? 'bg-gray-100' }}">
                            {{ ucfirst(str_replace('_', ' ', $req->status)) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <a href="{{ route('lab-requests.show', $req) }}" class="text-blue-600 hover:text-blue-800">
                            <i class="fa-solid fa-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                        <i class="fa-solid fa-flask text-3xl mb-3 block"></i>
                        Aucune demande d'analyse.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $requests->links() }}
</div>
@endsection
