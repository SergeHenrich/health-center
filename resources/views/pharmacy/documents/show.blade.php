@extends('layouts.app')
@section('title', $pharmacyDocument->title)
@section('page-title', $pharmacyDocument->title)

@section('content')
<div class="space-y-6">

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-start justify-between">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6 text-sm flex-1">
                <div>
                    <span class="text-gray-500">Type</span>
                    <p class="mt-1 font-medium">
                        @switch($pharmacyDocument->type)
                            @case('sop') Procédure (SOP) @break
                            @case('contract') Contrat @break
                            @case('regulatory') Réglementaire @break
                            @case('reference') Référence @break
                            @default Autre
                        @endswitch
                    </p>
                </div>
                <div>
                    <span class="text-gray-500">Version</span>
                    <p class="mt-1 font-mono">{{ $pharmacyDocument->version }}</p>
                </div>
                <div>
                    <span class="text-gray-500">Référence</span>
                    <p class="mt-1">{{ $pharmacyDocument->reference ?? '-' }}</p>
                </div>
                <div>
                    <span class="text-gray-500">Fichier</span>
                    <p class="mt-1 text-blue-600">
                        <i class="fa-regular fa-file mr-1"></i>{{ $pharmacyDocument->file_name }}
                        @if($pharmacyDocument->file_size)
                        <span class="text-gray-400">({{ round($pharmacyDocument->file_size / 1024) }} Ko)</span>
                        @endif
                    </p>
                </div>
                <div>
                    <span class="text-gray-500">Uploadé par</span>
                    <p class="mt-1">{{ $pharmacyDocument->uploader?->getFullName() ?? '-' }}</p>
                </div>
                <div>
                    <span class="text-gray-500">Date d'expiration</span>
                    <p class="mt-1 {{ $pharmacyDocument->expiry_date?->isPast() ? 'text-red-500' : '' }}">
                        {{ $pharmacyDocument->expiry_date?->format('d/m/Y') ?? '-' }}
                    </p>
                </div>
                @if($pharmacyDocument->medicine)
                <div>
                    <span class="text-gray-500">Médicament lié</span>
                    <p class="mt-1">{{ $pharmacyDocument->medicine->name }}</p>
                </div>
                @endif
                @if($pharmacyDocument->supplier)
                <div>
                    <span class="text-gray-500">Fournisseur lié</span>
                    <p class="mt-1">{{ $pharmacyDocument->supplier->name }}</p>
                </div>
                @endif
                <div>
                    <span class="text-gray-500">Publié le</span>
                    <p class="mt-1">{{ $pharmacyDocument->published_at?->format('d/m/Y') ?? '-' }}</p>
                </div>
            </div>
        </div>

        @if($pharmacyDocument->description)
        <div class="mt-6">
            <h4 class="text-sm font-semibold text-gray-700 mb-2">Description</h4>
            <p class="text-sm text-gray-600 bg-gray-50 rounded-xl p-4">{{ $pharmacyDocument->description }}</p>
        </div>
        @endif

        <div class="flex gap-3 mt-6 border-t border-gray-100 pt-6">
            <a href="{{ route('pharmacy-documents.edit', $pharmacyDocument) }}"
               class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm">
                <i class="fa-solid fa-pen mr-1"></i> Modifier
            </a>
            <form method="POST" action="{{ route('pharmacy-documents.destroy', $pharmacyDocument) }}" onsubmit="return confirm('Supprimer ce document ?')">
                @csrf @method('DELETE')
                <button type="submit" class="px-4 py-2 bg-red-50 hover:bg-red-100 text-red-600 rounded-xl text-sm">
                    <i class="fa-solid fa-trash mr-1"></i> Supprimer
                </button>
            </form>
        </div>
    </div>

</div>
@endsection
