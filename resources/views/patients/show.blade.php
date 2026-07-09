@extends('layouts.app')
@section('title', $patient->getFullName())
@section('page-title', 'Dossier patient')

@section('content')
<div x-data="{ tab: 'info' }" class="space-y-6">

    {{-- Header --}}
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-wrap gap-6 items-start justify-between">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-2xl bg-blue-600 text-white flex items-center justify-center text-2xl font-bold">
                {{ strtoupper(substr($patient->first_name, 0, 1) . substr($patient->last_name, 0, 1)) }}
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-800">{{ $patient->getFullName() }}</h2>
                <div class="flex flex-wrap gap-2 mt-1">
                    <span class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded-full font-mono">
                        {{ $patient->patient_code }}
                    </span>
                    <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full">
                        {{ $patient->getAge() }} ans · {{ $patient->gender === 'male' ? 'Masculin' : ($patient->gender === 'female' ? 'Féminin' : 'Autre') }}
                    </span>
                    @if($patient->blood_type)
                    <span class="text-xs bg-red-100 text-red-700 px-2 py-1 rounded-full">
                        Groupe {{ $patient->blood_type }}
                    </span>
                    @endif
                    @if($patient->isHospitalized())
                    <span class="text-xs bg-orange-100 text-orange-700 px-2 py-1 rounded-full font-medium">
                        ⚡ Hospitalisé
                    </span>
                    @endif
                </div>
            </div>
        </div>
        <div class="flex gap-2">
            @can('update', $patient)
            <a href="{{ route('patients.edit', $patient) }}"
               class="flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-xl text-sm text-gray-700">
                <i class="fa-solid fa-pen-to-square"></i> Modifier
            </a>
            @endcan
            @hasanyrole('general_practitioner|specialist|administrator')
            <a href="{{ route('consultations.create', ['patient_id' => $patient->id]) }}"
               class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded-xl text-sm text-white">
                <i class="fa-solid fa-stethoscope"></i> Nouvelle consultation
            </a>
            @endhasanyrole
        </div>
    </div>

    {{-- Tabs --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

        <div class="flex border-b border-gray-100 overflow-x-auto">
            @foreach([
                ['key' => 'info',     'label' => 'Informations',    'icon' => 'fa-id-card'],
                ['key' => 'consult',  'label' => 'Consultations',   'icon' => 'fa-stethoscope'],
                ['key' => 'rx',       'label' => 'Ordonnances',     'icon' => 'fa-prescription'],
                ['key' => 'lab',      'label' => 'Laboratoire',     'icon' => 'fa-flask'],
                ['key' => 'billing',  'label' => 'Facturation',     'icon' => 'fa-file-invoice-dollar'],
            ] as $t)
            <button @click="tab = '{{ $t['key'] }}'"
                    :class="tab === '{{ $t['key'] }}' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-500 hover:text-gray-700'"
                    class="flex items-center gap-2 px-6 py-4 text-sm font-medium whitespace-nowrap transition">
                <i class="fa-solid {{ $t['icon'] }}"></i>
                {{ $t['label'] }}
            </button>
            @endforeach
        </div>

        {{-- Tab: Info --}}
        <div x-show="tab === 'info'" class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-3">
                    <h3 class="font-semibold text-gray-700">Informations personnelles</h3>
                    @foreach([
                        ['label' => 'Date de naissance', 'value' => $patient->date_of_birth->format('d/m/Y')],
                        ['label' => 'Téléphone',         'value' => $patient->phone ?? '-'],
                        ['label' => 'Email',             'value' => $patient->email ?? '-'],
                        ['label' => 'Adresse',           'value' => $patient->address ?? '-'],
                        ['label' => 'Ville',             'value' => $patient->city ?? '-'],
                    ] as $row)
                    <div class="flex gap-3 text-sm">
                        <span class="w-40 text-gray-400">{{ $row['label'] }}</span>
                        <span class="text-gray-800">{{ $row['value'] }}</span>
                    </div>
                    @endforeach
                </div>
                <div class="space-y-3">
                    <h3 class="font-semibold text-gray-700">Urgence & Assurance</h3>
                    @foreach([
                        ['label' => 'Contact urgence', 'value' => $patient->emergency_contact_name ?? '-'],
                        ['label' => 'Tél. urgence',    'value' => $patient->emergency_contact_phone ?? '-'],
                        ['label' => 'Assurance',        'value' => $patient->insurance_provider ?? '-'],
                        ['label' => 'N° police',        'value' => $patient->insurance_number ?? '-'],
                    ] as $row)
                    <div class="flex gap-3 text-sm">
                        <span class="w-40 text-gray-400">{{ $row['label'] }}</span>
                        <span class="text-gray-800">{{ $row['value'] }}</span>
                    </div>
                    @endforeach
                </div>
                @if($patient->medicalRecord)
                <div class="md:col-span-2 space-y-3">
                    <h3 class="font-semibold text-gray-700">Antécédents médicaux</h3>
                    @foreach([
                        ['label' => 'Maladies chroniques', 'value' => $patient->medicalRecord->chronic_diseases ?? '-'],
                        ['label' => 'Allergies',           'value' => $patient->medicalRecord->allergies ?? '-'],
                        ['label' => 'Antécédents familiaux','value' => $patient->medicalRecord->family_history ?? '-'],
                        ['label' => 'Antécédents chirurgicaux','value' => $patient->medicalRecord->surgical_history ?? '-'],
                    ] as $row)
                    <div class="flex gap-3 text-sm">
                        <span class="w-48 text-gray-400 shrink-0">{{ $row['label'] }}</span>
                        <span class="text-gray-800">{{ $row['value'] }}</span>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        {{-- Tab: Consultations --}}
        <div x-show="tab === 'consult'" class="p-6">
            @forelse($patient->medicalRecord?->consultations ?? [] as $c)
            <div class="border border-gray-100 rounded-xl p-4 mb-3 hover:bg-gray-50 transition">
                <div class="flex items-center justify-between mb-2">
                    <div class="font-medium text-gray-800">{{ $c->consultation_date->format('d/m/Y') }}</div>
                    <span class="text-xs px-2 py-1 rounded-full
                        {{ $c->status === 'closed' ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700' }}">
                        {{ ucfirst($c->status) }}
                    </span>
                </div>
                <p class="text-sm text-gray-600">{{ $c->chief_complaint }}</p>
                <div class="text-xs text-gray-400 mt-1">Dr. {{ $c->doctor->getFullName() }}</div>
                <a href="{{ route('consultations.show', $c) }}" class="text-xs text-blue-600 hover:underline mt-2 inline-block">
                    Voir le détail →
                </a>
            </div>
            @empty
            <p class="text-gray-400 text-sm text-center py-8">Aucune consultation enregistrée.</p>
            @endforelse
        </div>

        {{-- Tab: Prescriptions --}}
        <div x-show="tab === 'rx'" class="p-6">
            @forelse($patient->prescriptions ?? [] as $rx)
            <div class="border border-gray-100 rounded-xl p-4 mb-3">
                <div class="flex items-center justify-between mb-2">
                    <span class="font-mono text-sm text-gray-600">{{ $rx->prescription_number }}</span>
                    <span class="text-xs px-2 py-1 rounded-full
                        {{ $rx->status === 'dispensed' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                        {{ ucfirst($rx->status) }}
                    </span>
                </div>
                <div class="text-xs text-gray-500">{{ $rx->issued_at->format('d/m/Y') }} · Dr. {{ $rx->doctor->getFullName() }}</div>
                <ul class="mt-2 space-y-1">
                    @foreach($rx->items as $item)
                    <li class="text-sm text-gray-700">
                        • {{ $item->medicine_name }} - {{ $item->dosage }} - {{ $item->frequency }}
                    </li>
                    @endforeach
                </ul>
            </div>
            @empty
            <p class="text-gray-400 text-sm text-center py-8">Aucune ordonnance.</p>
            @endforelse
        </div>

        {{-- Tab: Lab --}}
        <div x-show="tab === 'lab'" class="p-6">
            @forelse($patient->labRequests ?? [] as $lr)
            <div class="border border-gray-100 rounded-xl p-4 mb-3">
                <div class="flex items-center justify-between mb-2">
                    <span class="font-mono text-sm">{{ $lr->request_number }}</span>
                    <span class="text-xs px-2 py-1 rounded-full
                        {{ $lr->isCompleted() ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700' }}">
                        {{ ucfirst($lr->status) }}
                    </span>
                </div>
                <div class="text-xs text-gray-500">{{ $lr->requested_at->format('d/m/Y H:i') }}</div>
                <ul class="mt-2 space-y-1">
                    @foreach($lr->items as $item)
                    <li class="text-sm text-gray-700 flex items-center justify-between">
                        <span>{{ $item->labExam->name }}</span>
                        @if($item->result)
                        <span class="text-xs font-medium
                            {{ $item->result->isCritical() ? 'text-red-600' : ($item->result->isAbnormal() ? 'text-orange-500' : 'text-green-600') }}">
                            {{ $item->result->result_value }} {{ $item->result->unit }}
                        </span>
                        @endif
                    </li>
                    @endforeach
                </ul>
            </div>
            @empty
            <p class="text-gray-400 text-sm text-center py-8">Aucune demande d'analyse.</p>
            @endforelse
        </div>

        {{-- Tab: Billing --}}
        <div x-show="tab === 'billing'" class="p-6">
            @forelse($patient->invoices ?? [] as $inv)
            <div class="border border-gray-100 rounded-xl p-4 mb-3 flex items-center justify-between">
                <div>
                    <div class="font-mono text-sm text-gray-700">{{ $inv->invoice_number }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ $inv->invoice_date->format('d/m/Y') }}</div>
                </div>
                <div class="text-right">
                    <div class="font-bold text-gray-800">{{ number_format($inv->total_amount, 0, ',', ' ') }} XAF</div>
                    <span class="text-xs px-2 py-1 rounded-full inline-block mt-1
                        {{ $inv->isPaid() ? 'bg-green-100 text-green-700' : ($inv->isOverdue() ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                        {{ ucfirst($inv->status) }}
                    </span>
                </div>
                <a href="{{ route('invoices.show', $inv) }}" class="text-blue-600 hover:text-blue-800 text-sm ml-4">
                    Voir →
                </a>
            </div>
            @empty
            <p class="text-gray-400 text-sm text-center py-8">Aucune facture.</p>
            @endforelse
        </div>

    </div>

</div>
@endsection
