<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ProfileController extends Controller
{
    public function show(): View
    {
        $user = Auth::user();
        $user->load('subscription');
        $this->authorize('view', $user);

        return view('pages.profile', [
            'user' => $user,
            'isOwner' => true,
        ]);
    }

    public function showPublic($id): View|RedirectResponse
    {
        $user = User::with('subscription')->where('id', $id)->where('is_active', true)->first();
        
        if (!$user) {
            abort(404);
        }
        
        if (Auth::check() && Auth::id() === $user->id) {
            return redirect()->route('profile.show');
        }

        return view('pages.profile', [
            'user' => $user,
            'isOwner' => false,
        ]);
    }

    public function edit(): View
    {
        $user = Auth::user();
        $this->authorize('update', $user);

        return view('pages.edit_profile', [
            'user' => $user
        ]);
    }

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

    public function editPicture(): View
    {
        $user = Auth::user();
        $this->authorize('update', $user);

        return view('pages.edit_picture', [
            'user' => $user
        ]);
    }

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

    public function deletePicture(): RedirectResponse
    {
        $user = Auth::user();
        $this->authorize('update', $user);
        
        $user->profile_picture_url = null;
        $user->save();

        return redirect()->route('profile.show')->with('success', 'Profile picture removed successfully!');
    }

    public function destroy(): RedirectResponse
    {
        $user = Auth::user();
        $this->authorize('delete', $user);
        
        $user->is_active = false;
        $user->save();

        Auth::logout();

        return redirect()->route('home')->with('success', 'Your account has been deleted successfully.');
    }
}
