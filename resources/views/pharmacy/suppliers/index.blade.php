@extends('layouts.app')
@section('title', 'Fournisseurs')
@section('page-title', 'Gestion des fournisseurs')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <form method="GET" class="flex flex-wrap gap-2">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Rechercher..."
                   class="px-4 py-2 border border-gray-300 rounded-xl text-sm w-full sm:w-64 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <select name="filter" class="border border-gray-300 rounded-xl text-sm px-3 py-2">
                <option value="">Tous</option>
                <option value="active" {{ request('filter') === 'active' ? 'selected' : '' }}>Actifs</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-xl text-sm">Filtrer</button>
        </form>
        <a href="{{ route('suppliers.create') }}"
           class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm">
            <i class="fa-solid fa-plus mr-1"></i> Nouveau fournisseur
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Code</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Nom</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Contact</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Ville</th>
                    <th class="text-center px-6 py-4 font-semibold text-gray-600">Contrats</th>
                    <th class="text-center px-6 py-4 font-semibold text-gray-600">Commandes</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Statut</th>
                    <th class="text-right px-6 py-4 font-semibold text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($suppliers as $supplier)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 font-mono text-xs text-gray-500">{{ $supplier->code }}</td>
                    <td class="px-6 py-4">
                        <a href="{{ route('suppliers.show', $supplier) }}" class="font-medium text-blue-600 hover:underline">
                            {{ $supplier->name }}
                        </a>
                    </td>
                    <td class="px-6 py-4 text-gray-600">
                        <div>{{ $supplier->contact_name ?? '-' }}</div>
                        <div class="text-xs text-gray-400">{{ $supplier->phone ?? '' }}</div>
                    </td>
                    <td class="px-6 py-4 text-gray-600">{{ $supplier->city ?? '-' }}</td>
                    <td class="px-6 py-4 text-center">{{ $supplier->contracts_count }}</td>
                    <td class="px-6 py-4 text-center">{{ $supplier->purchase_orders_count }}</td>
                    <td class="px-6 py-4">
                        @if($supplier->is_active)
                        <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">Actif</span>
                        @else
                        <span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs font-medium">Inactif</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('suppliers.edit', $supplier) }}" class="text-blue-600 hover:underline text-xs">Modifier</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-6 py-12 text-center text-gray-400">Aucun fournisseur.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($suppliers->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">{{ $suppliers->withQueryString()->links() }}</div>
        @endif
    </div>
</div>
@endsection
