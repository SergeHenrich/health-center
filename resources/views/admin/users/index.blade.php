@extends('layouts.app')
@section('title', 'Utilisateurs')
@section('page-title', 'Gestion des utilisateurs')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm text-gray-500">{{ $users->total() }} utilisateur(s) au total</p>
        </div>
        <a href="{{ route('admin.users.create') }}"
           class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded-xl text-sm text-white">
            <i class="fa-solid fa-user-plus"></i> Nouvel utilisateur
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-6 py-3 font-semibold text-gray-600">Nom</th>
                    <th class="text-left px-6 py-3 font-semibold text-gray-600">Email</th>
                    <th class="text-left px-6 py-3 font-semibold text-gray-600">Rôle</th>
                    <th class="text-center px-6 py-3 font-semibold text-gray-600">Statut</th>
                    <th class="text-center px-6 py-3 font-semibold text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($users as $user)
                <tr class="hover:bg-gray-50/50">
                    <td class="px-6 py-4 font-medium text-gray-800">{{ $user->first_name }} {{ $user->last_name }}</td>
                    <td class="px-6 py-4 text-gray-500">{{ $user->email }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                            {{ $user->getRoleNames()->first() ?? 'Aucun rôle' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $user->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $user->is_active ? 'Actif' : 'Inactif' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center space-x-3">
                        <a href="{{ route('admin.users.edit', $user) }}" class="text-blue-600 hover:text-blue-800">
                            <i class="fa-solid fa-pen"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                        <i class="fa-solid fa-users text-3xl mb-3 block"></i>
                        Aucun utilisateur trouvé.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $users->links() }}
</div>
@endsection
