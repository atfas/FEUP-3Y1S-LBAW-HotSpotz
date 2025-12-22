<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Listing;
use App\Models\Availability;
use App\Models\Booking;
use Carbon\Carbon;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Exception\ApiErrorException;

class PaymentController extends Controller
{
    public function __construct()
    {
        $stripeSecret = config('services.stripe.secret');
        if (empty($stripeSecret)) {
            throw new \RuntimeException('Stripe secret key is not configured. Please set STRIPE_SECRET in your .env file.');
        }
        Stripe::setApiKey($stripeSecret);
    }

    /**
     * Create a payment intent (booking not created yet)
     */
    public function createPaymentIntent(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.50',
        ]);

        try {
            // Convert amount to cents (Stripe uses smallest currency unit)
            $amountInCents = (int)($validated['amount'] * 100);

            if ($amountInCents < 50) {
                return response()->json(['error' => 'Amount must be at least $0.50'], 400);
            }

            $paymentIntent = PaymentIntent::create([
                'amount' => $amountInCents,
                'currency' => 'usd',
                'metadata' => [
                    'user_id' => Auth::id(),
                ],
            ]);

            if (empty($paymentIntent->client_secret)) {
                return response()->json(['error' => 'Failed to create payment intent. Please try again.'], 500);
            }

            return response()->json([
                'client_secret' => $paymentIntent->client_secret,
                'clientSecret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
            ]);
        } catch (ApiErrorException $e) {
            return response()->json(['error' => 'Payment processing error: ' . $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An unexpected error occurred. Please try again.'], 500);
        }
    }

    /**
     * Confirm payment and create booking with all transactions
     */
    public function confirmPayment(Request $request)
    {
        $validated = $request->validate([
            'payment_intent_id' => 'required|string',
            'booking_data' => 'required|array',
            'booking_data.listing_id' => 'required|exists:listings,id',
            'booking_data.availability_id' => 'required|exists:availability,id',
            'booking_data.check_in_date' => 'required|date',
            'booking_data.check_out_date' => 'required|date|after:booking_data.check_in_date',
            'booking_data.num_guests' => 'required|integer|min:1',
            'booking_data.total_price' => 'required|numeric|min:0',
            'failed_attempts' => 'nullable|integer|min:0',
        ]);

        try {
            $paymentIntent = PaymentIntent::retrieve($validated['payment_intent_id']);

            if ($paymentIntent->status !== 'succeeded') {
                return response()->json(['error' => 'Payment not completed'], 400);
            }

            $bookingData = $validated['booking_data'];
            $failedAttempts = $validated['failed_attempts'] ?? 0;

            // Validate booking data
            $listing = Listing::findOrFail($bookingData['listing_id']);
            $availability = Availability::findOrFail($bookingData['availability_id']);
            
            $checkIn = Carbon::parse($bookingData['check_in_date']);
            $checkOut = Carbon::parse($bookingData['check_out_date']);
            
            // Validate min stay
            $stayDuration = $checkIn->diffInDays($checkOut);
            if ($stayDuration < $listing->minimum_stay) {
                return response()->json(['error' => "Min stay is {$listing->minimum_stay} nights."], 400);
            }
            
            // Validate max guests
            if ($bookingData['num_guests'] > $listing->max_occupancy) {
                return response()->json(['error' => "Max guests is {$listing->max_occupancy}."], 400);
            }
            
            // Validate dates are within availability
            $availStart = Carbon::parse($availability->start_date);
            $availEnd = Carbon::parse($availability->end_date);
            
            if ($checkIn->lt($availStart) || $checkOut->gt($availEnd)) {
                return response()->json(['error' => 'Dates are outside the available period.'], 400);
            }
            
            // Check for overlapping bookings
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
                return response()->json(['error' => 'Dates overlap with an existing booking.'], 400);
            }

            // Calculate total price (apply premium discount if applicable)
            $totalPrice = $stayDuration * $availability->price_per_night;
            if (Auth::check() && Auth::user()->isPremium()) {
                $discount = Auth::user()->getDiscountPercentage();
                $totalPrice = $totalPrice * (1 - $discount / 100);
            }

            // Everything in one transaction
            DB::beginTransaction();

            // Create booking with PendingPayment status
            $booking = Booking::create([
                'check_in_date' => $checkIn->toDateString(),
                'check_out_date' => $checkOut->toDateString(),
                'num_guests' => $bookingData['num_guests'],
                'status' => 'PendingPayment',
                'total_price' => $totalPrice,
                'availability_id' => $bookingData['availability_id'],
                'guest_id' => Auth::id(),
            ]);

            // Create failed transaction records if there were failed attempts
            for ($i = 0; $i < $failedAttempts; $i++) {
                DB::table('transactions')->insert([
                    'amount' => $totalPrice,
                    'transaction_method' => 'Card',
                    'type' => 'FullPayment',
                    'status' => 'Failed',
                    'paid_at' => null,
                    'refund_id' => null,
                    'booking_id' => $booking->id,
                ]);
            }

            // Create successful transaction
            DB::table('transactions')->insert([
                'amount' => $paymentIntent->amount / 100, // Convert back from cents
                'transaction_method' => 'Card',
                'type' => 'FullPayment',
                'status' => 'Completed',
                'paid_at' => DB::raw('CURRENT_TIMESTAMP'),
                'refund_id' => null,
                'booking_id' => $booking->id,
            ]);

            // Update booking status to Confirmed
            $booking->update(['status' => 'Confirmed']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment confirmed and booking created successfully',
                'booking_id' => $booking->id,
                'redirect_url' => route('bookings.index'),
            ]);
        } catch (ApiErrorException $e) {
            DB::rollBack();
            return response()->json(['error' => 'Stripe error: ' . $e->getMessage()], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Confirm a Stripe payment for an existing pending booking.
     */
    public function confirmRepayment(Request $request)
    {
        $validated = $request->validate([
            'payment_intent_id' => 'required|string',
            'booking_id' => 'required|exists:bookings,id',
        ]);

        try {
            $booking = Booking::findOrFail($validated['booking_id']);
            $this->authorize('view', $booking);

            if ($booking->status !== 'PendingPayment') {
                return response()->json(['error' => 'This booking is not pending payment.'], 422);
            }

            $paymentIntent = PaymentIntent::retrieve($validated['payment_intent_id']);

            if ($paymentIntent->status !== 'succeeded') {
                return response()->json(['error' => 'Payment not completed.'], 400);
            }

            // Verify amount matches
            $expectedAmount = (int)($booking->total_price * 100);
            if ($paymentIntent->amount !== $expectedAmount) {
                // Ideally, refund the payment here if amounts mismatch
                return response()->json(['error' => 'Payment amount mismatch.'], 400);
            }

            DB::beginTransaction();

            // Create successful transaction
            DB::table('transactions')->insert([
                'amount' => $paymentIntent->amount / 100, // Convert back from cents
                'transaction_method' => 'Card',
                'type' => 'FullPayment',
                'status' => 'Completed',
                'paid_at' => DB::raw('CURRENT_TIMESTAMP'),
                'refund_id' => null,
                'booking_id' => $booking->id,
            ]);

            // Update booking status to Confirmed
            $booking->update(['status' => 'Confirmed']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment confirmed and booking updated successfully.',
                'redirect_url' => route('bookings.index'),
            ]);

        } catch (ApiErrorException $e) {
            DB::rollBack();
            return response()->json(['error' => 'Stripe error: ' . $e->getMessage()], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Confirm a non-Stripe payment for an existing pending booking.
     */
    public function confirmOtherRepayment(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'payment_method' => 'required|string|in:pay_on_arrival,bank_transfer',
            'bank_reference' => 'nullable|string',
        ]);

        try {
            $booking = Booking::findOrFail($validated['booking_id']);
            $this->authorize('view', $booking);

            if ($booking->status !== 'PendingPayment') {
                return redirect()->route('bookings.index')->with('error', 'This booking is not pending payment.');
            }

            DB::beginTransaction();

            if ($validated['payment_method'] === 'bank_transfer') {
                $transactionMethod = 'Bank Transfer';
            } else {
                $transactionMethod = 'Cash';
            }

            DB::table('transactions')->insert([
                'amount' => $booking->total_price,
                'transaction_method' => $transactionMethod,
                'type' => 'FullPayment',
                'status' => 'Completed',
                'paid_at' => DB::raw('CURRENT_TIMESTAMP'),
                'refund_id' => null,
                'booking_id' => $booking->id,
            ]);

            $booking->update(['status' => 'Confirmed']);

            DB::commit();

            return redirect()->route('bookings.index')->with('success', 'Booking payment confirmed successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('bookings.index')->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }
}
