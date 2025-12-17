<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    public function create(Booking $booking)
    {
        // Check if user is the guest of this booking
        if ($booking->guest_id !== Auth::id()) {
            abort(403, 'You can only review your own bookings.');
        }

        // Check if booking is completed
        if ($booking->status !== 'Completed') {
            return redirect()->route('bookings.index')
                ->with('error', 'You can only review completed bookings.');
        }

        // Check if already reviewed
        if ($booking->review) {
            return redirect()->route('bookings.index')
                ->with('error', 'You have already reviewed this booking.');
        }

        // Check if check-out date has passed
        if ($booking->check_out_date >= now()) {
            return redirect()->route('bookings.index')
                ->with('error', 'You can only review after your stay is complete.');
        }

        // Check if within 2 days of check-out
        if ($booking->check_out_date->addDays(2) < now()) {
            return redirect()->route('bookings.index')
                ->with('error', 'Review period has expired. You must review within 2 days of check-out.');
        }

        $booking->load(['availability.listing']);

        return view('pages.addReview', compact('booking'));
    }

    public function store(Request $request, Booking $booking)
    {
        // Validate ownership
        if ($booking->guest_id !== Auth::id()) {
            abort(403, 'You can only review your own bookings.');
        }

        // Validate booking status and timing
        if ($booking->status !== 'Completed') {
            return back()->with('error', 'You can only review completed bookings.');
        }

        if ($booking->review) {
            return back()->with('error', 'You have already reviewed this booking.');
        }

        if ($booking->check_out_date >= now()) {
            return back()->with('error', 'You can only review after your stay is complete.');
        }

        if ($booking->check_out_date->addDays(2) < now()) {
            return back()->with('error', 'Review period has expired.');
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $listing = $booking->availability->listing;

        DB::transaction(function () use ($validated, $booking, $listing) {
            Review::create([
                'rating' => $validated['rating'],
                'comment' => $validated['comment'],
                'guest_id' => Auth::id(),
                'host_id' => $listing->host_id,
                'booking_id' => $booking->id,
            ]);
        });

        return redirect()->route('bookings.index')
            ->with('success', 'Review submitted successfully!');
    }

    public function edit(Review $review)
    {
        // Check if user is the author of this review
        if ($review->guest_id !== Auth::id()) {
            abort(403, 'You can only edit your own reviews.');
        }

        // Check if within edit period (2 days from creation)
        if ($review->created_at->addDays(2) < now()) {
            return redirect()->route('bookings.index')
                ->with('error', 'Review edit period has expired.');
        }

        $review->load('booking.availability.listing');

        return view('pages.editReview', compact('review'));
    }

    public function update(Request $request, Review $review)
    {
        // Validate ownership
        if ($review->guest_id !== Auth::id()) {
            abort(403, 'You can only edit your own reviews.');
        }

        // Check edit period
        if ($review->created_at->addDays(2) < now()) {
            return back()->with('error', 'Review edit period has expired.');
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $review->update($validated);

        return redirect()->route('bookings.index')
            ->with('success', 'Review updated successfully!');
    }

    public function destroy(Review $review)
    {
        // Validate ownership
        if ($review->guest_id !== Auth::id()) {
            abort(403, 'You can only delete your own reviews.');
        }

        // Check if within deletion period
        if ($review->created_at->addDays(2) < now()) {
            return back()->with('error', 'Review deletion period has expired.');
        }

        $review->delete();

        return redirect()->route('bookings.index')
            ->with('success', 'Review deleted successfully!');
    }
}