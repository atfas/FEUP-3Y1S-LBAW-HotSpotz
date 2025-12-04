<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ProfileController extends Controller
{
    /**
     * Display the user's profile.
     */
    public function show(): View
    {
        $user = Auth::user();
        $this->authorize('view', $user);

        return view('pages.profile', [
            'user' => $user
        ]);
    }

    /**
     * Show the form for editing the profile information.
     */
    public function edit(): View
    {
        $user = Auth::user();
        $this->authorize('update', $user);

        return view('pages.edit_profile', [
            'user' => $user
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $this->authorize('update', $user);

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'regex:/^\+[0-9]{1,3}[0-9]{4,14}$/'],
            'date_of_birth' => ['required', 'date', 'before:today'],
        ]);

        $user->full_name = $validated['full_name'];
        $user->phone = $validated['phone'];
        $user->date_of_birth = $validated['date_of_birth'];
        $user->save();

        return redirect()->route('profile.show')->with('success', 'Profile information updated successfully!');
    }

    /**
     * Show the form for editing the profile picture.
     */
    public function editPicture(): View
    {
        $user = Auth::user();
        $this->authorize('update', $user);

        return view('pages.edit_picture', [
            'user' => $user
        ]);
    }

    /**
     * Update the user's profile picture.
     */
    public function updatePicture(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $this->authorize('update', $user);

        $validated = $request->validate([
            'profile_picture_url' => ['nullable', 'url'],
        ]);

        $user->profile_picture_url = $validated['profile_picture_url'];
        $user->save();

        return redirect()->route('profile.show')->with('success', 'Profile picture updated successfully!');
    }

    /**
     * Delete the user's profile picture.
     */
    public function deletePicture(): RedirectResponse
    {
        $user = Auth::user();
        $this->authorize('update', $user); // Using update policy as it modifies the user model
        
        $user->profile_picture_url = null;
        $user->save();

        return redirect()->route('profile.show')->with('success', 'Profile picture removed successfully!');
    }

    /**
     * Soft delete the user's account.
     */
    public function destroy(): RedirectResponse
    {
        $user = Auth::user();
        $this->authorize('delete', $user);
        
        // Soft delete by setting is_active to false
        $user->is_active = false;
        $user->save();

        // Log out the user
        Auth::logout();

        return redirect()->route('home')->with('success', 'Your account has been deleted successfully.');
    }
}
