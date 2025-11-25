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
        return view('pages.profile', [
            'user' => Auth::user()
        ]);
    }

    /**
     * Show the form for editing the profile information.
     */
    public function edit(): View
    {
        return view('pages.edit_profile', [
            'user' => Auth::user()
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

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
        return view('pages.edit_picture', [
            'user' => Auth::user()
        ]);
    }

    /**
     * Update the user's profile picture.
     */
    public function updatePicture(Request $request): RedirectResponse
    {
        $user = Auth::user();

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
        $user->profile_picture_url = null;
        $user->save();

        return redirect()->route('profile.show')->with('success', 'Profile picture removed successfully!');
    }
}
