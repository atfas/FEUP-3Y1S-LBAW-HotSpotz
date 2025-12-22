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
     * AJAX endpoint to check if an email belongs to an existing user.
     */
    public function checkUserEmail(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $user = DB::table('users')->where('email', $validated['email'])->first();
        if ($user) {
            return response()->json(['exists' => true, 'user_id' => $user->id, 'email' => $user->email]);
        }

        return response()->json(['exists' => false]);
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
            'booking_data.split_emails' => 'nullable|array',
            'booking_data.split_emails.*' => 'email|exists:users,email',
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

            // Handle optional split payments (card split among multiple payers)
            $splitEmails = $bookingData['split_emails'] ?? [];
            $splitCount = 1 + count($splitEmails);

            // If split is used, ensure it fits guest count
            if (!empty($splitEmails) && $splitCount > $bookingData['num_guests']) {
                DB::rollBack();
                return response()->json(['error' => 'Number of payers exceeds number of guests.'], 400);
            }

            // Work in cents to avoid floating precision issues
            $totalCents = (int)round($totalPrice * 100);

            if (!empty($splitEmails)) {
                // Split evenly, distribute remainder cents to first shares
                $baseShare = intdiv($totalCents, $splitCount);
                $remainder = $totalCents - ($baseShare * $splitCount);

                // Build shares array (in cents)
                $shares = array_fill(0, $splitCount, $baseShare);
                for ($i = 0; $i < $remainder; $i++) {
                    $shares[$i] += 1;
                }

                // Initiator share is first
                $initiatorShareCents = $shares[0];

                // Validate payment intent amount matches initiator share
                if ((int)$paymentIntent->amount !== $initiatorShareCents) {
                    DB::rollBack();
                    return response()->json(['error' => 'Payment amount does not match your required share.'], 400);
                }

                // Create failed transaction records for initiator share if needed
                for ($i = 0; $i < $failedAttempts; $i++) {
                    DB::table('transactions')->insert([
                        'amount' => $initiatorShareCents / 100,
                        'transaction_method' => 'Card',
                        'type' => 'FullPayment',
                        'status' => 'Failed',
                        'paid_at' => null,
                        'refund_id' => null,
                        'booking_id' => $booking->id,
                    ]);
                }

                // Create completed transaction for initiator (user_id NULL)
                DB::table('transactions')->insert([
                    'amount' => $initiatorShareCents / 100,
                    'transaction_method' => 'Card',
                    'type' => 'FullPayment',
                    'status' => 'Completed',
                    'paid_at' => DB::raw('CURRENT_TIMESTAMP'),
                    'refund_id' => null,
                    'booking_id' => $booking->id,
                    'user_id' => null,
                ]);

                // Create pending transactions for each other payer (assign to user_id)
                for ($i = 1; $i < $splitCount; $i++) {
                    $email = $splitEmails[$i - 1] ?? null;
                    $payer = $email ? DB::table('users')->where('email', $email)->first() : null;
                    $payerId = $payer ? $payer->id : null;

                    DB::table('transactions')->insert([
                        'amount' => $shares[$i] / 100,
                        'transaction_method' => 'Card',
                        'type' => 'FullPayment',
                        'status' => 'Pending',
                        'paid_at' => null,
                        'refund_id' => null,
                        'booking_id' => $booking->id,
                        'user_id' => $payerId,
                    ]);
                }

                // Do NOT auto-confirm booking here; DB trigger will confirm when all shares completed
            } else {
                // Non-split (original flow)

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

                // Validate payment amount equals total price
                $expectedCents = $totalCents;
                if ((int)$paymentIntent->amount !== $expectedCents) {
                    DB::rollBack();
                    return response()->json(['error' => 'Payment amount mismatch'], 400);
                }

                // Create successful transaction for full amount
                DB::table('transactions')->insert([
                    'amount' => $paymentIntent->amount / 100, // Convert back from cents
                    'transaction_method' => 'Card',
                    'type' => 'FullPayment',
                    'status' => 'Completed',
                    'paid_at' => DB::raw('CURRENT_TIMESTAMP'),
                    'refund_id' => null,
                    'booking_id' => $booking->id,
                    'user_id' => null,
                ]);

                // Update booking status to Confirmed
                $booking->update(['status' => 'Confirmed']);
            }

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
            'booking_id' => 'nullable|exists:bookings,id',
            'transaction_id' => 'nullable|integer|exists:transactions,id',
        ]);

        try {
            // Resolve booking: prefer provided booking_id, otherwise derive from transaction
            if (!empty($validated['booking_id'])) {
                $booking = Booking::findOrFail($validated['booking_id']);
            } elseif (!empty($validated['transaction_id'])) {
                $txnForBooking = DB::table('transactions')->where('id', $validated['transaction_id'])->first();
                if (!$txnForBooking || empty($txnForBooking->booking_id)) {
                    return response()->json(['error' => 'Transaction or booking not found.'], 404);
                }
                $booking = Booking::findOrFail($txnForBooking->booking_id);
            } else {
                return response()->json(['error' => 'Missing booking or transaction reference.'], 400);
            }

            // If a specific transaction is being paid, allow the assigned user to pay
            // even if they are not authorized to view the booking. Otherwise require view permission.
            $txnForPayment = null;
            if (!empty($validated['transaction_id'])) {
                $txnForPayment = DB::table('transactions')->where('id', $validated['transaction_id'])->first();
                if (!$txnForPayment) {
                    return response()->json(['error' => 'Transaction not found.'], 404);
                }
                if ($txnForPayment->booking_id != $booking->id) {
                    return response()->json(['error' => 'Transaction does not belong to booking.'], 400);
                }
                if ($txnForPayment->status !== 'Pending') {
                    return response()->json(['error' => 'Transaction is not pending.'], 400);
                }
                if ($txnForPayment->user_id !== null && $txnForPayment->user_id != Auth::id()) {
                    return response()->json(['error' => 'You are not authorized to pay this transaction.'], 403);
                }
            } else {
                $this->authorize('view', $booking);
                if ($booking->status !== 'PendingPayment') {
                    return response()->json(['error' => 'This booking is not pending payment.'], 422);
                }
            }

            $paymentIntent = PaymentIntent::retrieve($validated['payment_intent_id']);

            if ($paymentIntent->status !== 'succeeded') {
                // Create a failed transaction record for this repayment attempt (only for repayment flows, not booking creation)
                try {
                    DB::beginTransaction();
                    if (!empty($validated['transaction_id'])) {
                        $origTxn = DB::table('transactions')->where('id', $validated['transaction_id'])->first();
                        if ($origTxn) {
                            DB::table('transactions')->insert([
                                'amount' => $origTxn->amount,
                                'transaction_method' => 'Card',
                                'type' => 'FullPayment',
                                'status' => 'Failed',
                                'paid_at' => null,
                                'refund_id' => null,
                                'booking_id' => $origTxn->booking_id,
                                'user_id' => $origTxn->user_id,
                            ]);
                        }
                    } else {
                        // Legacy repayment for full booking
                        DB::table('transactions')->insert([
                            'amount' => $booking->total_price,
                            'transaction_method' => 'Card',
                            'type' => 'FullPayment',
                            'status' => 'Failed',
                            'paid_at' => null,
                            'refund_id' => null,
                            'booking_id' => $booking->id,
                            'user_id' => null,
                        ]);
                    }
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                }

                return response()->json(['error' => 'Payment not completed.'], 400);
            }

            DB::beginTransaction();

            // If transaction_id provided, pay that specific pending transaction (used for split payments)
            if (!empty($validated['transaction_id'])) {
                $txnId = $validated['transaction_id'];

                $txn = DB::table('transactions')->where('id', $txnId)->lockForUpdate()->first();
                if (!$txn) {
                    DB::rollBack();
                    return response()->json(['error' => 'Transaction not found.'], 404);
                }

                if ($txn->booking_id != $booking->id) {
                    DB::rollBack();
                    return response()->json(['error' => 'Transaction does not belong to booking.'], 400);
                }

                if ($txn->status !== 'Pending') {
                    DB::rollBack();
                    return response()->json(['error' => 'Transaction is not pending.'], 400);
                }

                // Ensure only the assigned user can pay this transaction
                if ($txn->user_id !== null && $txn->user_id != Auth::id()) {
                    DB::rollBack();
                    return response()->json(['error' => 'You are not authorized to pay this transaction.'], 403);
                }

                // Verify amount matches transaction amount
                $expectedAmount = (int)round($txn->amount * 100);
                if ($paymentIntent->amount !== $expectedAmount) {
                    DB::rollBack();
                    return response()->json(['error' => 'Payment amount mismatch.'], 400);
                }

                // Mark transaction completed
                DB::table('transactions')->where('id', $txnId)->update([
                    'status' => 'Completed',
                    'paid_at' => DB::raw('CURRENT_TIMESTAMP'),
                    'transaction_method' => 'Card',
                ]);

                // Booking confirmation will be handled by DB trigger when sums match
            } else {
                // Legacy flow: pay full booking total
                // Verify amount matches full booking total
                $expectedAmount = (int)($booking->total_price * 100);
                if ($paymentIntent->amount !== $expectedAmount) {
                    DB::rollBack();
                    return response()->json(['error' => 'Payment amount mismatch.'], 400);
                }

                // Create successful transaction for full amount
                DB::table('transactions')->insert([
                    'amount' => $paymentIntent->amount / 100, // Convert back from cents
                    'transaction_method' => 'Card',
                    'type' => 'FullPayment',
                    'status' => 'Completed',
                    'paid_at' => DB::raw('CURRENT_TIMESTAMP'),
                    'refund_id' => null,
                    'booking_id' => $booking->id,
                ]);

                // Update booking status to Confirmed (legacy single-payer flow)
                $booking->update(['status' => 'Confirmed']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment confirmed and booking updated successfully.',
                'redirect_url' => !empty($validated['transaction_id']) ? route('transactions.index') : route('bookings.index'),
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
            'booking_id' => 'nullable|exists:bookings,id',
            'payment_method' => 'required|string|in:pay_on_arrival,bank_transfer',
            'bank_reference' => 'nullable|string',
            'transaction_id' => 'nullable|integer|exists:transactions,id',
        ]);

        try {
            // Resolve booking either from provided booking_id or from transaction
            if (!empty($validated['booking_id'])) {
                $booking = Booking::findOrFail($validated['booking_id']);
            } elseif (!empty($validated['transaction_id'])) {
                $txn = DB::table('transactions')->where('id', $validated['transaction_id'])->first();
                if (!$txn || empty($txn->booking_id)) {
                    return redirect()->route('bookings.index')->with('error', 'Transaction or booking not found.');
                }
                $booking = Booking::findOrFail($txn->booking_id);
            } else {
                return redirect()->route('bookings.index')->with('error', 'Missing booking or transaction reference.');
            }

            $this->authorize('view', $booking);

            if ($booking->status !== 'PendingPayment') {
                return redirect()->route('bookings.index')->with('error', 'This booking is not pending payment.');
            }

            DB::beginTransaction();

            $transactionMethod = $validated['payment_method'] === 'bank_transfer' ? 'Bank Transfer' : 'Cash';

            // If a specific transaction is provided (split scenario), mark that transaction completed only if method matches
            if (!empty($validated['transaction_id'])) {
                $txnId = $validated['transaction_id'];
                $txn = DB::table('transactions')->where('id', $txnId)->lockForUpdate()->first();
                if (!$txn) {
                    DB::rollBack();
                    return redirect()->route('bookings.index')->with('error', 'Transaction not found.');
                }

                if ($txn->booking_id != $booking->id) {
                    DB::rollBack();
                    return redirect()->route('bookings.index')->with('error', 'Transaction does not belong to booking.');
                }

                if ($txn->status !== 'Pending') {
                    DB::rollBack();
                    return redirect()->route('bookings.index')->with('error', 'Transaction is not pending.');
                }

                // Ensure only assigned user can confirm
                if ($txn->user_id !== null && $txn->user_id != Auth::id()) {
                    DB::rollBack();
                    return redirect()->route('bookings.index')->with('error', 'You are not authorized to pay this transaction.');
                }

                // Ensure transaction method matches
                if ($txn->transaction_method !== $transactionMethod && !empty($txn->transaction_method)) {
                    DB::rollBack();
                    return redirect()->route('bookings.index')->with('error', 'This transaction must be paid with ' . $txn->transaction_method);
                }

                // Mark transaction completed
                DB::table('transactions')->where('id', $txnId)->update([
                    'status' => 'Completed',
                    'paid_at' => DB::raw('CURRENT_TIMESTAMP'),
                    'transaction_method' => $transactionMethod,
                ]);

                // Booking confirmation will be handled by DB trigger if sums match
            } else {
                // Legacy: pay the whole booking with bank transfer or cash
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
            }

            DB::commit();

            if (!empty($validated['transaction_id'])) {
                return redirect()->route('transactions.index')->with('success', 'Payment confirmed successfully!');
            }
            return redirect()->route('bookings.index')->with('success', 'Booking payment confirmed successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            if (!empty($validated['transaction_id'])) {
                return redirect()->route('transactions.index')->with('error', 'An error occurred: ' . $e->getMessage());
            }
            return redirect()->route('bookings.index')->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Record a failed repayment attempt for a specific transaction (called when user cancels/back).
     */
    public function recordRepaymentFailure(Request $request)
    {
        $validated = $request->validate([
            'transaction_id' => 'required|integer|exists:transactions,id',
        ]);

        try {
            $txn = DB::table('transactions')->where('id', $validated['transaction_id'])->first();
            if (!$txn) {
                return response()->json(['ok' => false, 'error' => 'Transaction not found'], 404);
            }

            // Insert a copy of the transaction but mark as Failed
            DB::table('transactions')->insert([
                'amount' => $txn->amount,
                'transaction_method' => $txn->transaction_method ?? 'Card',
                'type' => $txn->type ?? 'FullPayment',
                'status' => 'Failed',
                'paid_at' => null,
                'refund_id' => null,
                'booking_id' => $txn->booking_id,
                'user_id' => $txn->user_id,
            ]);

            return response()->json(['ok' => true]);
        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Render subscription checkout (card-only) for chosen tier and billing.
     */
    public function subscriptionCheckout(Request $request)
    {
        $tier = $request->query('tier', 'Gold');
        $billing = $request->query('billing', 'Monthly');

        $prices = [
            'Silver' => ['Monthly' => 7.99, 'Annual' => 79.99],
            'Gold' => ['Monthly' => 14.99, 'Annual' => 149.99],
            'Platinum' => ['Monthly' => 24.99, 'Annual' => 249.99],
        ];

        if (!isset($prices[$tier])) {
            $tier = 'Gold';
        }
        if (!in_array($billing, ['Monthly', 'Annual'])) {
            $billing = 'Monthly';
        }

        $price = $prices[$tier][$billing]; // float euros

        return view('pages.card-only', [
            'tier' => $tier,
            'billing' => $billing,
            'price' => $price,
        ]);
    }

    /**
     * Confirm subscription after successful Stripe payment intent.
     */
    public function confirmSubscription(Request $request)
    {
        $validated = $request->validate([
            'payment_intent_id' => 'required|string',
            'tier' => 'required|string|in:Silver,Gold,Platinum',
            'billing' => 'required|string|in:Monthly,Annual',
            'price' => 'required|numeric|min:0',
        ]);

        try {
            $intent = PaymentIntent::retrieve($validated['payment_intent_id']);
            if ($intent->status !== 'succeeded') {
                return redirect()->route('subscription.plans')->with('error', 'Payment not completed.');
            }

            $user = Auth::user();
            // Prevent duplicate active subscription for same user
            if ($user->subscription && $user->subscription->member_status === 'Active') {
                return redirect()->route('profile.show')->with('error', 'You already have an active subscription.');
            }

            $pricePaid = $validated['price'];
            $nextPaymentDate = $validated['billing'] === 'Monthly' ? now()->addMonth()->toDateString() : now()->addYear()->toDateString();
            $discount = $validated['tier'] === 'Silver' ? 5 : ($validated['tier'] === 'Gold' ? 10 : 20);

            // Insert or update premium_subscriptions
            DB::table('premium_subscriptions')->updateOrInsert(
                ['user_id' => $user->id],
                [
                    'member_status' => 'Active',
                    'tier' => $validated['tier'],
                    'plan_type' => $validated['billing'] === 'Monthly' ? 'Monthly' : 'Annual',
                    'price_paid' => $pricePaid,
                    'discount_percentage' => $discount,
                    'member_since' => now(),
                    'next_payment_date' => $nextPaymentDate,
                    'total_savings' => 0,
                ]
            );

            return redirect()->route('profile.show')->with('success', 'Subscription activated.');

        } catch (ApiErrorException $e) {
            return redirect()->route('subscription.plans')->with('error', 'Stripe error: ' . $e->getMessage());
        } catch (\Exception $e) {
            return redirect()->route('subscription.plans')->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }
}
