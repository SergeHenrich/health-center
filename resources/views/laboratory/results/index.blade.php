@extends('layouts.app')
@section('title', 'Résultats d\'analyses')
@section('page-title', 'Laboratoire — Résultats')

@section('content')
<div class="space-y-6">

    <p class="text-sm text-gray-500">{{ $results->total() }} résultat(s) au total</p>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Demande</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Patient</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Examen</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Résultat</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Interprétation</th>
                    <th class="text-center px-6 py-4 font-semibold text-gray-600">Validé</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($results as $result)
                <tr class="hover:bg-gray-50/50">
                    <td class="px-6 py-4 font-mono text-gray-800">{{ $result->labRequest->request_number }}</td>
                    <td class="px-6 py-4 text-gray-700">{{ $result->labRequest->patient->getFullName() }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ $result->labRequestItem->labExam->name }}</td>
                    <td class="px-6 py-4 font-medium text-gray-800">
                        {{ $result->result_value }}
                        @if($result->unit) <span class="text-gray-400 text-xs">{{ $result->unit }}</span> @endif
                    </td>
                    <td class="px-6 py-4">
                        @php $iColors = ['normal' => 'bg-green-100 text-green-700', 'low' => 'bg-blue-100 text-blue-700', 'high' => 'bg-orange-100 text-orange-700', 'critical' => 'bg-red-100 text-red-700']; @endphp
                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $iColors[$result->interpretation] ?? 'bg-gray-100' }}">{{ ucfirst($result->interpretation) }}</span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if($result->is_validated)
                            <span class="text-green-600"><i class="fa-solid fa-circle-check"></i></span>
                        @else
                            <form method="POST" action="{{ route('lab-results.validate', $result) }}" class="inline">
                                @csrf
                                <button class="text-orange-500 hover:text-orange-700 text-xs font-medium">Valider</button>
                            </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                        <i class="fa-solid fa-microscope text-3xl mb-3 block"></i>
                        Aucun résultat enregistré.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $results->links() }}
</div>
@endsection
