<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * UserManagementController
 * 
 * Unified controller for managing Members, Trainers, and Staff
 * with category-based filtering.
 */
class UserManagementController extends BaseController
{
    /**
     * Display a listing of all users (Members, Trainers, Staff) with category tabs.
     */
    public function index(Request $request)
    {
        // Check permissions - user should have at least one of these permissions
        $hasMembersPermission = Auth::user()->hasPermission('members.view') || Auth::user()->isGymAdmin() || Auth::user()->isSuperAdmin();
        $hasTrainersPermission = Auth::user()->hasPermission('trainers.view') || Auth::user()->isGymAdmin() || Auth::user()->isSuperAdmin();
        $hasStaffPermission = Auth::user()->hasPermission('staff.view') || Auth::user()->isGymAdmin() || Auth::user()->isSuperAdmin();
        
        if (!$hasMembersPermission && !$hasTrainersPermission && !$hasStaffPermission) {
            abort(403, 'Unauthorized access.');
        }

        $user = Auth::user();
        $category = $request->get('category', 'members'); // members, trainers, staff

        // Get users based on category
        $query = User::query();
        
        if ($category === 'members') {
            $query->where('role', 'Member');
        } elseif ($category === 'trainers') {
            $query->where('role', 'Trainer');
        } elseif ($category === 'staff') {
            $query->where('role', 'Staff');
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('active', $request->status);
        }
        
        // Filter by search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Apply gym filter
        $users = $this->applyGymFilter($query)->latest()->get();

        // Get counts for tabs
        $membersQuery = User::where('role', 'Member');
        $trainersQuery = User::where('role', 'Trainer');
        $staffQuery = User::where('role', 'Staff');

        $membersCount = $this->applyGymFilter(clone $membersQuery)->count();
        $trainersCount = $this->applyGymFilter(clone $trainersQuery)->count();
        $staffCount = $this->applyGymFilter(clone $staffQuery)->count();

        // If AJAX request, return JSON with table body and counts
        if ($request->expectsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'html' => view('user-management._table-body', compact('users', 'category'))->render(),
                'counts' => [
                    'members' => $membersCount,
                    'trainers' => $trainersCount,
                    'staff' => $staffCount
                ]
            ]);
        }

        return view('user-management.index', compact('users', 'category', 'membersCount', 'trainersCount', 'staffCount'));
    }

    // ==================== API METHODS ====================

    public function apiIndex(Request $request)
    {
        $hasMembersPermission = Auth::user()->hasPermission('members.view') || Auth::user()->isGymAdmin() || Auth::user()->isSuperAdmin();
        $hasTrainersPermission = Auth::user()->hasPermission('trainers.view') || Auth::user()->isGymAdmin() || Auth::user()->isSuperAdmin();
        $hasStaffPermission = Auth::user()->hasPermission('staff.view') || Auth::user()->isGymAdmin() || Auth::user()->isSuperAdmin();
        
        if (!$hasMembersPermission && !$hasTrainersPermission && !$hasStaffPermission) {
            return $this->apiForbidden('Unauthorized access.');
        }

        $category = $request->get('category', 'members');
        $query = User::query();
        
        if ($category === 'members') {
            $query->where('role', 'Member');
        } elseif ($category === 'trainers') {
            $query->where('role', 'Trainer');
        } elseif ($category === 'staff') {
            $query->where('role', 'Staff');
        }
        
        if ($request->filled('status')) {
            $query->where('active', $request->status);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $this->applyGymFilter($query)->latest()->get();

        $membersQuery = User::where('role', 'Member');
        $trainersQuery = User::where('role', 'Trainer');
        $staffQuery = User::where('role', 'Staff');

        $membersCount = $this->applyGymFilter(clone $membersQuery)->count();
        $trainersCount = $this->applyGymFilter(clone $trainersQuery)->count();
        $staffCount = $this->applyGymFilter(clone $staffQuery)->count();

        return $this->apiSuccess([
            'users' => $users,
            'category' => $category,
            'counts' => [
                'members' => $membersCount,
                'trainers' => $trainersCount,
                'staff' => $staffCount
            ]
        ], 'Users retrieved successfully');
    }
}
