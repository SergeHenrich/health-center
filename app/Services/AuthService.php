<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    private const MAX_ATTEMPTS = 5;
    private const LOCKOUT_MINUTES = 15;

    /**
     * @throws ValidationException
     */
    public function login(string $username, string $password): array
    {
        $user = User::where('username', $username)
            ->orWhere('email', $username)
            ->active()
            ->first();

        if ($user && $user->isLocked()) {
            throw ValidationException::withMessages([
                'username' => [
                    'Compte temporairement verrouillé après ' . self::MAX_ATTEMPTS
                        . ' tentatives. Réessayez dans ' . $user->locked_until->diffInMinutes(now()) . ' minutes.',
                ],
            ]);
        }

        if (!$user || !Hash::check($password, $user->password)) {
            if ($user) {
                $this->incrementFailedAttempts($user);
            }
            throw ValidationException::withMessages([
                'username' => ['Les identifiants sont incorrects.'],
            ]);
        }

        $user->forceFill([
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ])->save();

        Auth::login($user);

        $user->recordLogin();

        return [
            'user'  => $user->load('roles'),
            'token' => null,
        ];
    }

    public function logout(): void
    {
    }

    public function createUser(array $data): User
    {
        $user = User::create([
            'first_name' => $data['first_name'],
            'last_name'  => $data['last_name'],
            'email'      => $data['email'],
            'username'   => $data['username'],
            'password'   => Hash::make($data['password']),
            'phone'      => $data['phone'] ?? null,
            'is_active'  => true,
        ]);

        if (!empty($data['role'])) {
            $user->assignRole($data['role']);
        }

        return $user;
    }

    public function changePassword(User $user, string $newPassword): void
    {
        $user->update(['password' => Hash::make($newPassword)]);
        $user->tokens()->delete();
    }

    private function incrementFailedAttempts(User $user): void
    {
        $attempts = $user->failed_login_attempts + 1;

        if ($attempts >= self::MAX_ATTEMPTS) {
            $user->forceFill([
                'failed_login_attempts' => $attempts,
                'locked_until' => now()->addMinutes(self::LOCKOUT_MINUTES),
            ])->save();
        } else {
            $user->forceFill(['failed_login_attempts' => $attempts])->save();
        }
    }
}
