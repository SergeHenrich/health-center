<!DOCTYPE html>
<html lang="fr" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'HealthCenter') - Centre de Santé</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    @vite('resources/css/app.css')
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #475569; border-radius: 4px; }
    </style>
</head>
<body class="h-full" x-data="{ sidebarOpen: false }"
      @keydown.window.escape="sidebarOpen = false">

<div class="flex h-full">

    {{-- Mobile backdrop --}}
    <template x-teleport="body">
        <div x-show="sidebarOpen" @click="sidebarOpen = false"
             class="fixed inset-0 z-30 bg-black/40 md:hidden"
             x-transition.opacity>
        </div>
    </template>

    {{-- Sidebar --}}
    @include('layouts.partials.sidebar')

    {{-- Main content --}}
    <div class="flex-1 flex flex-col overflow-hidden">

        {{-- Navbar --}}
        @include('layouts.partials.navbar')

        {{-- Page content --}}
        <main class="flex-1 overflow-y-auto p-6">
            @include('layouts.partials.alerts')
            @yield('content')
        </main>

    </div>
</div>

@stack('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const nav = document.querySelector('aside nav');
    const saved = sessionStorage.getItem('sidebar-scroll');
    if (nav && saved) nav.scrollTop = parseInt(saved, 10);
});
window.addEventListener('beforeunload', () => {
    const nav = document.querySelector('aside nav');
    if (nav) sessionStorage.setItem('sidebar-scroll', nav.scrollTop);
});
</script>
</body>
</html>