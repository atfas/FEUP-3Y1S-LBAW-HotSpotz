<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\Availability;
use App\Models\Booking;
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
        
        $originalPrice = $stayDuration * $availability->price_per_night;
        $totalPrice = $originalPrice;
        $savings = 0;

        // Apply premium discount if applicable
        if (Auth::check() && Auth::user()->isPremium()) {
            $discount = Auth::user()->getDiscountPercentage();
            $totalPrice = $totalPrice * (1 - $discount / 100);
            $savings = $originalPrice - $totalPrice;
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

            if ($pm === 'credit_card') {
                $transactionMethod = 'Card';
                if (empty($validated['cc_number']) || empty($validated['cc_name'])) {
                    DB::rollBack();
                    return back()->withErrors(['error' => 'Credit card details required'])->withInput();
                }
            } elseif ($pm === 'bank_transfer') {
                $transactionMethod = 'Bank Transfer';
                $bankRef = $validated['bank_reference'] ?? null;
                if (!$bankRef) {
                    DB::rollBack();
                    return back()->withErrors(['error' => 'Bank reference missing'])->withInput();
                }
            } else {
                $transactionMethod = 'Cash';
            }

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

            if ($savings > 0) {
                $subscription = Auth::user()->subscription;
                $subscription->total_savings += $savings;
                $subscription->save();
            }

            DB::commit();

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

        // min stay
        $stayDuration = $checkIn->diffInDays($checkOut);
        if ($stayDuration < $listing->minimum_stay) {
            $errors['check_out_date'] = "Min stay is {$listing->minimum_stay} nights.";
        }

        // max guests
        if ($validated['num_guests'] > $listing->max_occupancy) {
            $errors['num_guests'] = "Max guests is {$listing->max_occupancy}.";
        }

        // dates are within availability dates
        $availStart = Carbon::parse($availability->start_date);
        $availEnd = Carbon::parse($availability->end_date);
        if ($checkIn->lt($availStart) || $checkOut->gt($availEnd)) {
            $errors['check_in_date'] = 'Dates are outside the available period.';
        }

        // bookings on the same days (overlap)
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

        $totalBookings = Booking::where('guest_id', Auth::id())->count();
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
        
        // Calculate old savings to adjust
        $oldStayDuration = Carbon::parse($booking->check_in_date)->diffInDays(Carbon::parse($booking->check_out_date));
        $oldOriginalPrice = $oldStayDuration * $availability->price_per_night;
        $oldSavings = max(0, $oldOriginalPrice - $booking->total_price);

        $newOriginalPrice = $stayDuration * $availability->price_per_night;
        $totalPrice = $newOriginalPrice;
        $newSavings = 0;

        // Apply premium discount if applicable
        if ($booking->guest_id === Auth::id() && Auth::user()->isPremium()) {
            $discount = Auth::user()->getDiscountPercentage();
            $totalPrice = $totalPrice * (1 - $discount / 100);
            $newSavings = $newOriginalPrice - $totalPrice;
        }
        
        $booking->update([
            'check_in_date' => $checkIn->toDateString(),
            'check_out_date' => $checkOut->toDateString(),
            'num_guests' => $validated['num_guests'],
            'total_price' => $totalPrice,
        ]);

        $savingsDiff = $newSavings - $oldSavings;
        if (abs($savingsDiff) > 0.001) {
            $subscription = Auth::user()->subscription;
            if ($subscription) {
                $subscription->total_savings += $savingsDiff;
                $subscription->save();
            }
        }
        
        return redirect()->route('bookings.index')
            ->with('success', 'Booking updated successfully!');
    }

    /**
     * Delete a booking.
     */
    public function destroy(Booking $booking)
    {
        $this->authorize('delete', $booking);

        // Revert savings if applicable
        $stayDuration = Carbon::parse($booking->check_in_date)->diffInDays(Carbon::parse($booking->check_out_date));
        $originalPrice = $stayDuration * $booking->availability->price_per_night;
        $savings = max(0, $originalPrice - $booking->total_price);

        if ($savings > 0) {
            $guest = $booking->guest;
            if ($guest && $guest->subscription) {
                $guest->subscription->total_savings = max(0, $guest->subscription->total_savings - $savings);
                $guest->subscription->save();
            }
        }

        $booking->delete();
        
        return redirect()->route('bookings.index')
            ->with('success', 'Booking deleted successfully.');
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

            // change booking status to Rejected
            DB::table('bookings')->where('id', $booking->id)->update(['status' => 'Rejected']);

            // Revert savings if applicable
            $stayDuration = Carbon::parse($booking->check_in_date)->diffInDays(Carbon::parse($booking->check_out_date));
            $originalPrice = $stayDuration * $availability->price_per_night;
            $savings = max(0, $originalPrice - $booking->total_price);

            if ($savings > 0) {
                $guest = \App\Models\User::find($booking->guest_id);
                if ($guest && $guest->subscription) {
                    $guest->subscription->total_savings = max(0, $guest->subscription->total_savings - $savings);
                    $guest->subscription->save();
                }
            }

            // find last completed transaction for this booking
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

            // insert refund transaction. Use original paid_at to satisfy DB trigger timing constraints
            $refundId = DB::table('transactions')->insertGetId([
                'amount' => $lastTx->amount,
                'transaction_method' => $lastTx->transaction_method,
                'type' => 'Refund',
                'status' => 'Completed',
                'paid_at' => $lastTx->paid_at,
                'refund_id' => $lastTx->id,
                'booking_id' => $booking->id,
            ]);

            // update previous transaction status to Refunded
            DB::table('transactions')->where('id', $lastTx->id)->update(['status' => 'Refunded']);

            DB::commit();

            return redirect()->back()->with('success', 'Booking rejected and refund processed.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to process refund: ' . $e->getMessage());
        }
    }
}