@extends('layouts.app')
@section('title', 'Pay with card')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h4 class="mb-3">Subscribe — {{ $tier }} · {{ $billing }}</h4>
                    <p class="mb-3">First payment: <strong>€{{ number_format($price, 2) }}</strong> <small class="text-muted">({{ $billing === 'Monthly' ? 'per month' : 'per year' }})</small></p>

                    @include('partials.payment_form')

                    <form id="subscription-hidden-fields" style="display:none;">
                        @csrf
                        <input type="hidden" id="tier" name="tier" value="{{ $tier }}">
                        <input type="hidden" id="billing" name="billing" value="{{ $billing }}">
                        <input type="hidden" id="price" name="price" value="{{ $price }}">
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Use the same Stripe Elements layout as the payment form partial.
    const stripeKey = '{{ config('services.stripe.key') }}';
    if (!stripeKey) {
        console.error('Stripe public key not configured');
        const ce = document.getElementById('card-errors'); if (ce) ce.textContent = 'Payment configuration error.';
        return;
    }

    // Make the shared payment-step visible and select credit card by default
    const paymentStep = document.getElementById('payment-step');
    if (paymentStep) paymentStep.style.display = 'block';

    // Hide split UI when using subscription checkout
    const toggleSplitBtn = document.getElementById('toggle-split-btn');
    const splitSection = document.getElementById('split-section');
    if (toggleSplitBtn) toggleSplitBtn.style.display = 'none';
    if (splitSection) splitSection.style.display = 'none';

    // Show credit card box and hide other payment boxes
    const paymentOptions = document.querySelectorAll('.payment-option');
    // Remove the payment option buttons entirely for this page (card-only)
    paymentOptions.forEach(b => {
        const container = b.closest('.mb-3');
        if (container) container.remove();
        else b.remove();
    });
    const creditBtn = Array.from(paymentOptions).find(b => b.dataset && b.dataset.method === 'credit_card');
    if (creditBtn) creditBtn.classList.add('active');
    const creditBox = document.getElementById('credit_card_box');
    const arrivalBox = document.getElementById('pay_on_arrival_box');
    const bankBox = document.getElementById('bank_transfer_box');
    if (creditBox) creditBox.style.display = 'block';
    if (arrivalBox) arrivalBox.style.display = 'none';
    if (bankBox) bankBox.style.display = 'none';

    const stripe = Stripe(stripeKey);
    const elements = stripe.elements();

    const style = { base: { fontSize: '16px', color: '#32325d', '::placeholder': { color: '#aab7c4' }}};
    const cardNumberElement = elements.create('cardNumber', { style });
    const cardExpiryElement = elements.create('cardExpiry', { style });
    const cardCvcElement = elements.create('cardCvc', { style });

    if (document.getElementById('card-number-element')) cardNumberElement.mount('#card-number-element');
    if (document.getElementById('card-expiry-element')) cardExpiryElement.mount('#card-expiry-element');
    if (document.getElementById('card-cvc-element')) cardCvcElement.mount('#card-cvc-element');

    // Sync displayed price
    const price = parseFloat(document.getElementById('price').value || {{ $price }});
    document.querySelectorAll('.final-price').forEach(el => el.textContent = '€' + price.toFixed(2));

    const payButton = document.getElementById('stripe-pay-button');
    if (payButton) {
        payButton.textContent = 'Pay €' + price.toFixed(2);
    }

    // Handle submit using the existing stripe-pay-button from partial
    if (payButton) {
        payButton.addEventListener('click', async function(e) {
            e.preventDefault();
            payButton.disabled = true;
            const cardErrors = document.getElementById('card-errors');
            if (cardErrors) { cardErrors.style.display = 'none'; cardErrors.textContent = ''; }

            const cardName = document.getElementById('cc_name') ? document.getElementById('cc_name').value.trim() : '';
            if (!cardName) {
                if (cardErrors) cardErrors.textContent = 'Please enter cardholder name';
                payButton.disabled = false;
                return;
            }

            try {
                const tokenMeta = document.querySelector('meta[name="csrf-token"]');
                const token = tokenMeta ? tokenMeta.getAttribute('content') : '';

                const resp = await fetch('{{ route('payments.create-intent') }}', {
                    method: 'POST',
                    headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': token, 'Accept':'application/json' },
                    body: JSON.stringify({ amount: price })
                });
                const ji = await resp.json();
                if (!resp.ok) {
                    if (cardErrors) cardErrors.textContent = ji.error || 'Failed to create payment intent';
                    payButton.disabled = false;
                    return;
                }

                const clientSecret = ji.client_secret || ji.clientSecret;
                const { error, paymentIntent } = await stripe.confirmCardPayment(clientSecret, {
                    payment_method: { card: cardNumberElement, billing_details: { name: cardName } }
                });

                if (error) {
                    if (cardErrors) cardErrors.textContent = error.message;
                    payButton.disabled = false;
                    return;
                }

                if (paymentIntent && paymentIntent.status === 'succeeded') {
                    // Confirm subscription on server
                    const confirmResp = await fetch('{{ route('subscription.confirm') }}', {
                        method: 'POST',
                        headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': token },
                        body: JSON.stringify({ payment_intent_id: paymentIntent.id, tier: document.getElementById('tier').value, billing: document.getElementById('billing').value, price: price })
                    });
                    if (confirmResp.ok) {
                        window.location = '{{ route('profile.show') }}';
                        return;
                    }
                    const cj = await confirmResp.json().catch(()=>({}));
                    if (cardErrors) cardErrors.textContent = cj.error || 'Failed to confirm subscription';
                }
            } catch (ex) {
                const ce = document.getElementById('card-errors'); if (ce) ce.textContent = ex.message || 'Payment failed';
            }
            payButton.disabled = false;
        });
    }
});
</script>
@endpush
@endsection
