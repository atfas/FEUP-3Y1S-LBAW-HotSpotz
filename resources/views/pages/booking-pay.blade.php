@extends('layouts.app')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg">
                <div class="card-header bg-wine text-white">
                    <h3 class="mb-0">Complete Your Payment</h3>
                </div>
                <div class="card-body p-4">
                    <h4 class="card-title">{{ $booking->availability->listing->title }}</h4>
                    <p class="text-muted">{{ $booking->availability->listing->location_address }}</p>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Check-in:</strong>
                            <p>{{ \Carbon\Carbon::parse($booking->check_in_date)->format('D, M d, Y') }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Check-out:</strong>
                            <p>{{ \Carbon\Carbon::parse($booking->check_out_date)->format('D, M d, Y') }}</p>
                        </div>
                    </div>
                    <div class="alert alert-info d-flex justify-content-between align-items-center">
                        <span class="fs-5">Total Price:</span>
                        <span class="fs-4 fw-bold">${{ number_format($booking->total_price, 2) }}</span>
                    </div>

                    {{-- The form will be handled by Stripe and JS, but we need it for CSRF and structure --}}
                    <form id="repaymentForm">
                        @csrf
                        <input type="hidden" id="booking_id" name="booking_id" value="{{ $booking->id }}">
                        <input type="hidden" id="total_price_input" name="total_price" value="{{ $booking->total_price }}">

                        {{-- Include the same payment form partial --}}
                        @include('partials.payment_form')
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
{{-- If there are specific styles for the payment form, they should be included --}}
@endpush

@push('scripts')
    {{-- Stripe.js is required --}}
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        // Pass backend data to our new JS file
        window.stripePublishableKey = '{{ config('services.stripe.key') }}';
        window.bookingId = {{ $booking->id }};
        window.bookingTotalPrice = {{ $booking->total_price }};

        document.addEventListener('DOMContentLoaded', function() {
            // Show the payment step by default on this page
            const paymentStep = document.getElementById('payment-step');
            if (paymentStep) {
                paymentStep.style.display = 'block';
            }

            // Hide the 'back' button as it's not needed
            const backBtn = document.getElementById('backToBooking');
            if (backBtn) {
                backBtn.style.display = 'none';
            }
        });
    </script>
    {{-- We will create this new JS file to handle the payment logic for this page --}}
    <script src="{{ asset('js/repayment.js') }}" defer></script>
@endpush
