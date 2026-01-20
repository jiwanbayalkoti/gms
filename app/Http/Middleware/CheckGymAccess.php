<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CheckGymAccess Middleware
 * 
 * Ensures that users can only access data from their own gym.
 * SuperAdmin can access all gyms.
 * 
 * Usage in routes:
 * Route::middleware(['auth', 'gym.access'])->group(...)
 */
class CheckGymAccess
{
    /**
     * Handle an incoming request.
     * 
     * Validates that the user has access to the gym_id in the request.
     * For SuperAdmin, this check is bypassed.
     * 
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // SuperAdmin can access all gyms - bypass check
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Get gym_id from request (route parameter, query string, or request body)
        $requestGymId = $this->getGymIdFromRequest($request);

        // If no gym_id in request, use user's gym_id (for filtering)
        if ($requestGymId === null) {
            return $next($request);
        }

        // Validate that user can access the requested gym
        if (!$user->canAccessGym($requestGymId)) {
            abort(403, 'You do not have access to this gym.');
        }

        return $next($request);
    }

    /**
     * Extract gym_id from request.
     * Checks route parameters, query string, and request body.
     */
    private function getGymIdFromRequest(Request $request): ?int
    {
        // Check route parameters
        if ($request->route('gym_id')) {
            return (int) $request->route('gym_id');
        }

        // Check query string
        if ($request->has('gym_id')) {
            return (int) $request->query('gym_id');
        }

        // Check request body
        if ($request->has('gym_id')) {
            return (int) $request->input('gym_id');
        }

        return null;
    }
}

