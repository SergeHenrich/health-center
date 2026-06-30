<aside class="w-64 bg-slate-800 text-white flex-shrink-0 flex flex-col"
       :class="sidebarOpen ? 'fixed inset-y-0 left-0 z-50 block' : 'hidden md:flex'">

    {{-- Mobile close button --}}
    <button @click="sidebarOpen = false" class="md:hidden absolute top-3 right-3 text-white text-xl z-10">
        <i class="fa-solid fa-xmark"></i>
    </button>

    {{-- Logo --}}
    <div class="flex items-center gap-2 px-5 py-4 border-b border-slate-700 shrink-0">
        <i class="fa-solid fa-hospital-user text-blue-400 text-xl"></i>
        <span class="font-bold text-sm tracking-wide">HealthCenter</span>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 overflow-y-auto text-xs custom-scrollbar">

        <a href="{{ route('dashboard') }}"
           class="flex items-center gap-3 px-5 py-2.5 hover:bg-slate-700 {{ request()->routeIs('dashboard') ? 'bg-blue-600' : '' }}">
            <i class="fa-solid fa-gauge w-5 text-center"></i> Tableau de bord
        </a>

        @hasanyrole('administrator|receptionist|general_practitioner|specialist|nurse')
        <div class="px-5 pt-3 pb-1 text-xs text-slate-400 uppercase tracking-widest">Patients</div>

        <a href="{{ route('patients.index') }}"
           class="flex items-center gap-3 px-5 py-2.5 hover:bg-slate-700 {{ request()->routeIs('patients.*') ? 'bg-blue-600' : '' }}">
            <i class="fa-solid fa-user-injured w-5 text-center"></i> Patients
        </a>

        <a href="{{ route('appointments.index') }}"
           class="flex items-center gap-3 px-5 py-2.5 hover:bg-slate-700 {{ request()->routeIs('appointments.*') ? 'bg-blue-600' : '' }}">
            <i class="fa-solid fa-calendar-check w-5 text-center"></i> Rendez-vous
        </a>

        <a href="{{ route('queue.index') }}"
           class="flex items-center gap-3 px-5 py-2.5 hover:bg-slate-700 {{ request()->routeIs('queue.*') ? 'bg-blue-600' : '' }}">
            <i class="fa-solid fa-list-ol w-5 text-center"></i> File d'attente
        </a>
        @endhasanyrole

        @hasanyrole('general_practitioner|specialist|administrator')
        <div class="px-5 pt-3 pb-1 text-xs text-slate-400 uppercase tracking-widest">Médical</div>

        <a href="{{ route('consultations.index') }}"
           class="flex items-center gap-3 px-5 py-2.5 hover:bg-slate-700 {{ request()->routeIs('consultations.*') ? 'bg-blue-600' : '' }}">
            <i class="fa-solid fa-stethoscope w-5 text-center"></i> Consultations
        </a>

        <a href="{{ route('lab-requests.index') }}"
           class="flex items-center gap-3 px-5 py-2.5 hover:bg-slate-700 {{ request()->routeIs('lab-*') ? 'bg-blue-600' : '' }}">
            <i class="fa-solid fa-flask w-5 text-center"></i> Laboratoire
        </a>
        @endhasanyrole

        @hasanyrole('pharmacist|preparateur|stock_manager|administrator')
        <div class="px-5 pt-3 pb-1 text-xs text-slate-400 uppercase tracking-widest">Pharmacie</div>

        <a href="{{ route('pharmacy.pos.index') }}"
           class="flex items-center gap-3 px-5 py-2.5 hover:bg-slate-700 {{ request()->routeIs('pharmacy.pos.*') ? 'bg-blue-600' : '' }}">
            <i class="fa-solid fa-cash-register w-5 text-center"></i> Point de vente
        </a>

        <a href="{{ route('stock.index') }}"
           class="flex items-center gap-3 px-5 py-2.5 hover:bg-slate-700 {{ request()->routeIs('stock.*') ? 'bg-blue-600' : '' }}">
            <i class="fa-solid fa-pills w-5 text-center"></i> Stock
        </a>

        <a href="{{ route('formulary.index') }}"
           class="flex items-center gap-3 px-5 py-2.5 hover:bg-slate-700 {{ request()->routeIs('formulary.*') ? 'bg-blue-600' : '' }}">
            <i class="fa-solid fa-book-medical w-5 text-center"></i> Livret
        </a>

        @can('pharmacy.dispense')
        <a href="{{ route('dispensations.index') }}"
           class="flex items-center gap-3 px-5 py-2.5 hover:bg-slate-700 {{ request()->routeIs('dispensations.*') ? 'bg-blue-600' : '' }}">
            <i class="fa-solid fa-prescription-bottle w-5 text-center"></i> Dispensation
        </a>
        @endcan

        @can('pharmacy.validation')
        <a href="{{ route('validations.index') }}"
           class="flex items-center gap-3 px-5 py-2.5 hover:bg-slate-700 {{ request()->routeIs('validations.*') ? 'bg-blue-600' : '' }}">
            <i class="fa-solid fa-clipboard-check w-5 text-center"></i> Validation
        </a>
        @endcan

        <a href="{{ route('purchase-orders.index') }}"
           class="flex items-center gap-3 px-5 py-2.5 hover:bg-slate-700 {{ request()->routeIs('purchase-orders.*') ? 'bg-blue-600' : '' }}">
            <i class="fa-solid fa-truck-medical w-5 text-center"></i> Commandes
        </a>

        @can('pharmacy.suppliers.manage')
        <a href="{{ route('suppliers.index') }}"
           class="flex items-center gap-3 px-5 py-2.5 hover:bg-slate-700 {{ request()->routeIs('suppliers.*') ? 'bg-blue-600' : '' }}">
            <i class="fa-solid fa-truck-field w-5 text-center"></i> Fournisseurs
        </a>
        @endcan

        <a href="{{ route('warehouses.index') }}"
           class="flex items-center gap-3 px-5 py-2.5 hover:bg-slate-700 {{ request()->routeIs('warehouses.*') ? 'bg-blue-600' : '' }}">
            <i class="fa-solid fa-warehouse w-5 text-center"></i> Dépôts
        </a>

        <a href="{{ route('narcotics.index') }}"
           class="flex items-center gap-3 px-5 py-2.5 hover:bg-slate-700 {{ request()->routeIs('narcotics.*') ? 'bg-blue-600' : '' }}">
            <i class="fa-solid fa-skull-crossbones w-5 text-center"></i> Stupéfiants
        </a>

        <div class="border-t border-slate-700 mx-4 my-1.5"></div>
        <div class="px-5 pb-1 text-xs text-slate-400 uppercase tracking-widest">Pilotage</div>

        <a href="{{ route('kpi.index') }}"
           class="flex items-center gap-3 px-5 py-2.5 hover:bg-slate-700 {{ request()->routeIs('kpi.*') ? 'bg-blue-600' : '' }}">
            <i class="fa-solid fa-chart-line w-5 text-center"></i> Indicateurs
        </a>

        <a href="{{ route('medication-events.index') }}"
           class="flex items-center gap-3 px-5 py-2.5 hover:bg-slate-700 {{ request()->routeIs('medication-events.*') ? 'bg-blue-600' : '' }}">
            <i class="fa-solid fa-triangle-exclamation w-5 text-center"></i> Événements
        </a>

        <a href="{{ route('pharmacy-documents.index') }}"
           class="flex items-center gap-3 px-5 py-2.5 hover:bg-slate-700 {{ request()->routeIs('pharmacy-documents.*') ? 'bg-blue-600' : '' }}">
            <i class="fa-solid fa-folder-open w-5 text-center"></i> Documents
        </a>

        <a href="{{ route('batches.index') }}"
           class="flex items-center gap-3 px-5 py-2.5 hover:bg-slate-700 {{ request()->routeIs('batches.*') ? 'bg-blue-600' : '' }}">
            <i class="fa-solid fa-cubes w-5 text-center"></i> Lots
        </a>

        <a href="{{ route('reports.stock') }}"
           class="flex items-center gap-3 px-5 py-2.5 hover:bg-slate-700 {{ request()->routeIs('reports.*') ? 'bg-blue-600' : '' }}">
            <i class="fa-solid fa-chart-bar w-5 text-center"></i> Rapports
        </a>
        @endhasanyrole

        @hasanyrole('general_practitioner|specialist|nurse|administrator')
        <div class="px-5 pt-3 pb-1 text-xs text-slate-400 uppercase tracking-widest">Hospitalisation</div>

        <a href="{{ route('rooms.index') }}"
           class="flex items-center gap-3 px-5 py-2.5 hover:bg-slate-700 {{ request()->routeIs('rooms.*') ? 'bg-blue-600' : '' }}">
            <i class="fa-solid fa-bed w-5 text-center"></i> Chambres
        </a>

        <a href="{{ route('hospitalizations.index') }}"
           class="flex items-center gap-3 px-5 py-2.5 hover:bg-slate-700 {{ request()->routeIs('hospitalizations.*') ? 'bg-blue-600' : '' }}">
            <i class="fa-solid fa-bed-pulse w-5 text-center"></i> Admissions
        </a>
        @endhasanyrole

        @hasanyrole('cashier|administrator|director')
        <div class="px-5 pt-3 pb-1 text-xs text-slate-400 uppercase tracking-widest">Facturation</div>

        <a href="{{ route('invoices.index') }}"
           class="flex items-center gap-3 px-5 py-2.5 hover:bg-slate-700 {{ request()->routeIs('invoices.*') ? 'bg-blue-600' : '' }}">
            <i class="fa-solid fa-file-invoice-dollar w-5 text-center"></i> Factures
        </a>

        <a href="{{ route('payments.index') }}"
           class="flex items-center gap-3 px-5 py-2.5 hover:bg-slate-700 {{ request()->routeIs('payments.*') ? 'bg-blue-600' : '' }}">
            <i class="fa-solid fa-money-bill-wave w-5 text-center"></i> Paiements
        </a>
        @endhasanyrole

        @hasanyrole('administrator')
        <div class="px-5 pt-3 pb-1 text-xs text-slate-400 uppercase tracking-widest">Administration</div>

        <a href="{{ route('admin.users.index') }}"
           class="flex items-center gap-3 px-5 py-2.5 hover:bg-slate-700 {{ request()->routeIs('admin.users.*') ? 'bg-blue-600' : '' }}">
            <i class="fa-solid fa-users-gear w-5 text-center"></i> Utilisateurs
        </a>

        <a href="{{ route('admin.settings.index') }}"
           class="flex items-center gap-3 px-5 py-2.5 hover:bg-slate-700 {{ request()->routeIs('admin.settings.*') ? 'bg-blue-600' : '' }}">
            <i class="fa-solid fa-gear w-5 text-center"></i> Paramètres
        </a>
        @endhasanyrole

    </nav>

    {{-- User info --}}
    <div class="px-5 py-3 border-t border-slate-700 text-xs text-slate-400 shrink-0">
        <div class="font-medium text-white text-sm truncate">{{ auth()->user()->getFullName() }}</div>
        <div class="capitalize truncate">{{ auth()->user()->getRoleNames()->first() }}</div>
    </div>

</aside>
