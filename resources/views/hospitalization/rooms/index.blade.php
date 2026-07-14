@extends('layouts.app')
@section('title', 'Chambres')
@section('page-title', 'Hospitalisation — Chambres')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">{{ $rooms->total() }} chambre(s)</p>
        <a href="{{ route('rooms.create') }}"
           class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded-xl text-sm text-white">
            <i class="fa-solid fa-plus"></i> Nouvelle chambre
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($rooms as $room)
        <a href="{{ route('rooms.show', $room) }}"
           class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition group">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center group-hover:bg-blue-100 transition">
                        <i class="fa-solid fa-bed text-blue-600"></i>
                    </div>
                    <div>
                        <div class="font-bold text-gray-800">{{ $room->room_number }}</div>
                        <div class="text-xs text-gray-400 capitalize">{{ $room->type }}</div>
                    </div>
                </div>
                @if($room->floor)
                <span class="text-xs bg-gray-100 text-gray-500 px-2 py-1 rounded-full">Étage {{ $room->floor }}</span>
                @endif
            </div>
            <div class="flex items-center gap-4 text-xs text-gray-500">
                @php
                    $total = $room->beds->count();
                    $available = $room->beds->filter->isAvailable()->count();
                @endphp
                <span><i class="fa-solid fa-bed mr-1"></i> {{ $total }} lit(s)</span>
                <span class="{{ $available > 0 ? 'text-green-600 font-semibold' : 'text-red-500' }}">
                    <i class="fa-solid fa-circle mr-1" style="font-size:6px"></i> {{ $available }} disponible(s)
                </span>
            </div>
        </a>
        @empty
        <div class="col-span-3 text-center py-12 text-gray-400">
            <i class="fa-solid fa-bed text-3xl mb-3 block"></i>
            Aucune chambre configurée.
        </div>
        @endforelse
    </div>

    @if($rooms->hasPages())
    <div>{{ $rooms->links() }}</div>
    @endif
</div>
@endsection
