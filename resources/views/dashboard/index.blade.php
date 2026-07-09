@extends('layouts.app')
@section('title', 'Tableau de bord')
@section('page-title', 'Tableau de bord')

@section('content')
<div class="space-y-6">

    {{-- Greeting --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">
                Bonjour, {{ auth()->user()->first_name }} 👋
            </h2>
            <p class="text-gray-500 text-sm mt-1">{{ now()->isoFormat('dddd D MMMM YYYY') }}</p>
        </div>
        <span class="px-3 py-1 bg-blue-100 text-blue-700 text-sm font-medium rounded-full capitalize">
            {{ auth()->user()->getRoleNames()->first() }}
        </span>
    </div>

    @hasanyrole('administrator|director')
    {{-- Admin stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">

        @php
            $cards = [
                ['label' => 'Patients actifs',    'value' => $stats['total_patients'],     'icon' => 'fa-user-injured',        'color' => 'blue'],
                ['label' => 'RDV aujourd\'hui',   'value' => $stats['today_appointments'], 'icon' => 'fa-calendar-check',      'color' => 'green'],
                ['label' => 'Recette du jour',    'value' => number_format($stats['today_revenue'], 0, ',', ' ') . ' XAF', 'icon' => 'fa-money-bill-wave', 'color' => 'emerald'],
                ['label' => 'Stock bas',          'value' => $stats['low_stock_count'],    'icon' => 'fa-pills',               'color' => 'amber'],
                ['label' => 'Factures impayées',  'value' => $stats['overdue_invoices'],   'icon' => 'fa-file-invoice-dollar', 'color' => 'red'],
                ['label' => 'Recette mensuelle',  'value' => number_format($stats['monthly_revenue'], 0, ',', ' ') . ' XAF', 'icon' => 'fa-chart-line', 'color' => 'violet'],
            ];
            $colorMap = [
                'blue'    => 'bg-blue-50 text-blue-600',
                'green'   => 'bg-green-50 text-green-600',
                'emerald' => 'bg-emerald-50 text-emerald-600',
                'amber'   => 'bg-amber-50 text-amber-600',
                'red'     => 'bg-red-50 text-red-600',
                'violet'  => 'bg-violet-50 text-violet-600',
            ];
        @endphp

        @foreach($cards as $card)
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex flex-col gap-3">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center {{ $colorMap[$card['color']] }}">
                <i class="fa-solid {{ $card['icon'] }}"></i>
            </div>
            <div>
                <div class="text-2xl font-bold text-gray-800">{{ $card['value'] }}</div>
                <div class="text-xs text-gray-500 mt-0.5">{{ $card['label'] }}</div>
            </div>
        </div>
        @endforeach

    </div>
    @endhasanyrole

    @hasanyrole('general_practitioner|specialist')
    {{-- Doctor stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <div class="text-3xl font-bold text-blue-600">{{ $stats['today_appointments'] }}</div>
            <div class="text-sm text-gray-500 mt-1">RDV aujourd'hui</div>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <div class="text-3xl font-bold text-orange-500">{{ $stats['open_consultations'] }}</div>
            <div class="text-sm text-gray-500 mt-1">Consultations ouvertes</div>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <div class="text-3xl font-bold text-purple-500">{{ $stats['pending_lab_results'] }}</div>
            <div class="text-sm text-gray-500 mt-1">Résultats labo en attente</div>
        </div>
    </div>

    {{-- Upcoming appointments --}}
    @if(!empty($stats['upcoming_appointments']) && $stats['upcoming_appointments']->isNotEmpty())
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
        <div class="px-6 py-4 border-b border-gray-100 font-semibold text-gray-700">
            Prochains rendez-vous
        </div>
        <div class="divide-y divide-gray-50">
            @foreach($stats['upcoming_appointments'] as $appt)
            <div class="px-6 py-4 flex items-center justify-between">
                <div>
                    <div class="font-medium text-gray-800">{{ $appt->patient->getFullName() }}</div>
                    <div class="text-sm text-gray-500">{{ $appt->appointment_date->format('d/m/Y') }} à {{ $appt->appointment_time }}</div>
                </div>
                <a href="{{ route('patients.show', $appt->patient_id) }}"
                   class="text-blue-600 hover:text-blue-800 text-sm">
                    Voir le dossier →
                </a>
            </div>
            @endforeach
        </div>
    </div>
    @endif
    @endhasanyrole

    @hasanyrole('pharmacist')
    {{-- Pharmacist stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <div class="text-3xl font-bold text-amber-500">{{ $stats['pending_prescriptions'] }}</div>
            <div class="text-sm text-gray-500 mt-1">Ordonnances en attente</div>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <div class="text-3xl font-bold text-blue-600">{{ $stats['today_dispensations'] }}</div>
            <div class="text-sm text-gray-500 mt-1">Dispensations du jour</div>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <div class="text-3xl font-bold text-red-500">{{ $stats['low_stock_medicines']->count() }}</div>
            <div class="text-sm text-gray-500 mt-1">Médicaments en stock bas</div>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <div class="text-3xl font-bold text-purple-500">{{ $stats['pending_orders'] }}</div>
            <div class="text-sm text-gray-500 mt-1">Commandes en attente</div>
        </div>
    </div>

    @if($stats['low_stock_medicines']->isNotEmpty())
    <div class="bg-white rounded-2xl shadow-sm border border-red-100">
        <div class="px-6 py-4 border-b border-red-100 font-semibold text-red-700 flex items-center gap-2">
            <i class="fa-solid fa-triangle-exclamation"></i> Alertes stock bas
        </div>
        <div class="divide-y divide-gray-50">
            @foreach($stats['low_stock_medicines'] as $stock)
            <div class="px-6 py-3 flex items-center justify-between text-sm">
                <span class="text-gray-800">{{ $stock->medicine->name }}</span>
                <span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs font-medium">
                    {{ $stock->quantity_available }} / {{ $stock->minimum_quantity }}
                </span>
            </div>
            @endforeach
        </div>
    </div>
    @endif
    @endhasanyrole

</div>
@endsection
