@extends('layouts.app')
@section('title', 'Point de vente')
@section('page-title', 'Point de vente - Pharmacie')

@section('content')
<div class="flex gap-6 h-[calc(100vh-12rem)]"
     x-data="posApp({{ json_encode(array_values($cart)) }}, {{ $cartTotal }})">
    {{-- Left: Product search & listing --}}
    <div class="flex-1 flex flex-col bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-4 border-b border-gray-100">
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="text" x-model="query" @input.debounce.300ms="searchMedicines()"
                       placeholder="Rechercher un médicament (nom, code...)"
                       class="w-full pl-9 pr-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex gap-2 mt-3">
                <template x-for="cat in ['Tous', 'Comprimé', 'Sirop', 'Injectable', 'Pommade', 'Gouttes']" :key="cat">
                    <button @click="filter = (filter === cat ? 'Tous' : cat); searchMedicines()"
                            class="px-3 py-1.5 text-xs rounded-full border transition"
                            :class="filter === cat ? 'bg-blue-600 text-white border-blue-600' : 'bg-gray-50 text-gray-600 border-gray-200 hover:bg-gray-100'"
                            x-text="cat"></button>
                </template>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-4">
            <div x-show="loading" class="text-center py-8 text-gray-400">
                <i class="fa-solid fa-spinner fa-spin text-xl"></i>
            </div>
            <div x-show="!loading && results.length === 0 && query.length > 0"
                 class="text-center py-8 text-gray-400">
                Aucun médicament trouvé.
            </div>
            <div x-show="!loading && results.length === 0 && query.length === 0"
                 class="text-center py-8 text-gray-400">
                Commencez à taper pour rechercher.
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                <template x-for="med in results" :key="med.id">
                    <div class="border border-gray-200 rounded-xl p-3 hover:shadow-md transition cursor-pointer"
                         @click="addToCart(med.id, 1)">
                        <div class="flex items-start justify-between">
                            <div class="flex-1 min-w-0">
                                <div class="font-medium text-gray-800 text-sm truncate" x-text="med.name"></div>
                                <div class="text-xs text-gray-400 truncate" x-text="med.generic_name"></div>
                                <div class="text-xs text-gray-400" x-text="med.form + (med.strength ? ' - ' + med.strength : '')"></div>
                            </div>
                            <span class="text-xs text-gray-400 font-mono ml-2" x-text="med.code"></span>
                        </div>
                        <div class="flex items-center justify-between mt-2">
                            <span class="text-sm font-bold text-blue-600" x-text="formatPrice(med.unit_price)"></span>
                            <span class="text-xs" :class="med.stock_available > 5 ? 'text-green-600' : 'text-red-600'"
                                  x-text="med.stock_available + ' en stock'"></span>
                        </div>
                        <button @click.stop="addToCart(med.id, 1)"
                                class="mt-2 w-full py-1.5 text-xs rounded-lg font-medium"
                                :class="med.stock_available > 0 ? 'bg-blue-50 text-blue-600 hover:bg-blue-100' : 'bg-gray-100 text-gray-400 cursor-not-allowed'"
                                :disabled="med.stock_available === 0">
                            <i class="fa-solid fa-plus mr-1"></i> Ajouter
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- Right: Cart panel --}}
    <div class="w-96 flex flex-col bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-semibold text-gray-800">
                <i class="fa-solid fa-cart-shopping text-blue-600 mr-2"></i>Panier
                <span class="ml-1 text-sm text-gray-400" x-show="cart.length > 0" x-text="'(' + cart.length + ')'"></span>
            </h2>
            <button @click="clearCart()" x-show="cart.length > 0"
                    class="text-xs text-red-500 hover:text-red-700">
                <i class="fa-solid fa-trash-can mr-1"></i>Vider
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-4 space-y-3">
            <template x-if="cart.length === 0">
                <div class="text-center py-8 text-gray-400">
                    <i class="fa-solid fa-cart-plus text-3xl mb-2"></i>
                    <p class="text-sm">Panier vide</p>
                </div>
            </template>
            <template x-for="(item, idx) in cart" :key="item.medicine_id">
                <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl">
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium text-gray-800 truncate" x-text="item.name"></div>
                        <div class="text-xs text-gray-400" x-text="formatPrice(item.unit_price) + ' / unité'"></div>
                    </div>
                    <div class="flex items-center gap-1">
                        <button @click="updateQty(item.medicine_id, item.quantity - 1)"
                                class="w-7 h-7 flex items-center justify-center rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-600 text-xs">
                            <i class="fa-solid fa-minus"></i>
                        </button>
                        <input type="number" :value="item.quantity" @change="updateQty(item.medicine_id, $event.target.value)"
                               class="w-12 text-center text-sm border border-gray-300 rounded-lg py-1 focus:outline-none focus:ring-1 focus:ring-blue-500"
                               min="0">
                        <button @click="updateQty(item.medicine_id, item.quantity + 1)"
                                class="w-7 h-7 flex items-center justify-center rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-600 text-xs">
                            <i class="fa-solid fa-plus"></i>
                        </button>
                    </div>
                    <div class="text-right min-w-[80px]">
                        <div class="text-sm font-semibold text-gray-800" x-text="formatPrice(item.unit_price * item.quantity)"></div>
                    </div>
                    <button @click="removeItem(item.medicine_id)"
                            class="text-red-400 hover:text-red-600 text-xs">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </template>
        </div>

        {{-- Cart footer --}}
        <div class="border-t border-gray-100 p-4 space-y-3" x-show="cart.length > 0">
            <div class="flex justify-between items-center">
                <span class="text-gray-600 text-sm">Total</span>
                <span class="text-xl font-bold text-gray-900" x-text="formatPrice(cartTotal)"></span>
            </div>

            {{-- Patient selection (optional) --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    Patient <span class="text-gray-400">(optionnel - Client de passage par défaut)</span>
                </label>
                <div class="relative">
                    <input type="text" x-model="patientQuery" @input.debounce.300ms="searchPatients()"
                           placeholder="Rechercher un patient..."
                           class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <template x-if="patientResults.length > 0">
                        <div class="absolute z-10 top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-lg max-h-48 overflow-y-auto">
                            <template x-for="p in patientResults" :key="p.id">
                                <div @click="selectPatient(p)" class="px-3 py-2 hover:bg-blue-50 cursor-pointer text-sm"
                                     :class="selectedPatient?.id === p.id ? 'bg-blue-50' : ''">
                                    <span class="font-medium text-gray-800" x-text="p.full_name"></span>
                                    <span class="text-xs text-gray-400 ml-2" x-text="p.code"></span>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
                <template x-if="selectedPatient">
                    <div class="mt-1 flex items-center gap-2 text-xs text-gray-600">
                        <i class="fa-solid fa-user text-blue-500"></i>
                        <span x-text="selectedPatient.full_name"></span>
                        <button @click="selectedPatient = null; patientQuery = ''" class="text-red-400 hover:text-red-600 ml-1">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </template>
                <template x-if="!selectedPatient">
                    <div class="mt-1 text-xs text-gray-400">
                        <i class="fa-solid fa-user-slash mr-1"></i> Client de passage
                    </div>
                </template>
            </div>

            <button @click="openPaymentModal()"
                    class="w-full py-3 rounded-xl text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white transition">
                <i class="fa-solid fa-credit-card mr-2"></i> Procéder au paiement
            </button>
        </div>
    </div>
</div>

{{-- Payment Modal --}}
<div x-show="showPaymentModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4"
     x-cloak @click.away="showPaymentModal = false">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Finaliser la vente</h3>
        <form method="POST" action="{{ route('pharmacy.pos.checkout') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="patient_id" :value="selectedPatient?.id || ''">
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Total à payer</span>
                <span class="text-xl font-bold text-gray-900" x-text="formatPrice(cartTotal)"></span>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Montant perçu (XAF)</label>
                <input type="number" step="0.01" name="amount" :value="cartTotal"
                       min="0.01" required
                       class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Mode de paiement</label>
                <select name="method" required class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="cash">Espèces</option>
                    <option value="mobile_money">Mobile Money</option>
                    <option value="bank_transfer">Virement bancaire</option>
                    <option value="card">Carte bancaire</option>
                    <option value="insurance">Assurance</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Référence (optionnel)</label>
                <input type="text" name="reference_code"
                       class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" @click="showPaymentModal = false"
                        class="flex-1 px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-xl text-sm text-gray-700">
                    Annuler
                </button>
                <button type="submit"
                        class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 rounded-xl text-sm text-white font-medium">
                    <i class="fa-solid fa-check mr-1"></i> Confirmer le paiement
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function posApp(cart = [], cartTotal = 0) {
    return {
        query: '',
        filter: 'Tous',
        results: [],
        loading: false,
        cart: cart,
        cartTotal: cartTotal,
        showPaymentModal: false,
        patientQuery: '',
        patientResults: [],
        selectedPatient: null,

        searchMedicines() {
            if (this.query.length < 1 && this.filter === 'Tous') {
                this.results = [];
                return;
            }
            this.loading = true;
            fetch('{{ route("pharmacy.pos.medicines.search") }}?q=' + encodeURIComponent(this.query) + '&filter=' + encodeURIComponent(this.filter))
                .then(r => r.json())
                .then(data => { this.results = data; this.loading = false; })
                .catch(() => this.loading = false);
        },

        searchPatients() {
            if (this.patientQuery.length < 1) {
                this.patientResults = [];
                return;
            }
            fetch('{{ route("pharmacy.pos.patients.search") }}?q=' + encodeURIComponent(this.patientQuery))
                .then(r => r.json())
                .then(data => this.patientResults = data);
        },

        selectPatient(p) {
            this.selectedPatient = p;
            this.patientQuery = p.full_name;
            this.patientResults = [];
        },

        addToCart(medicineId, quantity) {
            fetch('{{ route("pharmacy.pos.cart.add") }}', {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                body: JSON.stringify({medicine_id: medicineId, quantity: quantity})
            })
            .then(r => r.json())
            .then(data => {
                if (data.error) { alert(data.error); return; }
                this.cart = data.cart;
                this.cartTotal = data.cart_total;
            })
            .catch(() => alert('Erreur lors de l\'ajout au panier.'));
        },

        removeItem(medicineId) {
            fetch('{{ route("pharmacy.pos.cart.remove") }}', {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                body: JSON.stringify({medicine_id: medicineId})
            })
            .then(r => r.json())
            .then(data => { this.cart = data.cart; this.cartTotal = data.cart_total; });
        },

        updateQty(medicineId, qty) {
            qty = parseInt(qty);
            if (isNaN(qty) || qty < 0) return;
            fetch('{{ route("pharmacy.pos.cart.update") }}', {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                body: JSON.stringify({medicine_id: medicineId, quantity: qty})
            })
            .then(r => r.json())
            .then(data => {
                if (data.error) { alert(data.error); return; }
                this.cart = data.cart;
                this.cartTotal = data.cart_total;
            });
        },

        clearCart() {
            const ids = this.cart.map(i => i.medicine_id);
            ids.forEach(id => this.removeItem(id));
        },

        openPaymentModal() {
            this.showPaymentModal = true;
        },

        formatPrice(amount) {
            return Number(amount).toLocaleString('fr-FR') + ' XAF';
        }
    }
}
</script>
@endpush
@endsection
