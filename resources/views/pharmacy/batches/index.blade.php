@extends('layouts.app')
@section('title', 'Gestion des lots')
@section('page-title', 'Gestion des lots')

@section('content')
<div class="space-y-4">

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
        <form method="GET" class="flex flex-wrap gap-2">
            <select name="status" class="border border-gray-300 rounded-xl text-sm px-3 py-2">
                <option value="">Tous statuts</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actif</option>
                <option value="depleted" {{ request('status') === 'depleted' ? 'selected' : '' }}>Épuisé</option>
                <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Périmé</option>
                <option value="written_off" {{ request('status') === 'written_off' ? 'selected' : '' }}>Mis au rebut</option>
            </select>
            <label class="flex items-center gap-1 text-sm">
                <input type="checkbox" name="expiring" value="1" {{ request('expiring') ? 'checked' : '' }}
                       class="rounded border-gray-300 text-blue-600">
                Péremption < 90j
            </label>
            <label class="flex items-center gap-1 text-sm">
                <input type="checkbox" name="expired" value="1" {{ request('expired') ? 'checked' : '' }}
                       class="rounded border-gray-300 text-blue-600">
                Périmé
            </label>
            <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-xl text-sm">Filtrer</button>
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Médicament</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">N° lot</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Péremption</th>
                    <th class="text-right px-6 py-4 font-semibold text-gray-600">Disponible</th>
                    <th class="text-right px-6 py-4 font-semibold text-gray-600">Initial</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Statut</th>
                    <th class="text-center px-6 py-4 font-semibold text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($batches as $batch)
                <tr class="hover:bg-gray-50 transition {{ $batch->isExpired() ? 'bg-red-50' : '' }}">
                    <td class="px-6 py-4">
                        <a href="{{ route('batches.show', $batch) }}" class="text-blue-600 hover:underline font-medium">
                            {{ $batch->medicine->name }}
                        </a>
                    </td>
                    <td class="px-6 py-4 font-mono text-xs text-gray-500">{{ $batch->lot_number ?? '-' }}</td>
                    <td class="px-6 py-4 {{ $batch->isExpired() ? 'text-red-600 font-medium' : '' }}">
                        {{ $batch->expiry_date?->format('d/m/Y') ?? '-' }}
                        @if($batch->expiry_date && $batch->expiry_date->isPast())
                        <span class="text-xs text-red-500">(périmé)</span>
                        @elseif($batch->expiry_date && $batch->expiry_date <= now()->addDays(90))
                        <span class="text-xs text-amber-500">(bientôt)</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right font-mono">{{ $batch->quantity_available }}</td>
                    <td class="px-6 py-4 text-right text-gray-400 text-xs">{{ $batch->initial_quantity }}</td>
                    <td class="px-6 py-4">
                        @switch($batch->status)
                            @case('active') <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs">Actif</span> @break
                            @case('depleted') <span class="px-2 py-1 bg-gray-100 text-gray-500 rounded-full text-xs">Épuisé</span> @break
                            @case('expired') <span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs">Périmé</span> @break
                            @case('written_off') <span class="px-2 py-1 bg-orange-100 text-orange-700 rounded-full text-xs">Rebus</span> @break
                            @default <span class="px-2 py-1 bg-gray-100 text-gray-500 rounded-full text-xs">{{ $batch->status }}</span>
                        @endswitch
                    </td>
                    <td class="px-6 py-4 text-center">
                        <a href="{{ route('batches.show', $batch) }}" class="text-blue-600 hover:underline text-xs">Détails</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-6 py-12 text-center text-gray-400">Aucun lot trouvé.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($batches->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">{{ $batches->links() }}</div>
        @endif
    </div>
</div>
@endsection
