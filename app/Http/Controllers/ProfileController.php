<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
     * Show host reservations calendar for the authenticated host.
     */
    public function hostReservations()
    {
        $target = Auth::user();
        if (!$target) abort(404);

        // authorize the action (policy should ensure only hosts can view their calendar)
        $this->authorize('viewHostReservations', $target);

        // require that the target is a host (approved)
        if (($target->application_status ?? null) !== 'Approved') {
            return redirect()->back()->with('error', 'User is not a verified host.');
        }

        $since = $target->verified_at ?? $target->registration_date ?? null;
        if (!$since) {
            $since = Carbon::now()->subYears(10)->toDateString();
        }

        $bookings = DB::table('bookings')
            ->join('availability', 'bookings.availability_id', '=', 'availability.id')
            ->join('listings', 'availability.listing_id', '=', 'listings.id')
            ->join('users as guests', 'bookings.guest_id', '=', 'guests.id')
            ->where('listings.host_id', $target->id)
            ->whereIn('bookings.status', ['Confirmed', 'Completed'])
            ->whereDate('bookings.check_out_date', '>=', $since)
            ->select(
                'bookings.id',
                'bookings.check_in_date',
                'bookings.check_out_date',
                'bookings.status',
                'bookings.total_price',
                'listings.id as listing_id',
                'listings.title as listing_title',
                'guests.full_name as guest_name'
            )
            ->orderBy('bookings.check_in_date')
            ->get();

        $events = $bookings->map(function($b){
            return [
                'id' => $b->id,
                'title' => $b->listing_title . ' — ' . $b->guest_name,
                'start' => $b->check_in_date,
                'end' => Carbon::parse($b->check_out_date)->addDay()->toDateString(),
                'extendedProps' => [
                    'status' => $b->status,
                    'listing_id' => $b->listing_id,
                    'total_price' => (float)$b->total_price,
                ],
            ];
        })->toArray();

        return view('pages.host_reservations_calendar', [
            'target' => $target,
            'events' => $events,
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
}
