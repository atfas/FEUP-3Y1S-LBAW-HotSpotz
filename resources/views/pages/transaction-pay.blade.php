@extends('layouts.app')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg">
                <div class="card-header bg-wine text-white">
                    <h3 class="mb-0">Pay Transaction</h3>
                </div>
                <div class="card-body p-4">
                    <h4 class="card-title">{{ $transaction->booking?->availability?->listing?->title ?? 'Transaction' }}</h4>
                    <p class="text-muted">Transaction #{{ $transaction->id }} — {{ $transaction->type }}</p>
                    <hr>
                    <div class="alert alert-info d-flex justify-content-between align-items-center">
                        <span class="fs-5">Amount:</span>
                        <span class="fs-4 fw-bold">${{ number_format($transaction->amount, 2) }}</span>
                    </div>

                    <form id="repaymentForm">
                        @csrf
                        <input type="hidden" id="transaction_id" name="transaction_id" value="{{ $transaction->id }}">
                        <input type="hidden" id="booking_id" name="booking_id" value="{{ $transaction->booking_id }}">
                        <input type="hidden" id="total_price_input" name="total_price" value="{{ $transaction->amount }}">

                        {{-- Reuse payment form partial (payment method specific UI will be shown) --}}
                        @include('partials.payment_form')
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        window.stripePublishableKey = '{{ config('services.stripe.key') }}';
        window.transactionId = {{ $transaction->id }};
        window.transactionAmount = {{ $transaction->amount }};
        window.bookingId = {{ $transaction->booking_id ?? 'null' }};
    </script>
    <script>
        // Auto-select payment method defined on the transaction and prevent switching
        document.addEventListener('DOMContentLoaded', function(){
            const methodRaw = '{{ $transaction->transaction_method }}'.toLowerCase();
            let target = 'credit_card';
            if (methodRaw.includes('bank')) target = 'bank_transfer';
            else if (methodRaw.includes('cash') || methodRaw.includes('pay')) target = 'pay_on_arrival';

            // simulate click on matching payment option and hide other options
            const btn = document.querySelector(`.payment-option[data-method="${target}"]`);
            if (btn) {
                btn.click();
                // hide other buttons
                document.querySelectorAll('.payment-option').forEach(b => {
                    if (b !== btn) b.style.display = 'none';
                });
            }
        });
    </script>
    <script>
        // Ensure the payment step is visible on the transaction pay page
        document.addEventListener('DOMContentLoaded', function() {
            const paymentStep = document.getElementById('payment-step');
            if (paymentStep) paymentStep.style.display = 'block';
        });
    </script>
    <script src="{{ asset('js/repayment.js') }}" defer></script>
@endpush
