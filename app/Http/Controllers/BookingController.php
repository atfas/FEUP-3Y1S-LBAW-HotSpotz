<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\Availability;
use App\Models\Booking;
use App\Models\Notification;
use App\Events\NotificationCreated;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;


class BookingController extends Controller
{
    /**
     * Show the booking form for a specific listing.
     */
    public function show(Listing $listing): View
    {
        // Load the listing with its availabilities
        $listing->load('availabilities');
        
        return view('pages.booking',compact('listing'));
    }
    
    /**
     * Store a new booking in the database.
     */
    public function store(Request $request)
    {
        // Validate the booking input
        $validated = $request->validate([
            'listing_id' => 'required|exists:listings,id',
            'availability_id' => 'required|exists:availability,id',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
            'num_guests' => 'required|integer|min:1',
        ]);
        
        $listing = Listing::findOrFail($validated['listing_id']);
        $availability = Availability::findOrFail($validated['availability_id']);
        
        $checkIn = Carbon::parse($validated['check_in_date']);
        $checkOut = Carbon::parse($validated['check_out_date']);
        
        // min stay
        $stayDuration = $checkIn->diffInDays($checkOut);
        if ($stayDuration < $listing->minimum_stay) {
            return back()->withErrors([
                'check_out_date' => "Min stay is {$listing->minimum_stay} nights."
            ])->withInput();
        }
        
        // max guests
        if ($validated['num_guests'] > $listing->max_occupancy) {
            return back()->withErrors([
                'num_guests' => "Max guests is {$listing->max_occupancy}."
            ])->withInput();
        }
        
        // dates are within availability dates
        $availStart = Carbon::parse($availability->start_date);
        $availEnd = Carbon::parse($availability->end_date);
        
        if ($checkIn->lt($availStart) || $checkOut->gt($availEnd)) {
            return back()->withErrors([
                'check_in_date' => 'dates are outside the available period.'
            ])->withInput();
        }
        
        // bookings on the same days
        $hasOverlap = DB::table('bookings as b')
            ->join('availability as a', 'b.availability_id', '=', 'a.id')
            ->where('a.listing_id', $listing->id)
            ->whereIn('b.status', ['PendingPayment', 'Confirmed'])
            ->where(function($query) use ($checkIn, $checkOut) {
                $query->whereBetween('b.check_in_date', [$checkIn, $checkOut])
                    ->orWhereBetween('b.check_out_date', [$checkIn, $checkOut])
                    ->orWhere(function($q) use ($checkIn, $checkOut) {
                        $q->where('b.check_in_date', '<=', $checkIn)
                          ->where('b.check_out_date', '>=', $checkOut);
                    });
            })
            ->exists();
        
        if ($hasOverlap) {
            return back()->withErrors([
                'check_in_date' => 'dates overlap'
            ])->withInput();
        }
        
        $totalPrice = $stayDuration * $availability->price_per_night;
        
        try {
            DB::beginTransaction();

            // create booking with pending payment
            $booking = Booking::create([
                'check_in_date' => $checkIn->toDateString(),
                'check_out_date' => $checkOut->toDateString(),
                'num_guests' => $validated['num_guests'],
                'status' => 'PendingPayment',
                'total_price' => $totalPrice,
                'availability_id' => $validated['availability_id'],
                'guest_id' => Auth::id(),
            ]);

            // insert a cash transaction for the total and mark it completed
            // use the database CURRENT_TIMESTAMP to avoid PHP/DB clock ordering issues
            DB::table('transactions')->insert([
                'amount' => $totalPrice,
                'transaction_method' => 'Cash',
                'type' => 'FullPayment',
                'status' => 'Completed',
                'paid_at' => DB::raw('CURRENT_TIMESTAMP'),
                'refund_id' => null,
                'booking_id' => $booking->id,
            ]);

            // update booking to confirmed
            $booking->update(['status' => 'Confirmed']);

            // Create notification for guest (booking confirmed)
            $guestNotification = Notification::create([
                'type' => Notification::TYPE_BOOKING_CONFIRMED,
                'content' => "Your booking for '{$listing->title}' has been confirmed! Check-in: {$checkIn->format('M d, Y')}",
                'user_id' => Auth::id(),
                'booking_id' => $booking->id,
            ]);

            // Create notification for host (new booking)
            $hostNotification = Notification::create([
                'type' => Notification::TYPE_BOOKING_CONFIRMED,
                'content' => "New booking received for '{$listing->title}' from {$checkIn->format('M d, Y')} to {$checkOut->format('M d, Y')}",
                'user_id' => $listing->host_id,
                'booking_id' => $booking->id,
            ]);

            DB::commit();

            // Broadcast notifications
            event(new NotificationCreated($guestNotification));
            event(new NotificationCreated($hostNotification));

            return redirect()->route('profile.show')
                ->with('success', 'Booking Successful: payment at arrival');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors([
                'error' => 'Failed to create booking: ' . $e->getMessage()
            ])->withInput();
        }
    }

    /**
     * Display user's bookings.
     */
    public function index(): View
    {
        $perPage = 10;
        
        $bookings = Booking::where('guest_id', Auth::id())
            ->where('status', '!=', 'Deleted')
            ->with(['availability.listing'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $bookings->getCollection()->transform(function ($booking) {
            return [
                'id' => $booking->id,
                'listing_title' => $booking->availability->listing->title,
                'listing_location' => $booking->availability->listing->location_address,
                'check_in_date' => $booking->check_in_date->format('M d, Y'),
                'check_out_date' => $booking->check_out_date->format('M d, Y'),
                'num_guests' => $booking->num_guests,
                'status' => $booking->status,
                'total_price' => number_format($booking->total_price, 2),
                'created_at' => $booking->created_at->format('F d, Y'),
                'raw_check_in' => $booking->check_in_date->format('Y-m-d'),
                'raw_check_out' => $booking->check_out_date->format('Y-m-d'),
            ];
        });

        $totalBookings = Booking::where('guest_id', Auth::id())
            ->where('status', '!=', 'Deleted')
            ->count();
        $activeBookings = Booking::where('guest_id', Auth::id())
            ->whereIn('status', ['PendingPayment', 'Confirmed'])
            ->count();

        return view('pages.bookings', compact('bookings', 'totalBookings', 'activeBookings'));
    }

    /**
     * Show edit form for a booking.
     */
    public function edit(Booking $booking): View
    {
        $this->authorize('update', $booking);

        $booking->load(['availability.listing']);
        
        return view('pages.booking-edit', compact('booking'));
    }

    /**
     * Update a booking.
     */
    public function update(Request $request, Booking $booking)
    {
        $this->authorize('update', $booking);

        $validated = $request->validate([
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
            'num_guests' => 'required|integer|min:1',
        ]);

        $listing = $booking->availability->listing;
        $availability = $booking->availability;
        
        $checkIn = Carbon::parse($validated['check_in_date']);
        $checkOut = Carbon::parse($validated['check_out_date']);
        
        $stayDuration = $checkIn->diffInDays($checkOut);
        if ($stayDuration < $listing->minimum_stay) {
            return back()->withErrors([
                'check_out_date' => "Min stay is {$listing->minimum_stay} nights."
            ])->withInput();
        }
        
        if ($validated['num_guests'] > $listing->max_occupancy) {
            return back()->withErrors([
                'num_guests' => "Max guests is {$listing->max_occupancy}."
            ])->withInput();
        }
        
        $availStart = Carbon::parse($availability->start_date);
        $availEnd = Carbon::parse($availability->end_date);
        
        if ($checkIn->lt($availStart) || $checkOut->gt($availEnd)) {
            return back()->withErrors([
                'check_in_date' => 'Dates are outside the available period.'
            ])->withInput();
        }
        
        $hasOverlap = DB::table('bookings as b')
            ->join('availability as a', 'b.availability_id', '=', 'a.id')
            ->where('a.listing_id', $listing->id)
            ->where('b.id', '!=', $booking->id)
            ->whereIn('b.status', ['PendingPayment', 'Confirmed'])
            ->where(function($query) use ($checkIn, $checkOut) {
                $query->whereBetween('b.check_in_date', [$checkIn, $checkOut])
                    ->orWhereBetween('b.check_out_date', [$checkIn, $checkOut])
                    ->orWhere(function($q) use ($checkIn, $checkOut) {
                        $q->where('b.check_in_date', '<=', $checkIn)
                          ->where('b.check_out_date', '>=', $checkOut);
                    });
            })
            ->exists();
        
        if ($hasOverlap) {
            return back()->withErrors([
                'check_in_date' => 'Dates overlap with another booking.'
            ])->withInput();
        }
        
        $totalPrice = $stayDuration * $availability->price_per_night;
        
        $booking->update([
            'check_in_date' => $checkIn->toDateString(),
            'check_out_date' => $checkOut->toDateString(),
            'num_guests' => $validated['num_guests'],
            'total_price' => $totalPrice,
        ]);
        
        return redirect()->route('bookings.index')
            ->with('success', 'Booking updated successfully!');
    }

    /**
     * Delete a booking.
     */
    public function destroy(Booking $booking)
    {
        $this->authorize('delete', $booking);

        // Load relationships for notification content
        $booking->load('availability.listing');
        $listing = $booking->availability->listing;
        $hostId = $listing->host_id;
        $guestId = $booking->guest_id;

        // Create cancellation notification for host
        $hostNotification = Notification::create([
            'type' => Notification::TYPE_BOOKING_CANCELED,
            'content' => "Booking for '{$listing->title}' ({$booking->check_in_date->format('M d, Y')} - {$booking->check_out_date->format('M d, Y')}) has been cancelled.",
            'user_id' => $hostId,
            'booking_id' => $booking->id,
        ]);

        // If the booking was cancelled by host, notify guest
        if (Auth::id() !== $guestId) {
            $guestNotification = Notification::create([
                'type' => Notification::TYPE_BOOKING_CANCELED,
                'content' => "Your booking for '{$listing->title}' has been cancelled.",
                'user_id' => $guestId,
                'booking_id' => $booking->id,
            ]);
            event(new NotificationCreated($guestNotification));
        }

        // Broadcast to host
        event(new NotificationCreated($hostNotification));

        // Soft delete?
        $booking->update(['status' => 'Deleted']);
        
        return redirect()->route('bookings.index')
            ->with('success', 'Booking cancelled successfully.');
    }
}