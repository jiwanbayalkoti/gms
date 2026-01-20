<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends BaseController
{
    /**
     * Show the profile edit form.
     */
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    /**
     * Update the user's profile.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'current_password' => 'nullable|required_with:password',
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        // Update basic information
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'] ?? null;

        // Update password if provided
        if ($request->filled('password')) {
            // Verify current password
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'The current password is incorrect.'])->withInput();
            }

            $user->password = Hash::make($validated['password']);
        }

        // Handle profile photo upload with enhanced security
        if ($request->hasFile('profile_photo')) {
            $request->validate([
                'profile_photo' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            $file = $request->file('profile_photo');
            
            // Enhanced file type validation
            $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
            $allowedExtensions = ['jpeg', 'png', 'jpg', 'gif'];
            
            if (!validate_file_type($file, $allowedMimes, $allowedExtensions)) {
                return back()->withErrors(['profile_photo' => 'Invalid file type. Only JPEG, PNG, JPG, and GIF images are allowed.'])->withInput();
            }

            // Delete old photo if exists
            if ($user->profile_photo) {
                $oldPhotoPath = public_path('storage/' . $user->profile_photo);
                if (file_exists($oldPhotoPath)) {
                    @unlink($oldPhotoPath);
                }
            }

            // Sanitize filename and store
            $originalName = sanitize_filename($file->getClientOriginalName());
            $fileName = 'profile_' . $user->id . '_' . time() . '_' . $originalName;
            $path = $file->storeAs('profile-photos', $fileName, 'public');
            $user->profile_photo = $path;
        }

        $user->save();

        return redirect()->route('profile.edit')->with('success', 'Profile updated successfully.');
    }

    // ==================== API METHODS ====================

    public function apiShow(Request $request)
    {
        $user = Auth::user();
        return $this->apiSuccess($user, 'Profile retrieved successfully');
    }

    public function apiEdit(Request $request)
    {
        $user = Auth::user();
        return $this->apiSuccess($user, 'Profile edit data retrieved successfully');
    }

    public function apiUpdate(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'current_password' => 'nullable|required_with:password',
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'] ?? null;

        if ($request->filled('password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return $this->apiValidationError(['current_password' => ['The current password is incorrect.']], 'Current password is incorrect');
            }
            $user->password = Hash::make($validated['password']);
        }

        if ($request->hasFile('profile_photo')) {
            $request->validate([
                'profile_photo' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            $file = $request->file('profile_photo');
            $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
            $allowedExtensions = ['jpeg', 'png', 'jpg', 'gif'];
            
            if (!validate_file_type($file, $allowedMimes, $allowedExtensions)) {
                return $this->apiValidationError(['profile_photo' => ['Invalid file type']], 'Invalid file type');
            }

            if ($user->profile_photo) {
                $oldPhotoPath = public_path('storage/' . $user->profile_photo);
                if (file_exists($oldPhotoPath)) {
                    @unlink($oldPhotoPath);
                }
            }

            $originalName = sanitize_filename($file->getClientOriginalName());
            $fileName = 'profile_' . $user->id . '_' . time() . '_' . $originalName;
            $path = $file->storeAs('profile-photos', $fileName, 'public');
            $user->profile_photo = $path;
        }

        $user->save();

        return $this->apiSuccess($user, 'Profile updated successfully');
    }
}
