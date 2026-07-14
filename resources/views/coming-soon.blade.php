@extends('layouts.app')
@section('title', $pageTitle ?? 'En cours de développement')
@section('content')

<div class="flex flex-col items-center justify-center py-24 text-center">
    <div class="w-20 h-20 rounded-full bg-blue-50 flex items-center justify-center mb-6">
        <i class="fa-solid fa-tools text-3xl text-blue-400"></i>
    </div>
    <h2 class="text-2xl font-bold text-gray-800 mb-2">{{ $pageTitle ?? 'Page en cours de développement' }}</h2>
    <p class="text-gray-500 max-w-md">
        {{ $message ?? 'Cette fonctionnalité est en cours de développement et sera disponible prochainement.' }}
    </p>
    <a href="{{ url()->previous() }}"
       class="mt-8 inline-flex items-center gap-2 px-5 py-2.5 bg-gray-100 hover:bg-gray-200 rounded-xl text-sm text-gray-700 transition">
        <i class="fa-solid fa-arrow-left"></i> Retour
    </a>
</div>

@endsection
