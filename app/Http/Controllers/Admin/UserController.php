<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct(private readonly AuthService $authService) {}

    public function index(Request $request): View
    {
        $users = User::with('roles')
            ->when($request->q, fn($q) => $q->where('first_name', 'ilike', "%{$request->q}%")
                ->orWhere('last_name', 'ilike', "%{$request->q}%")
                ->orWhere('email', 'ilike', "%{$request->q}%"))
            ->when($request->role, fn($q) => $q->role($request->role))
            ->latest()
            ->paginate(20);

        $roles = Role::orderBy('name')->get();

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function create(): View
    {
        $roles = Role::orderBy('name')->get();
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'email'      => 'required|email|unique:users,email',
            'username'   => 'required|string|min:3|unique:users,username',
            'password'   => 'required|string|min:8|confirmed',
            'phone'      => 'nullable|string|max:20',
            'role'       => 'required|exists:roles,name',
        ], [
            'email.unique'    => 'Cet email est déjà utilisé.',
            'username.unique' => 'Ce nom d\'utilisateur est déjà pris.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ]);

        $user = $this->authService->createUser($validated);

        return redirect()->route('admin.users.index')
            ->with('success', "Utilisateur {$user->getFullName()} créé.");
    }

    public function edit(User $user): View
    {
        $roles = Role::orderBy('name')->get();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'phone'      => 'nullable|string|max:20',
            'is_active'  => 'boolean',
            'role'       => 'required|exists:roles,name',
        ]);

        $user->update($validated);
        $user->syncRoles([$validated['role']]);

        return back()->with('success', 'Utilisateur mis à jour.');
    }

    public function toggleActive(User $user): RedirectResponse
    {
        $user->is_active ? $user->deactivate() : $user->activate();
        $status = $user->fresh()->is_active ? 'activé' : 'désactivé';
        return back()->with('success', "Compte {$status}.");
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $this->authService->changePassword($user, $validated['password']);

        return back()->with('success', 'Mot de passe réinitialisé.');
    }
}
