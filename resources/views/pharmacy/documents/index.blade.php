@extends('layouts.app')
@section('title', 'Gestion documentaire')
@section('page-title', 'Gestion documentaire')

@section('content')
<div class="space-y-4">

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
        <div class="flex items-center justify-between">
            <form method="GET" class="flex flex-wrap gap-2">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Rechercher..."
                       class="px-4 py-2 border border-gray-300 rounded-xl text-sm w-full sm:w-48 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <select name="type" class="border border-gray-300 rounded-xl text-sm px-3 py-2">
                    <option value="">Tous types</option>
                    <option value="sop" {{ request('type') === 'sop' ? 'selected' : '' }}>Procédure (SOP)</option>
                    <option value="contract" {{ request('type') === 'contract' ? 'selected' : '' }}>Contrat</option>
                    <option value="regulatory" {{ request('type') === 'regulatory' ? 'selected' : '' }}>Réglementaire</option>
                    <option value="reference" {{ request('type') === 'reference' ? 'selected' : '' }}>Référence</option>
                    <option value="other" {{ request('type') === 'other' ? 'selected' : '' }}>Autre</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-xl text-sm">Filtrer</button>
            </form>
            <a href="{{ route('pharmacy-documents.create') }}"
               class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm">
                <i class="fa-solid fa-upload mr-1"></i> Nouveau document
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4">
        @forelse($documents as $doc)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition">
            <div class="flex items-start justify-between">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
                        <i class="fa-solid fa-file-lines"></i>
                    </div>
                    <div>
                        <a href="{{ route('pharmacy-documents.show', $doc) }}" class="font-medium text-blue-600 hover:underline">
                            {{ $doc->title }}
                        </a>
                        <div class="flex gap-3 mt-1 text-xs text-gray-500">
                            <span class="px-2 py-0.5 bg-gray-100 rounded-full">
                                @switch($doc->type)
                                    @case('sop') SOP @break
                                    @case('contract') Contrat @break
                                    @case('regulatory') Réglementaire @break
                                    @case('reference') Référence @break
                                    @default Autre
                                @endswitch
                            </span>
                            <span>v{{ $doc->version }}</span>
                            <span>{{ $doc->file_name }}</span>
                            @if($doc->file_size)
                            <span>{{ round($doc->file_size / 1024) }} Ko</span>
                            @endif
                        </div>
                        @if($doc->description)
                        <p class="text-xs text-gray-400 mt-1">{{ Str::limit($doc->description, 120) }}</p>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    @if($doc->expiry_date)
                    <span class="text-xs {{ $doc->expiry_date->isPast() ? 'text-red-500' : 'text-gray-400' }}">
                        <i class="fa-regular fa-calendar mr-1"></i>{{ $doc->expiry_date->format('d/m/Y') }}
                    </span>
                    @endif
                    <a href="{{ route('pharmacy-documents.edit', $doc) }}" class="text-gray-400 hover:text-blue-600 text-sm">
                        <i class="fa-solid fa-pen"></i>
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center text-gray-400">
            Aucun document trouvé.
        </div>
        @endforelse
    </div>

    @if($documents->hasPages())
    <div class="mt-4">{{ $documents->links() }}</div>
    @endif
</div>
@endsection
