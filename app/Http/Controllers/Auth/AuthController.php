<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $authService) {}

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $result = $this->authService->login(
            $request->input('username'),
            $request->input('password')
        );

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'))
            ->with('success', "Bienvenue, {$result['user']->getFullName()} !");
    }

    public function logout(): RedirectResponse
    {
        $this->authService->logout();
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    }
}
