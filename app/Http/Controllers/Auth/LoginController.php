<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle a login request.
     * 
     * For web requests: Uses session-based authentication
     * For API requests: Returns token-based authentication
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        // Attempt web session login
        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            $user = Auth::user();
            
            // Validate user is active
            if (!$user->active) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Your account has been deactivated. Please contact administrator.',
                ]);
            }

            // Validate gym is active (for non-super-admin)
            if (!$user->isSuperAdmin() && $user->gym && !$user->gym->isActive()) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Your gym account has been deactivated. Please contact administrator.',
                ]);
            }

            // For API requests, return token
            if ($request->expectsJson() || $request->is('api/*')) {
                $token = $user->createToken('auth-token')->plainTextToken;
                
                return response()->json([
                    'success' => true,
                    'message' => 'Login successful',
                    'token' => $token,
                    'token_type' => 'Bearer',
                    'data' => [
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
                        ]
                    ]
                ]);
            }

            // For web requests, redirect to dashboard
            return redirect()->intended(route('dashboard'));
        }

        throw ValidationException::withMessages([
            'email' => ['The provided credentials do not match our records.'],
        ]);
    }

    /**
     * Log the user out.
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        
        // Revoke all tokens if user exists
        if ($user) {
            $user->tokens()->delete();
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully.'
            ]);
        }

        return redirect()->route('login');
    }
}
