<header class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between shadow-sm">

    {{-- Mobile toggle --}}
    <button @click="sidebarOpen = !sidebarOpen" class="md:hidden text-gray-500 hover:text-gray-800">
        <i class="fa-solid fa-bars text-xl"></i>
    </button>

    {{-- Page title --}}
    <h1 class="text-lg font-semibold text-gray-700">@yield('page-title', 'Tableau de bord')</h1>

    {{-- Right side --}}
    <div class="flex items-center gap-4">

        {{-- Notifications --}}
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" class="relative text-gray-500 hover:text-blue-600">
                <i class="fa-solid fa-bell text-xl"></i>
                @php $unread = auth()->user()->notifications()->wherePivot('is_read', false)->count() @endphp
                @if($unread > 0)
                    <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center">
                        {{ $unread }}
                    </span>
                @endif
            </button>
            <div x-show="open" @click.away="open = false"
                 class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-xl border border-gray-100 z-50 overflow-hidden">
                <div class="px-4 py-3 border-b font-semibold text-sm text-gray-700">Notifications</div>
                @forelse(auth()->user()->notifications()->latest()->take(5)->get() as $notif)
                    <div class="px-4 py-3 border-b text-sm hover:bg-gray-50
                        {{ $notif->pivot->is_read ? 'text-gray-500' : 'text-gray-800 font-medium' }}">
                        <div>{{ $notif->title }}</div>
                        <div class="text-xs text-gray-400 mt-1">{{ $notif->created_at->diffForHumans() }}</div>
                    </div>
                @empty
                    <div class="px-4 py-4 text-sm text-gray-400 text-center">Aucune notification</div>
                @endforelse
            </div>
        </div>

        {{-- User menu --}}
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" class="flex items-center gap-2 text-sm text-gray-700 hover:text-blue-600">
                <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-xs">
                    {{ strtoupper(substr(auth()->user()->first_name, 0, 1) . substr(auth()->user()->last_name, 0, 1)) }}
                </div>
                <span class="hidden md:block">{{ auth()->user()->getFullName() }}</span>
                <i class="fa-solid fa-chevron-down text-xs"></i>
            </button>
            <div x-show="open" @click.away="open = false"
                 class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-gray-100 z-50">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full flex items-center gap-2 px-4 py-3 text-sm text-red-600 hover:bg-red-50 rounded-xl">
                        <i class="fa-solid fa-right-from-bracket"></i> Déconnexion
                    </button>
                </form>
            </div>
        </div>

    </div>
</header>
