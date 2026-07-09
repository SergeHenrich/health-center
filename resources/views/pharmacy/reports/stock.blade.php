@extends('layouts.app')
@section('title', 'Rapport des stocks')
@section('page-title', 'Rapport des stocks')

@section('content')
<div class="space-y-6">

    {{-- Export toolbar --}}
    <div class="flex justify-end gap-2">
        <a href="{{ route('reports.stock.pdf') }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-500 text-white text-xs font-medium rounded-xl hover:bg-red-600 transition">
            <i class="fa-solid fa-file-pdf"></i> PDF
        </a>
        <a href="{{ route('reports.stock.excel') }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-500 text-white text-xs font-medium rounded-xl hover:bg-emerald-600 transition">
            <i class="fa-solid fa-file-excel"></i> Excel
        </a>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-400 uppercase tracking-wide">Total lots</p>
            <p class="text-2xl font-bold mt-1">{{ $totals['batches'] }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-400 uppercase tracking-wide">Qté disponible</p>
            <p class="text-2xl font-bold mt-1">{{ $totals['total_available'] }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-400 uppercase tracking-wide">Qté initiale</p>
            <p class="text-2xl font-bold mt-1">{{ $totals['total_initial'] }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-400 uppercase tracking-wide">Lots périmés</p>
            <p class="text-2xl font-bold mt-1 text-red-600">{{ $totals['expired_count'] }}</p>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Médicament</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">N° lot</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Péremption</th>
                    <th class="text-right px-6 py-4 font-semibold text-gray-600">Disponible</th>
                    <th class="text-right px-6 py-4 font-semibold text-gray-600">Initiale</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Statut</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($batches as $batch)
                <tr>
                    <td class="px-6 py-3">{{ $batch->medicine->name }}</td>
                    <td class="px-6 py-3 font-mono text-xs">{{ $batch->lot_number ?? '-' }}</td>
                    <td class="px-6 py-3">{{ $batch->expiry_date?->format('d/m/Y') ?? '-' }}</td>
                    <td class="px-6 py-3 text-right font-mono">{{ $batch->quantity_available }}</td>
                    <td class="px-6 py-3 text-right text-gray-400">{{ $batch->initial_quantity }}</td>
                    <td class="px-6 py-3">{{ $batch->status }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-12 text-center text-gray-400">Aucun lot.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
