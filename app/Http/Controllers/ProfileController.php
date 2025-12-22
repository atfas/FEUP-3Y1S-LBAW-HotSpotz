<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
            ->whereIn('bookings.status', ['Confirmed', 'Completed', 'PendingPayment'])
            ->whereDate('bookings.check_out_date', '>=', $since)
            ->select(
                'bookings.id',
                'bookings.check_in_date',
                'bookings.check_out_date',
                'bookings.status',
                'bookings.total_price',
                'listings.id as listing_id',
                'listings.title as listing_title',
                'guests.full_name as guest_name',
                DB::raw("(SELECT COUNT(1) FROM transactions t WHERE t.booking_id = bookings.id AND t.status = 'Pending' AND (t.user_id IS NULL OR t.user_id = listings.host_id)) as pending_txn_count")
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

    /**
     * Show host listing statistics dashboard.
     */
    public function hostStatistics()
    {
        $host = Auth::user();
        $this->authorize('viewHostReservations', $host);

        if (($host->application_status ?? null) !== 'Approved') {
            return redirect()->back()->with('error', 'User is not a verified host.');
        }

        // Per-listing stats
        $listings = DB::table('listings')
            ->where('listings.host_id', $host->id)
            ->where('listings.is_active', true)
            ->leftJoin('availability', 'availability.listing_id', '=', 'listings.id')
            ->leftJoin('bookings', function ($join) {
                $join->on('bookings.availability_id', '=', 'availability.id')
                    ->whereIn('bookings.status', ['Confirmed', 'Completed']);
            })
            ->select(
                'listings.id',
                'listings.title',
                DB::raw('COUNT(DISTINCT bookings.id) AS total_bookings'),
                DB::raw('COALESCE(SUM(bookings.total_price), 0) AS total_revenue'),
                'listings.average_rating',
                'listings.num_reviews'
            )
            ->groupBy('listings.id')
            ->orderBy('total_revenue', 'DESC')
            ->get();

        // Global stats
        $summary = [
            'total_listings' => $listings->count(),
            'total_bookings' => $listings->sum('total_bookings'),
            'total_revenue' => $listings->sum('total_revenue'),
            'average_rating' => round(
                $listings->where('num_reviews', '>', 0)->avg('average_rating') ?? 0,
                2
            ),
        ];

        return view('pages.host_statistics', [
            'host' => $host,
            'listings' => $listings,
            'summary' => $summary,
        ]);
    }

    public function hostDashboard()
    {
        return view('pages.host_dashboard');
    }
}