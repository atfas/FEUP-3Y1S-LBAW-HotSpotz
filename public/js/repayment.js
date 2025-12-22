document.addEventListener('DOMContentLoaded', function () {
    // --- DOM Elements ---
    const paymentOptions = document.querySelectorAll('.payment-option');
    const paymentBoxes = document.querySelectorAll('.payment-box');
    const completeBtns = document.querySelectorAll('.complete-payment');
    const repaymentForm = document.getElementById('repaymentForm');
    const finalPriceEls = document.querySelectorAll('.final-price');

    // --- Stripe Variables ---
    let stripe = null;
    let elements = null;
    let cardNumberElement = null;
    let cardExpiryElement = null;
    let cardCvcElement = null;

    // --- Initial Setup ---
    // Support both booking-based repayment pages and transaction-based pages
    const hasBooking = (typeof window.bookingId !== 'undefined' && typeof window.bookingTotalPrice !== 'undefined');
    const hasTransaction = (typeof window.transactionId !== 'undefined' && typeof window.transactionAmount !== 'undefined');

    if (!repaymentForm || !window.stripePublishableKey || (!hasBooking && !hasTransaction)) {
        console.error('Repayment page is missing required data.');
        return;
    }

    // On repayment/transaction pages we must hide the split-payment UI (split is only for booking creation)
    const toggleSplitBtn_r_hide = document.getElementById('toggle-split-btn');
    if (toggleSplitBtn_r_hide) toggleSplitBtn_r_hide.style.display = 'none';

    // Set the final price on all elements (use transactionAmount if present)
    const displayAmount = hasTransaction ? window.transactionAmount : window.bookingTotalPrice;
    const formattedPrice = '$' + Number(displayAmount).toFixed(2);
    finalPriceEls.forEach(el => el.textContent = formattedPrice);

    // Adjust back button: on transaction pay page go back to transactions index
    const backBtn = document.getElementById('backToBooking');
    if (backBtn) {
        if (hasTransaction) {
            backBtn.textContent = 'Back';
            backBtn.addEventListener('click', async function(){
                try {
                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    await fetch('/repayment/record-failure', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ transaction_id: window.transactionId }),
                    });
                } catch (e) {
                    // ignore errors, still redirect
                }
                window.location.href = '/transactions';
            });
        } else {
            // default behaviour: go back to bookings list
            backBtn.addEventListener('click', function(){ window.location.href = '/bookings'; });
        }
    }

    // Split payment is only available during booking creation; repayment page must not show split UI.


    // --- Functions ---

    function initializeStripe() {
        if (stripe) return; // Already initialized

        stripe = Stripe(window.stripePublishableKey);
        elements = stripe.elements();

        const style = { base: { fontSize: '16px', color: '#32325d' } };
        cardNumberElement = elements.create('cardNumber', { style });
        cardExpiryElement = elements.create('cardExpiry', { style });
        cardCvcElement = elements.create('cardCvc', { style });

        cardNumberElement.mount('#card-number-element');
        cardExpiryElement.mount('#card-expiry-element');
        cardCvcElement.mount('#card-cvc-element');

        const cardErrors = document.getElementById('card-errors');
        const payButton = document.getElementById('stripe-pay-button');

        [cardNumberElement, cardExpiryElement, cardCvcElement].forEach(el => {
            el.on('change', function(event) {
                if (event.error) {
                    cardErrors.textContent = event.error.message;
                    cardErrors.style.display = 'block';
                    payButton.disabled = true;
                } else {
                    cardErrors.style.display = 'none';
                    // Very basic validation: enable button if card name is not empty
                    const cardName = document.getElementById('cc_name').value.trim();
                    if (cardName) {
                        payButton.disabled = false;
                    }
                }
            });
        });
    }

    async function handleStripePayment() {
        const payButton = document.getElementById('stripe-pay-button');
        const cardErrors = document.getElementById('card-errors');
        const cardName = document.getElementById('cc_name').value.trim();

        if (!cardName) {
            cardErrors.textContent = 'Please enter the cardholder name.';
            cardErrors.style.display = 'block';
            return;
        }

        payButton.disabled = true;
        payButton.textContent = 'Processing...';
        cardErrors.style.display = 'none';

        try {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // 1. Create a Payment Intent
            // Determine amount to charge: transactionAmount (if paying a specific txn) or bookingTotalPrice
            const amountToCharge = (typeof window.transactionAmount !== 'undefined') ? window.transactionAmount : window.bookingTotalPrice;
            const intentResponse = await fetch('/payments/create-intent', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ amount: amountToCharge }),
            });

            const intentData = await intentResponse.json();
            if (!intentResponse.ok) throw new Error(intentData.error || 'Failed to create payment intent.');

            const clientSecret = intentData.client_secret;
            if (!clientSecret) throw new Error('Missing client_secret from payment intent.');

            // 2. Confirm the card payment with Stripe
            const { error: confirmError, paymentIntent } = await stripe.confirmCardPayment(
                clientSecret,
                {
                    payment_method: {
                        card: cardNumberElement,
                        billing_details: { name: cardName },
                    },
                }
            );

            if (confirmError) throw confirmError;

            // 3. Confirm the repayment on our backend
            const payload = {
                payment_intent_id: paymentIntent.id,
                booking_id: window.bookingId,
            };
            if (typeof window.transactionId !== 'undefined') payload.transaction_id = window.transactionId;

            const confirmResponse = await fetch(`/repayment/confirm`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    payment_intent_id: paymentIntent.id,
                    booking_id: window.bookingId,
                    transaction_id: window.transactionId,
                }),
            });

            const confirmData = await confirmResponse.json();
            if (!confirmResponse.ok) throw new Error(confirmData.error || 'Failed to confirm payment on server.');

            // 4. Success! Redirect.
            window.location.href = confirmData.redirect_url || '/bookings';

        } catch (error) {
            cardErrors.textContent = error.message || 'An unexpected error occurred.';
            cardErrors.style.display = 'block';
            payButton.disabled = false;
            payButton.textContent = 'Complete Payment';
        }
    }


    // --- Event Listeners ---

    paymentOptions.forEach(btn => {
        btn.addEventListener('click', function () {
            paymentOptions.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const method = this.dataset.method;

            paymentBoxes.forEach(pb => pb.style.display = 'none');
            const targetBox = document.getElementById(method + '_box');
            if (targetBox) {
                targetBox.style.display = 'block';
            }

            if (method === 'credit_card') {
                initializeStripe();
                const cardNameInput = document.getElementById('cc_name');
                const payButton = document.getElementById('stripe-pay-button');
                payButton.disabled = !cardNameInput.value.trim();
            }
        });
    });

    completeBtns.forEach(cb => {
        cb.addEventListener('click', function () {
            const method = this.dataset.method;

            let pmInput = repaymentForm.querySelector('input[name="payment_method"]');
            if (!pmInput) {
                pmInput = document.createElement('input');
                pmInput.type = 'hidden';
                pmInput.name = 'payment_method';
                repaymentForm.appendChild(pmInput);
            }
            pmInput.value = method;

            if (method === 'credit_card') {
                handleStripePayment();
            } else {
                // For other methods, submit the form to a new endpoint
                repaymentForm.action = `/repayment/confirm-other`;
                repaymentForm.method = 'POST';
                repaymentForm.submit();
            }
        });
    });
});
