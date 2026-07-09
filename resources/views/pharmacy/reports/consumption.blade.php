@extends('layouts.app')
@section('title', 'Rapport de consommation')
@section('page-title', 'Rapport de consommation')

@section('content')
<div class="space-y-6">

    {{-- Export toolbar --}}
    <div class="flex justify-end gap-2">
        <a href="{{ route('reports.consumption.pdf') }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-500 text-white text-xs font-medium rounded-xl hover:bg-red-600 transition">
            <i class="fa-solid fa-file-pdf"></i> PDF
        </a>
        <a href="{{ route('reports.consumption.excel') }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-500 text-white text-xs font-medium rounded-xl hover:bg-emerald-600 transition">
            <i class="fa-solid fa-file-excel"></i> Excel
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <form method="GET" class="flex gap-4 items-end">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Période</label>
                <select name="period" onchange="this.form.submit()" class="border border-gray-300 rounded-xl text-sm px-3 py-2">
                    <option value="week" {{ $period === 'week' ? 'selected' : '' }}>Semaine</option>
                    <option value="month" {{ $period === 'month' ? 'selected' : '' }}>Mois</option>
                    <option value="year" {{ $period === 'year' ? 'selected' : '' }}>Année</option>
                </select>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h3 class="font-semibold text-gray-800 mb-4">Évolution de la consommation</h3>
        <div style="height: 300px;">
            <canvas id="consumptionChart"></canvas>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('consumptionChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($chartData['labels']),
            datasets: [{
                label: 'Médicaments dispensés',
                data: @json($chartData['dispensed'] ?? $chartData['data'] ?? []),
                backgroundColor: 'rgba(59, 130, 246, 0.7)',
                borderColor: 'rgb(59, 130, 246)',
                borderWidth: 1,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
        }
    });
});
</script>
@endpush
