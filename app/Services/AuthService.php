<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * AuthService
 * 
 * Handles authentication logic including token generation and user data retrieval.
 * Uses Laravel Sanctum for token-based authentication.
 */
class AuthService
{
    /**
     * Authenticate user and return token with user data.
     * 
     * @param string $email
     * @param string $password
     * @param bool $remember
     * @return array
     * @throws ValidationException
     */
    public function login(string $email, string $password, bool $remember = false): array
    {
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials do not match our records.'],
            ]);
        }

        // Check if user is active
        if (!$user->active) {
            throw ValidationException::withMessages([
                'email' => ['Your account has been deactivated. Please contact administrator.'],
            ]);
        }

        // Check if gym is active (for non-super-admin users)
        if (!$user->isSuperAdmin() && $user->gym && !$user->gym->isActive()) {
            throw ValidationException::withMessages([
                'email' => ['Your gym account has been deactivated. Please contact administrator.'],
            ]);
        }

        // Create token for API authentication (Sanctum)
        $token = $user->createToken('auth-token')->plainTextToken;

        // Return user data with token
        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'gym_id' => $user->gym_id,
                'gym' => $user->gym ? [
                    'id' => $user->gym->id,
                    'name' => $user->gym->name,
                    'status' => $user->gym->status,
                ] : null,
                'permissions' => $user->getPermissions(),
            ],
            'token' => $token,
            'token_type' => 'Bearer',
        ];
    }

    /**
     * Get authenticated user data with permissions.
     * 
     * @return array
     */
    public function getUserData(): array
    {
        $user = Auth::user();

        if (!$user) {
            return [];
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'gym_id' => $user->gym_id,
            'gym' => $user->gym ? [
                'id' => $user->gym->id,
                'name' => $user->gym->name,
                'status' => $user->gym->status,
            ] : null,
            'permissions' => $user->getPermissions(),
        ];
    }

    /**
     * Logout user and revoke tokens.
     * 
     * @param User|null $user
     * @return void
     */
    public function logout(?User $user = null): void
    {
        $user = $user ?? Auth::user();

        if ($user) {
            // Revoke all tokens for the user
            $user->tokens()->delete();
        }

        // Also logout from web session
        Auth::logout();
    }
}

