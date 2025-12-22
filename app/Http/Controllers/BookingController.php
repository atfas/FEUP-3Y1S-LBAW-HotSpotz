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

        // Fetch active bookings for this listing to block dates on the calendar
        $bookingsCollection = Booking::whereHas('availability', function ($query) use ($listing) {
            $query->where('listing_id', $listing->id);
        })->whereIn('status', ['Confirmed', 'PendingPayment'])->get();

        $bookings = $bookingsCollection->map(function ($booking) {
            return [
                'id' => $booking->id,
                'start_date' => $booking->check_in_date,
                'end_date' => $booking->check_out_date,
                'status' => $booking->status,
            ];
        });
        
        $availabilities = $listing->availabilities->map(function($r){ return [
            'id' => $r->id ?? null,
            'start_date' => $r->start_date,
            'end_date' => $r->end_date,
            'price_per_night' => (float)$r->price_per_night,
            'check_in_time' => $r->check_in_time ?? null,
            'check_out_time' => $r->check_out_time ?? null,
        ]; })->all();

        return view('pages.booking',compact('listing', 'bookings', 'availabilities'));
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
            'payment_method' => 'nullable|string|in:pay_on_arrival,credit_card,bank_transfer',
            'bank_reference' => 'nullable|string',
            'cc_name' => 'nullable|string',
            'cc_number' => 'nullable|string',
            'cc_expiry' => 'nullable|string',
            'cc_cvc' => 'nullable|string',
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

        // Apply premium discount if applicable
        if (Auth::check() && Auth::user()->isPremium()) {
            $discount = Auth::user()->getDiscountPercentage();
            $totalPrice = $totalPrice * (1 - $discount / 100);
        }
        
        $pm = $validated['payment_method'] ?? null;

        try {
            DB::beginTransaction();

            $booking = Booking::create([
                'check_in_date' => $checkIn->toDateString(),
                'check_out_date' => $checkOut->toDateString(),
                'num_guests' => $validated['num_guests'],
                'status' => 'PendingPayment',
                'total_price' => $totalPrice,
                'availability_id' => $validated['availability_id'],
                'guest_id' => Auth::id(),
            ]);

            // Handle different payment methods
            if ($pm === 'credit_card') {
                DB::rollBack();
                return back()->withErrors([
                    'error' => 'Credit card payments should be processed through the payment form.'
                ])->withInput();
                    
            } elseif ($pm === 'bank_transfer') {
                $transactionMethod = 'Bank Transfer';
                $bankRef = $validated['bank_reference'] ?? null;
                if (!$bankRef) {
                    DB::rollBack();
                    return back()->withErrors(['error' => 'Bank reference missing'])->withInput();
                }

                DB::table('transactions')->insert([
                    'amount' => $totalPrice,
                    'transaction_method' => $transactionMethod,
                    'type' => 'FullPayment',
                    'status' => 'Pending',
                    'paid_at' => null,
                    'refund_id' => null,
                    'booking_id' => $booking->id,
                ]);
            } else {
                $transactionMethod = 'Cash';
                
                DB::table('transactions')->insert([
                    'amount' => $totalPrice,
                    'transaction_method' => $transactionMethod,
                    'type' => 'FullPayment',
                    'status' => 'Completed',
                    'paid_at' => DB::raw('CURRENT_TIMESTAMP'),
                    'refund_id' => null,
                    'booking_id' => $booking->id,
                ]);

                $booking->update(['status' => 'Confirmed']);
            }

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

            return redirect()->route('profile.show')->with('success', 'Booking Successful');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors([
                'error' => 'Failed to create booking: ' . $e->getMessage()
            ])->withInput();
        }
    }

    /**
     * AJAX: validate booking input before proceeding to payment
     */
    public function validateBooking(Request $request)
    {
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

        $errors = [];

        $stayDuration = $checkIn->diffInDays($checkOut);
        if ($stayDuration < $listing->minimum_stay) {
            $errors['check_out_date'] = "Min stay is {$listing->minimum_stay} nights.";
        }

        if ($validated['num_guests'] > $listing->max_occupancy) {
            $errors['num_guests'] = "Max guests is {$listing->max_occupancy}.";
        }

        $availStart = Carbon::parse($availability->start_date);
        $availEnd = Carbon::parse($availability->end_date);
        if ($checkIn->lt($availStart) || $checkOut->gt($availEnd)) {
            $errors['check_in_date'] = 'Dates are outside the available period.';
        }

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
            $errors['check_in_date'] = 'Dates overlap with an existing booking.';
        }

        if (!empty($errors)) {
            return response()->json(['ok' => false, 'errors' => $errors], 422);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Display user's bookings.
     */
    public function index(): View
    {
        $perPage = 10;
        
        $bookings = Booking::where('guest_id', Auth::id())
            ->where('status', '!=', 'Deleted')
            ->with(['availability.listing', 'review'])
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
                'has_review' => $booking->review !== null,
                'review_id' => $booking->review?->id,
                'review_created_at' => $booking->review?->created_at?->format('Y-m-d H:i:s'),
                'check_out_date_raw' => $booking->check_out_date,
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
     * Show the payment form for a specific pending booking.
     */
    public function showPaymentForm(Booking $booking): View
    {
        $this->authorize('view', $booking);

        if ($booking->status !== 'PendingPayment') {
            return redirect()->route('bookings.index')->with('error', 'This booking does not have a pending payment.');
        }

        $booking->load('availability.listing');

        return view('pages.booking-pay', compact('booking'));
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

        if ($booking->guest_id === Auth::id() && Auth::user()->isPremium()) {
            $discount = Auth::user()->getDiscountPercentage();
            $totalPrice = $totalPrice * (1 - $discount / 100);
        }
        
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

    /**
     * Host rejects a confirmed booking and issues refund transaction.
     * Only the listing host may perform this action.
     */
    public function hostReject($bookingId)
    {
        $userId = Auth::id();

        $booking = Booking::find($bookingId);
        if (!$booking) return redirect()->back()->with('error', 'Booking not found.');

        // load availability -> listing to verify host
        $availability = DB::table('availability')->where('id', $booking->availability_id)->first();
        if (!$availability) return redirect()->back()->with('error', 'Associated availability not found.');

        $listing = DB::table('listings')->where('id', $availability->listing_id)->first();
        if (!$listing) return redirect()->back()->with('error', 'Associated listing not found.');

        if ($listing->host_id !== $userId) {
            return redirect()->back()->with('error', 'Only the listing host can reject bookings.');
        }

        // compute hours until start
        $now = Carbon::now();
        $start = Carbon::parse($booking->check_in_date)->startOfDay();
        if ($now->greaterThanOrEqualTo($start)) {
            $hours = 0;
        } else {
            $hours = $now->diffInHours($start);
        }

        $thresholdHours = 48; // 48 hours (2 days) cutoff
        if ($hours < $thresholdHours) {
            return redirect()->back()->with('error', "Cannot delete a reservation within {$thresholdHours} hours of its start.");
        }

        try {
            DB::beginTransaction();

            DB::table('bookings')->where('id', $booking->id)->update(['status' => 'Rejected']);

            $lastTx = DB::table('transactions')
                ->where('booking_id', $booking->id)
                ->where('status', 'Completed')
                ->orderByDesc('paid_at')
                ->lockForUpdate()
                ->first();

            if (!$lastTx) {
                DB::rollBack();
                return redirect()->back()->with('error', 'No completed payment found for this booking to refund.');
            }

            $refundId = DB::table('transactions')->insertGetId([
                'amount' => $lastTx->amount,
                'transaction_method' => $lastTx->transaction_method,
                'type' => 'Refund',
                'status' => 'Completed',
                'paid_at' => $lastTx->paid_at,
                'refund_id' => $lastTx->id,
                'booking_id' => $booking->id,
            ]);

            DB::table('transactions')->where('id', $lastTx->id)->update(['status' => 'Refunded']);

            DB::commit();

            return redirect()->back()->with('success', 'Booking rejected and refund processed.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to process refund: ' . $e->getMessage());
        }
    }

    /**
     * Host confirms a pending booking after receiving an external payment (e.g., bank transfer).
     */
    public function hostConfirmPayment($bookingId)
    {
        $userId = Auth::id();

        $booking = Booking::find($bookingId);
        if (!$booking) return redirect()->back()->with('error', 'Booking not found.');

        // load availability -> listing to verify host
        $availability = DB::table('availability')->where('id', $booking->availability_id)->first();
        if (!$availability) return redirect()->back()->with('error', 'Associated availability not found.');

        $listing = DB::table('listings')->where('id', $availability->listing_id)->first();
        if (!$listing) return redirect()->back()->with('error', 'Associated listing not found.');

        if ($listing->host_id !== $userId) {
            return redirect()->back()->with('error', 'Only the listing host can confirm payments.');
        }

        if ($booking->status !== 'PendingPayment') {
            return redirect()->back()->with('error', 'Booking is not pending payment.');
        }

        try {
            DB::beginTransaction();

            // Find a pending transaction (bank transfer) and mark it completed
            $pendingTx = DB::table('transactions')
                ->where('booking_id', $booking->id)
                ->where('status', 'Pending')
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            if (!$pendingTx) {
                DB::rollBack();
                return redirect()->back()->with('error', 'No pending transaction found for this booking to confirm.');
            }

            // Mark transaction as completed and set paid_at
            DB::table('transactions')->where('id', $pendingTx->id)->update([
                'status' => 'Completed',
                'paid_at' => DB::raw('CURRENT_TIMESTAMP'),
            ]);

            // Update booking status to Confirmed
            DB::table('bookings')->where('id', $booking->id)->update(['status' => 'Confirmed']);

            DB::commit();

            return redirect()->back()->with('success', 'Payment confirmed and booking updated.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to confirm payment: ' . $e->getMessage());
        }
    }
}