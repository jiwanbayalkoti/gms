<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * BaseController
 * 
 * Provides common functionality for all controllers in the multi-tenant system.
 * Includes gym filtering and authorization helpers.
 */
abstract class BaseController extends Controller
{
    /**
     * Get the current user's effective gym_id.
     * Returns null for SuperAdmin (can access all gyms).
     * Returns gym_id for other users.
     */
    protected function getEffectiveGymId(): ?int
    {
        $user = Auth::user();
        
        if (!$user) {
            return null;
        }

        return $user->getEffectiveGymId();
    }

    /**
     * Apply gym filter to a query.
     * SuperAdmin sees all data, others see only their gym's data.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $gymIdColumn Column name for gym_id (default: 'gym_id')
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applyGymFilter($query, string $gymIdColumn = 'gym_id')
    {
        $gymId = $this->getEffectiveGymId();

        // If gym_id is null (SuperAdmin), don't filter - return all records
        if ($gymId === null) {
            return $query;
        }

        // Filter by gym_id for other users
        return $query->where($gymIdColumn, $gymId);
    }

    /**
     * Validate that the user can access the specified gym.
     * 
     * @param int|null $gymId
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function validateGymAccess(?int $gymId): void
    {
        $user = Auth::user();

        if (!$user) {
            abort(401, 'Unauthenticated.');
        }

        if (!$user->canAccessGym($gymId)) {
            abort(403, 'You do not have access to this gym.');
        }
    }

    /**
     * Get the current user.
     */
    protected function getCurrentUser()
    {
        return Auth::user();
    }

    /**
     * Check if current user has a specific permission.
     */
    protected function hasPermission(string $permission): bool
    {
        $user = $this->getCurrentUser();
        
        if (!$user) {
            return false;
        }

        return $user->hasPermission($permission);
    }

    /**
     * Authorize a permission. Throws 403 if user doesn't have permission.
     * SuperAdmin bypasses all permission checks.
     */
    protected function authorizePermission(string $permission): void
    {
        $user = $this->getCurrentUser();
        
        // SuperAdmin bypasses all permission checks
        if ($user && $user->isSuperAdmin()) {
            return;
        }
        
        if (!$this->hasPermission($permission)) {
            abort(403, "You do not have permission to perform this action: {$permission}");
        }
    }

    /**
     * Return a successful API response.
     */
    protected function apiSuccess($data = null, string $message = 'Operation successful', int $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    /**
     * Return an error API response.
     */
    protected function apiError(string $message = 'An error occurred', $errors = null, int $code = 400)
    {
        $response = [
            'success' => false,
            'message' => $message
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    /**
     * Return a validation error API response.
     */
    protected function apiValidationError($errors, string $message = 'Validation failed')
    {
        return $this->apiError($message, $errors, 422);
    }

    /**
     * Return a not found API response.
     */
    protected function apiNotFound(string $message = 'Resource not found')
    {
        return $this->apiError($message, null, 404);
    }

    /**
     * Return an unauthorized API response.
     */
    protected function apiUnauthorized(string $message = 'Unauthorized')
    {
        return $this->apiError($message, null, 401);
    }

    /**
     * Return a forbidden API response.
     */
    protected function apiForbidden(string $message = 'Forbidden')
    {
        return $this->apiError($message, null, 403);
    }
}

