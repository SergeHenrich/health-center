@extends('layouts.app')
@section('title', 'Nouvelle dépense')
@section('page-title', 'Enregistrer une dépense')

@section('content')
<div class="max-w-2xl">

    <form method="POST" action="{{ route('expenses.store') }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
        @csrf

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Catégorie *</label>
                <select name="category" required class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="supplies">Fournitures</option>
                    <option value="equipment">Équipement</option>
                    <option value="maintenance">Maintenance</option>
                    <option value="salary">Salaires</option>
                    <option value="utilities">Charges</option>
                    <option value="other">Autre</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date *</label>
                <input type="date" name="expense_date" value="{{ old('expense_date', date('Y-m-d')) }}" required
                       class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
            <textarea name="description" rows="2" required class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">{{ old('description') }}</textarea>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Montant (XAF) *</label>
                <input type="number" name="amount" value="{{ old('amount') }}" min="1" required
                       class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Mode de paiement *</label>
                <select name="payment_method" required class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="cash">Espèces</option>
                    <option value="mobile_money">Mobile Money</option>
                    <option value="bank_transfer">Virement bancaire</option>
                    <option value="card">Carte</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Référence</label>
            <input type="text" name="reference_code" value="{{ old('reference_code') }}"
                   class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea name="notes" rows="2" class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">{{ old('notes') }}</textarea>
        </div>

        <div class="flex gap-3 pt-2">
            <a href="{{ route('expenses.index') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 rounded-xl text-sm text-gray-700">Annuler</a>
            <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 rounded-xl text-sm text-white font-medium">
                <i class="fa-solid fa-save mr-1"></i> Enregistrer
            </button>
        </div>
    </form>
</div>
@endsection
