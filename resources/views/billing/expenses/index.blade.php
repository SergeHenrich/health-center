@extends('layouts.app')
@section('title', 'Dépenses')
@section('page-title', 'Gestion des dépenses')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">{{ $expenses->total() }} dépense(s) au total</p>
        <a href="{{ route('expenses.create') }}"
           class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded-xl text-sm text-white">
            <i class="fa-solid fa-plus"></i> Nouvelle dépense
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Date</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Catégorie</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Description</th>
                    <th class="text-left px-6 py-4 font-semibold text-gray-600">Mode</th>
                    <th class="text-right px-6 py-4 font-semibold text-gray-600">Montant</th>
                    <th class="text-center px-6 py-4 font-semibold text-gray-600">Statut</th>
                    <th class="text-center px-6 py-4 font-semibold text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($expenses as $expense)
                <tr class="hover:bg-gray-50/50">
                    <td class="px-6 py-4 text-gray-500">{{ $expense->expense_date->format('d/m/Y') }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 capitalize">{{ $expense->category }}</span>
                    </td>
                    <td class="px-6 py-4 text-gray-700 max-w-xs truncate">{{ $expense->description }}</td>
                    <td class="px-6 py-4 text-gray-600 capitalize">{{ str_replace('_', ' ', $expense->payment_method) }}</td>
                    <td class="px-6 py-4 text-right font-semibold text-gray-800">{{ number_format($expense->amount, 0, ',', ' ') }} XAF</td>
                    <td class="px-6 py-4 text-center">
                        @php $sColors = ['pending' => 'bg-yellow-100 text-yellow-700', 'approved' => 'bg-green-100 text-green-700', 'rejected' => 'bg-red-100 text-red-700']; @endphp
                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $sColors[$expense->status] ?? 'bg-gray-100' }}">{{ ucfirst($expense->status) }}</span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if($expense->status === 'pending')
                        <form method="POST" action="{{ route('expenses.approve', $expense) }}" class="inline">
                            @csrf
                            <button class="text-green-600 hover:text-green-800 text-xs font-medium">Approuver</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                        <i class="fa-solid fa-receipt text-3xl mb-3 block"></i>
                        Aucune dépense enregistrée.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $expenses->links() }}
</div>
@endsection
