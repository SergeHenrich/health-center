@extends('layouts.app')
@section('title', 'Nouvelle chambre')
@section('page-title', 'Créer une chambre')

@section('content')
<div class="max-w-2xl">

    <form method="POST" action="{{ route('rooms.store') }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
        @csrf

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Numéro de chambre *</label>
                <input type="text" name="room_number" value="{{ old('room_number') }}" required
                       class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
                       placeholder="ex: A-101">
                @error('room_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nom (optionnel)</label>
                <input type="text" name="name" value="{{ old('name') }}"
                       class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
        </div>

        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Type *</label>
                <select name="type" required class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="standard">Standard</option>
                    <option value="intensive_care">Soins intensifs</option>
                    <option value="surgery">Chirurgie</option>
                    <option value="maternity">Maternité</option>
                    <option value="emergency">Urgence</option>
                    <option value="isolation">Isolement</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Étage</label>
                <input type="text" name="floor" value="{{ old('floor') }}"
                       class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
                       placeholder="ex: 2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Capacité (lits) *</label>
                <input type="number" name="capacity" value="{{ old('capacity', 1) }}" min="1" max="50" required
                       class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea name="notes" rows="2" class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">{{ old('notes') }}</textarea>
        </div>

        <div class="flex gap-3 pt-2">
            <a href="{{ route('rooms.index') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 rounded-xl text-sm text-gray-700">Annuler</a>
            <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 rounded-xl text-sm text-white font-medium">
                <i class="fa-solid fa-bed mr-1"></i> Créer la chambre
            </button>
        </div>
    </form>
</div>
@endsection
