<!DOCTYPE html>
<html lang="fr" class="h-full bg-gradient-to-br from-slate-800 to-blue-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - HealthCenter</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    @vite('resources/css/app.css')
</head>
<body class="h-full flex items-center justify-center p-4">

<div class="w-full max-w-md">

    {{-- Logo --}}
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-600 rounded-2xl mb-4 shadow-lg">
            <i class="fa-solid fa-hospital-user text-white text-3xl"></i>
        </div>
        <h1 class="text-3xl font-bold text-white">HealthCenter</h1>
        <p class="text-blue-200 mt-1 text-sm">Système de gestion de centre de santé</p>
    </div>

    {{-- Card --}}
    <div class="bg-white rounded-2xl shadow-2xl p-8">

        <h2 class="text-xl font-semibold text-gray-800 mb-6">Connexion</h2>

        @if($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nom d'utilisateur ou email
                </label>
                <div class="relative">
                    <i class="fa-solid fa-user absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="username" value="{{ old('username') }}" required autofocus
                           class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl text-sm
                                  focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                  @error('username') border-red-400 @enderror"
                           placeholder="username ou email">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Mot de passe</label>
                <div class="relative" x-data="{ show: false }">
                    <i class="fa-solid fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input :type="show ? 'text' : 'password'" name="password" required
                           class="w-full pl-10 pr-10 py-3 border border-gray-300 rounded-xl text-sm
                                  focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="••••••••">
                    <button type="button" @click="show = !show"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <i :class="show ? 'fa-eye-slash' : 'fa-eye'" class="fa-solid text-sm"></i>
                    </button>
                </div>
            </div>

            <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-xl
                           transition duration-200 flex items-center justify-center gap-2">
                <i class="fa-solid fa-right-to-bracket"></i> Se connecter
            </button>

        </form>

    </div>

    <p class="text-center text-blue-200 text-xs mt-6">
        &copy; {{ date('Y') }} HealthCenter - Tous droits réservés
    </p>

</div>

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>