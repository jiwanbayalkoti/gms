<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CheckRole Middleware
 * 
 * Validates that the authenticated user has one of the required roles.
 * 
 * Usage in routes:
 * Route::middleware(['auth', 'role:GymAdmin,Trainer'])->group(...)
 */
class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  Comma-separated list of allowed roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Check if the user is authenticated
        if (!$request->user()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login');
        }

        $user = $request->user();

        // Check if user account is active
        if (!$user->active) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Your account has been deactivated.'], 403);
            }
            auth()->logout();
            return redirect()->route('login')
                ->withErrors(['email' => 'Your account has been deactivated.']);
        }

        // If no roles are specified, allow the user to proceed
        if (empty($roles)) {
            return $next($request);
        }

        // Flatten roles array (in case of comma-separated string)
        $allowedRoles = [];
        foreach ($roles as $role) {
            $allowedRoles = array_merge($allowedRoles, explode(',', $role));
        }
        $allowedRoles = array_map('trim', $allowedRoles);

        // Check if the user has any of the required roles
        if (in_array($user->role, $allowedRoles)) {
            return $next($request);
        }

        // If the user doesn't have any of the required roles
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'You do not have permission to access this resource.',
                'required_roles' => $allowedRoles,
                'your_role' => $user->role,
            ], 403);
        }

        return redirect()->route('dashboard')
            ->with('error', 'You do not have permission to access this page.');
    }
}
