@extends('layouts.app')
@section('title', $supplier->name)
@section('page-title', $supplier->name)

@section('content')
<div class="space-y-6">

    {{-- Info --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 text-sm">
            <div><span class="text-gray-500">Code</span><p class="font-mono mt-1">{{ $supplier->code }}</p></div>
            <div><span class="text-gray-500">Contact</span><p class="mt-1">{{ $supplier->contact_name ?? '-' }}</p></div>
            <div><span class="text-gray-500">Email</span><p class="mt-1">{{ $supplier->email ?? '-' }}</p></div>
            <div><span class="text-gray-500">Téléphone</span><p class="mt-1">{{ $supplier->phone ?? '-' }}</p></div>
            <div><span class="text-gray-500">Ville</span><p class="mt-1">{{ $supplier->city ?? '-' }}</p></div>
            <div><span class="text-gray-500">N° fiscal</span><p class="mt-1">{{ $supplier->tax_id ?? '-' }}</p></div>
            <div><span class="text-gray-500">Catégorie</span><p class="mt-1">{{ $supplier->category ?? '-' }}</p></div>
            <div>
                <span class="text-gray-500">Statut</span>
                <p class="mt-1">
                    @if($supplier->is_active)
                    <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">Actif</span>
                    @else
                    <span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs font-medium">Inactif</span>
                    @endif
                </p>
            </div>
        </div>
        @if($supplier->address)
        <div class="mt-4 text-sm"><span class="text-gray-500">Adresse :</span> {{ $supplier->address }}</div>
        @endif
        <div class="flex gap-2 mt-4">
            <a href="{{ route('suppliers.edit', $supplier) }}" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm">Modifier</a>
        </div>
    </div>

    {{-- Commandes récentes --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h3 class="font-semibold text-gray-800 mb-4">Dernières commandes</h3>
        @if($supplier->purchaseOrders->count())
        <table class="w-full text-sm">
            <thead><tr class="text-left text-gray-500"><th class="pb-2">N°</th><th class="pb-2">Date</th><th class="pb-2">Montant</th><th class="pb-2">Statut</th></tr></thead>
            <tbody>
                @foreach($supplier->purchaseOrders as $po)
                <tr class="border-t border-gray-50">
                    <td class="py-2 font-mono text-xs">{{ $po->order_number }}</td>
                    <td class="py-2">{{ $po->ordered_at->format('d/m/Y') }}</td>
                    <td class="py-2">{{ number_format($po->total_amount, 0, ',', ' ') }} XAF</td>
                    <td class="py-2"><span class="px-2 py-0.5 bg-gray-100 rounded-full text-xs">{{ $po->status }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else <p class="text-gray-400 text-sm">Aucune commande.</p> @endif
    </div>

    {{-- Contrats --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-800">Contrats</h3>
            <button @click="document.getElementById('contract-form').classList.toggle('hidden')"
                    class="text-blue-600 text-sm hover:underline">+ Ajouter</button>
        </div>
        <form id="contract-form" method="POST" action="{{ route('suppliers.contracts.store', $supplier) }}" class="hidden mb-6 p-4 bg-gray-50 rounded-xl space-y-3">
            @csrf
            <div class="grid grid-cols-2 gap-3">
                <input type="text" name="contract_number" placeholder="N° contrat *" required
                       class="px-3 py-2 border border-gray-300 rounded-xl text-sm">
                <input type="date" name="start_date" required
                       class="px-3 py-2 border border-gray-300 rounded-xl text-sm">
                <input type="date" name="end_date"
                       class="px-3 py-2 border border-gray-300 rounded-xl text-sm">
                <input type="number" name="discount_percent" step="0.01" min="0" max="100" placeholder="Remise (%)"
                       class="px-3 py-2 border border-gray-300 rounded-xl text-sm">
            </div>
            <textarea name="terms" rows="2" placeholder="Conditions..."
                      class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm"></textarea>
            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-xl text-sm">Enregistrer le contrat</button>
        </form>
        @if($supplier->contracts->count())
        <table class="w-full text-sm">
            <thead><tr class="text-left text-gray-500"><th class="pb-2">N°</th><th class="pb-2">Début</th><th class="pb-2">Fin</th><th class="pb-2">Remise</th><th class="pb-2">Statut</th></tr></thead>
            <tbody>
                @foreach($supplier->contracts as $contract)
                <tr class="border-t border-gray-50">
                    <td class="py-2 font-mono text-xs">{{ $contract->contract_number }}</td>
                    <td class="py-2">{{ $contract->start_date->format('d/m/Y') }}</td>
                    <td class="py-2">{{ $contract->end_date?->format('d/m/Y') ?? '-' }}</td>
                    <td class="py-2">{{ $contract->discount_percent }}%</td>
                    <td class="py-2"><span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-xs">{{ $contract->status }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else <p class="text-gray-400 text-sm">Aucun contrat.</p> @endif
    </div>

    {{-- Évaluations --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-800">Évaluations</h3>
            <button @click="document.getElementById('eval-form').classList.toggle('hidden')"
                    class="text-blue-600 text-sm hover:underline">+ Évaluer</button>
        </div>
        <form id="eval-form" method="POST" action="{{ route('suppliers.evaluations.store', $supplier) }}" class="hidden mb-6 p-4 bg-gray-50 rounded-xl space-y-3">
            @csrf
            <div class="grid grid-cols-3 gap-3">
                <div><label class="text-xs text-gray-500">Qualité (1-10)</label>
                    <input type="number" name="quality_score" min="1" max="10" class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm"></div>
                <div><label class="text-xs text-gray-500">Livraison (1-10)</label>
                    <input type="number" name="delivery_score" min="1" max="10" class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm"></div>
                <div><label class="text-xs text-gray-500">Prix (1-10)</label>
                    <input type="number" name="price_score" min="1" max="10" class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm"></div>
            </div>
            <textarea name="comments" rows="2" placeholder="Commentaires..."
                      class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm"></textarea>
            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-xl text-sm">Enregistrer l'évaluation</button>
        </form>
        @if($supplier->evaluations->count())
        <table class="w-full text-sm">
            <thead><tr class="text-left text-gray-500"><th class="pb-2">Date</th><th class="pb-2">Qualité</th><th class="pb-2">Livraison</th><th class="pb-2">Prix</th><th class="pb-2">Global</th><th class="pb-2">Évaluateur</th></tr></thead>
            <tbody>
                @foreach($supplier->evaluations as $eval)
                <tr class="border-t border-gray-50">
                    <td class="py-2">{{ $eval->evaluation_date->format('d/m/Y') }}</td>
                    <td class="py-2">{{ $eval->quality_score ?? '-' }}</td>
                    <td class="py-2">{{ $eval->delivery_score ?? '-' }}</td>
                    <td class="py-2">{{ $eval->price_score ?? '-' }}</td>
                    <td class="py-2 font-bold">{{ $eval->overall_score ?? '-' }}</td>
                    <td class="py-2">{{ $eval->evaluator?->getFullName() ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else <p class="text-gray-400 text-sm">Aucune évaluation.</p> @endif
    </div>
</div>
@endsection
