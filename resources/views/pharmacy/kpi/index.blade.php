@extends('layouts.app')
@section('title', 'Tableau de bord Pharmacie')
@section('page-title', 'Tableau de bord Pharmacie')

@section('content')
<div class="space-y-6">

    {{-- Dispensation KPIs --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs text-gray-400 uppercase tracking-wide">Dispensations (mois)</div>
            <div class="text-3xl font-bold text-blue-600 mt-1">{{ $data['dispensation']['monthly'] }}</div>
            <div class="text-xs text-gray-400 mt-1">{{ $data['dispensation']['total'] }} au total</div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs text-gray-400 uppercase tracking-wide">Ordonnances en attente</div>
            <div class="text-3xl font-bold text-amber-600 mt-1">{{ $data['dispensation']['pending'] }}</div>
            <div class="text-xs text-gray-400 mt-1">Moy. {{ $data['dispensation']['daily_avg'] }}/jour</div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs text-gray-400 uppercase tracking-wide">Santé du stock</div>
            <div class="text-3xl font-bold text-green-600 mt-1">{{ $data['stock']['stock_health'] }}%</div>
            <div class="text-xs text-gray-400 mt-1">{{ $data['stock']['low_stock'] }} ruptures, {{ $data['stock']['out_of_stock'] }} épuisés</div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs text-gray-400 uppercase tracking-wide">Valeur du stock</div>
            <div class="text-3xl font-bold text-indigo-600 mt-1">{{ number_format($data['stock']['stock_value'], 0, ',', ' ') }} F</div>
            <div class="text-xs text-gray-400 mt-1">{{ $data['stock']['warehouse_items'] }} articles en dépôts</div>
        </div>
    </div>

    {{-- Quality + Formulary + Narcotic + Events KPIs --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs text-gray-400 uppercase tracking-wide">Validations pharmaceutiques</div>
            <div class="text-3xl font-bold text-cyan-600 mt-1">{{ $data['quality']['validation_rate'] }}%</div>
            <div class="text-xs text-gray-400 mt-1">{{ $data['quality']['approved'] }}/{{ $data['quality']['total_validations'] }} approuvées</div>
            <div class="text-xs text-gray-400">Interventions : {{ $data['quality']['interventions'] }}</div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs text-gray-400 uppercase tracking-wide">Couverture livret</div>
            <div class="text-3xl font-bold text-emerald-600 mt-1">{{ $data['formulary']['coverage_rate'] }}%</div>
            <div class="text-xs text-gray-400 mt-1">{{ $data['formulary']['on_formulary'] }} inscrits / {{ $data['formulary']['off_formulary'] }} hors livret</div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs text-gray-400 uppercase tracking-wide">Stupéfiants</div>
            <div class="text-3xl font-bold text-rose-600 mt-1">{{ $data['narcotic']['narcotic_medicines'] }}</div>
            <div class="text-xs text-gray-400 mt-1">{{ $data['narcotic']['total_entries'] }} mouvements</div>
            <div class="text-xs text-gray-400">Solde total : {{ $data['narcotic']['total_balance'] }}</div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs text-gray-400 uppercase tracking-wide">Événements</div>
            <div class="text-3xl font-bold text-red-600 mt-1">{{ $data['events']['open'] }}</div>
            <div class="text-xs text-gray-400 mt-1">{{ $data['events']['total'] }} signalés, {{ $data['events']['critical'] }} critiques</div>
        </div>
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-800 mb-4">Dispensations (6 mois)</h3>
            <div class="relative" style="height: 260px;">
                <canvas id="dispensationsChart"></canvas>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-800 mb-4">Événements signalés (6 mois)</h3>
            <div class="relative" style="height: 260px;">
                <canvas id="eventsChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Events by type --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        @foreach(['medication_error' => 'Erreurs médicamenteuses', 'adverse_drug_reaction' => 'Effets indésirables', 'near_miss' => 'Presque-accidents', 'quality_incident' => 'Incidents qualité'] as $type => $label)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 text-center">
            <div class="text-2xl font-bold text-gray-700">{{ $data['events']['by_type'][$type] ?? 0 }}</div>
            <div class="text-xs text-gray-500 mt-1">{{ $label }}</div>
        </div>
        @endforeach
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const dispensations = @json($data['charts']['dispensations']);
    const events = @json($data['charts']['events']);

    new Chart(document.getElementById('dispensationsChart'), {
        type: 'bar',
        data: {
            labels: Object.keys(dispensations),
            datasets: [{
                label: 'Dispensations',
                data: Object.values(dispensations),
                backgroundColor: 'rgba(59, 130, 246, 0.5)',
                borderColor: 'rgba(59, 130, 246, 1)',
                borderWidth: 1
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });

    new Chart(document.getElementById('eventsChart'), {
        type: 'line',
        data: {
            labels: Object.keys(events),
            datasets: [{
                label: 'Événements',
                data: Object.values(events),
                borderColor: 'rgba(239, 68, 68, 1)',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                fill: true,
                tension: 0.3
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });
});
</script>
@endpush
