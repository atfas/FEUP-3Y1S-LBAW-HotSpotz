document.addEventListener('DOMContentLoaded', function () {
    const selectBtn = document.getElementById('selectPaymentBtn');
    const paymentStep = document.getElementById('payment-step');
    const bookingControls = document.getElementById('booking-controls');
    const bookingInputs = document.getElementById('booking-inputs');
    const availabilitySelect = document.getElementById('availability_id');
    const checkInEl = document.getElementById('check_in_date');
    const checkOutEl = document.getElementById('check_out_date');
    const totalPriceInput = document.getElementById('total_price_input');

    const paymentOptions = document.querySelectorAll('.payment-option');
    const paymentBoxes = document.querySelectorAll('.payment-box');
    const backBtn = document.getElementById('backToBooking');
    const completeBtns = document.querySelectorAll('.complete-payment');
    const bankRefEl = document.getElementById('bank_ref');
    const bankDeadlineEl = document.getElementById('bank_deadline');
    const bankInput = document.getElementById('bank_reference_input');
    const bookingForm = document.getElementById('bookingForm');

    if (!selectBtn || !paymentStep || !bookingForm) return;

    // Stripe variables
    let stripe = null;
    let elements = null;
    let cardNumberElement = null;
    let cardExpiryElement = null;
    let cardCvcElement = null;
    let paymentIntentClientSecret = null;
    let failedPaymentAttempts = 0;
    let bookingData = null;

    function generateBankRef() {
        return 'BT' + Math.floor(100000 + Math.random() * 900000);
    }

    function generateDeadline() {
        const d = new Date();
        d.setDate(d.getDate() + 2);
        return d.toISOString().split('T')[0];
    }

    function parsePrice(str) {
        return parseFloat(str) || 0;
    }

    function computePrice() {
        // get selected availability price
        let pricePerNight = 0;
        if (availabilitySelect && availabilitySelect.dataset.price) {
            pricePerNight = parsePrice(availabilitySelect.dataset.price);
        }

        // compute nights
        let nights = 0;
        if (checkInEl && checkOutEl && checkInEl.value && checkOutEl.value) {
            const a = new Date(checkInEl.value);
            const b = new Date(checkOutEl.value);
            const diff = (b - a) / (1000 * 60 * 60 * 24);
            nights = Math.max(0, Math.floor(diff));
        }

        const total = (pricePerNight * nights) || 0;
        const formatted = total.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});

        // If split UI is visible and has valid emails, compute initiator share and show per-payer amount
        let displayAmount = total;
        try {
            if (splitSection && splitSection.style.display !== 'none') {
                const splitInputs = document.querySelectorAll('.split-email-input');
                const validEmails = [];
                splitInputs.forEach(si => {
                    const v = si.value.trim(); if (v) validEmails.push(v);
                });
                if (validEmails.length > 0) {
                    const totalCents = Math.round(total * 100);
                    const splitCount = 1 + validEmails.length;
                    const base = Math.floor(totalCents / splitCount);
                    const remainder = totalCents - base * splitCount;
                    const shares = Array(splitCount).fill(base);
                    for (let i=0;i<remainder;i++) shares[i] += 1;
                    const initiatorShare = shares[0] / 100;
                    displayAmount = initiatorShare;
                }
            }
        } catch (e) {
            displayAmount = total;
        }

        const displayFormatted = displayAmount.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});
        document.querySelectorAll('.final-price').forEach(el => el.textContent = '$' + displayFormatted);
        if (totalPriceInput) totalPriceInput.value = total;
        return total;
    }

    // compute on changes
    [availabilitySelect, checkInEl, checkOutEl].forEach(el => {
        if (!el) return;
        el.addEventListener('change', computePrice);
    });

    // initial compute
    computePrice();

    selectBtn.addEventListener('click', function () {
        // validate booking with server before showing payment step
        computePrice();
        validateBooking().then(ok => {
            if (ok) {
                // Reset payment state when entering payment step
                failedPaymentAttempts = 0;
                bookingData = null;
                
                if (bookingInputs) bookingInputs.style.display = 'none';
                bookingControls.style.display = 'none';
                paymentStep.style.display = 'block';
            }
        });
    });

    function setBookingError(id, msg) {
        const eid = id + '_error';
        const err = document.getElementById(eid);
        const el = document.getElementById(id);
        if (err) { err.textContent = msg; err.style.display = msg ? 'block' : 'none'; }
        if (el) el.classList.toggle('is-invalid', !!msg);
    }

    async function validateBooking() {
        // clear booking field errors
        ['availability_id','check_in_date','check_out_date','num_guests'].forEach(f => setBookingError(f, ''));

        const tokenMeta = document.querySelector('meta[name="csrf-token"]');
        const token = tokenMeta ? tokenMeta.getAttribute('content') : '';

        const payload = {
            listing_id: bookingForm.querySelector('input[name="listing_id"]').value,
            availability_id: availabilitySelect ? availabilitySelect.value : '',
            check_in_date: checkInEl ? checkInEl.value : '',
            check_out_date: checkOutEl ? checkOutEl.value : '',
            num_guests: document.getElementById('num_guests') ? document.getElementById('num_guests').value : '',
        };

        try {
            const res = await fetch('/bookings/validate', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                },
                body: JSON.stringify(payload),
            });
            if (res.status === 200) return true;

            const data = await res.json().catch(() => null);
            // Show field errors if present
                if (data && data.errors) {
                Object.keys(data.errors).forEach(k => setBookingError(k, data.errors[k]));
                const calendarErr = document.getElementById('booking_calendar_error_message');
                if (calendarErr) calendarErr.textContent = 'Please select a date and a number of guests.';
            } else if (data && data.message) {
                const calendarErr = document.getElementById('booking_calendar_error_message');
                if (calendarErr) calendarErr.textContent = data.message;
                else alert(data.message || 'Validation failed');
            } else {
                const calendarErr = document.getElementById('booking_calendar_error_message');
                if (calendarErr) calendarErr.textContent = 'Validation failed. Please check your input.';
                else alert('Validation failed');
            }
            return false;
        } catch (e) {
            const calendarErr = document.getElementById('booking_calendar_error_message');
            if (calendarErr) calendarErr.textContent = 'Unable to validate booking. Please try again.';
            return false;
        }
    }

    // Initialize Stripe Elements
    function initializeStripe() {
        if (!window.Stripe || !window.stripePublishableKey) {
            alert('Payment system not available. Please refresh the page.');
            return;
        }

        stripe = Stripe(window.stripePublishableKey);
        elements = stripe.elements();

        // Create card elements
        const style = {
            base: {
                fontSize: '16px',
                color: '#32325d',
                '::placeholder': {
                    color: '#aab7c4',
                },
            },
            invalid: {
                color: '#fa755a',
                iconColor: '#fa755a',
            },
        };

        cardNumberElement = elements.create('cardNumber', { style });
        cardExpiryElement = elements.create('cardExpiry', { style });
        cardCvcElement = elements.create('cardCvc', { style });

        // Mount elements
        const cardNumberContainer = document.getElementById('card-number-element');
        const cardExpiryContainer = document.getElementById('card-expiry-element');
        const cardCvcContainer = document.getElementById('card-cvc-element');

        if (cardNumberContainer && cardExpiryContainer && cardCvcContainer) {
            cardNumberElement.mount('#card-number-element');
            cardExpiryElement.mount('#card-expiry-element');
            cardCvcElement.mount('#card-cvc-element');

            // Handle real-time validation errors
            const cardErrors = document.getElementById('card-errors');
            
            cardNumberElement.on('change', function(event) {
                const displayError = document.getElementById('card-number-error');
                if (event.error) {
                    displayError.textContent = event.error.message;
                    displayError.style.display = 'block';
                    if (cardErrors) {
                        cardErrors.textContent = event.error.message;
                        cardErrors.style.display = 'block';
                    }
                } else {
                    displayError.style.display = 'none';
                    if (cardErrors) {
                        cardErrors.style.display = 'none';
                    }
                }
            });

            cardExpiryElement.on('change', function(event) {
                const displayError = document.getElementById('card-expiry-error');
                if (event.error) {
                    displayError.textContent = event.error.message;
                    displayError.style.display = 'block';
                    if (cardErrors) {
                        cardErrors.textContent = event.error.message;
                        cardErrors.style.display = 'block';
                    }
                } else {
                    displayError.style.display = 'none';
                    if (cardErrors) {
                        cardErrors.style.display = 'none';
                    }
                }
            });

            cardCvcElement.on('change', function(event) {
                const displayError = document.getElementById('card-cvc-error');
                if (event.error) {
                    displayError.textContent = event.error.message;
                    displayError.style.display = 'block';
                    if (cardErrors) {
                        cardErrors.textContent = event.error.message;
                        cardErrors.style.display = 'block';
                    }
                } else {
                    displayError.style.display = 'none';
                    if (cardErrors) {
                        cardErrors.style.display = 'none';
                    }
                }
            });
        }
    }

    // --- Split payment UI & validation ---
    const toggleSplitBtn = document.getElementById('toggle-split-btn');
    const splitSection = document.getElementById('split-section');
    const splitEmailsContainer = document.getElementById('split-emails-container');

    function clearSplitInputs() {
        if (!splitEmailsContainer) return;
        splitEmailsContainer.innerHTML = `
            <div class="input-group mb-2 split-email-row">
                <input type="email" class="form-control split-email-input" placeholder="payer@example.com" />
                <span class="input-group-text split-email-status" style="width:2.5rem"></span>
                <div class="invalid-feedback d-block split-email-error" style="display:none"></div>
            </div>`;
    }

    async function validateEmailAjax(email) {
        const tokenMeta = document.querySelector('meta[name="csrf-token"]');
        const token = tokenMeta ? tokenMeta.getAttribute('content') : '';
        try {
            const res = await fetch('/payments/check-email', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ email }),
            });
            return await res.json();
        } catch (e) {
            return { exists: false };
        }
    }

    function attachSplitInputHandlers(row) {
        const input = row.querySelector('.split-email-input');
        const status = row.querySelector('.split-email-status');
        const error = row.querySelector('.split-email-error');

        let lastValue = '';
        let debounce = null;
        input.addEventListener('input', function () {
            const v = input.value.trim();
            error.style.display = 'none';
            status.textContent = '';
            if (debounce) clearTimeout(debounce);
            debounce = setTimeout(async () => {
                if (!v) return;
                if (v === lastValue) return;
                lastValue = v;
                // Basic client-side email regex
                const emailRegex = /^[^@\s]+@[^@\s]+\.[^@\s]+$/;
                if (!emailRegex.test(v)) {
                    error.textContent = 'Invalid email format';
                    error.style.display = 'block';
                    return;
                }
                const data = await validateEmailAjax(v);
                if (data.exists) {
                    status.textContent = '✓';
                    status.style.color = 'green';
                    error.style.display = 'none';
                    // If this is the last input, append another blank one
                    const rows = splitEmailsContainer.querySelectorAll('.split-email-row');
                    if (rows[rows.length - 1] === row) {
                        const newRow = document.createElement('div');
                        newRow.className = 'input-group mb-2 split-email-row';
                        newRow.innerHTML = `<input type="email" class="form-control split-email-input" placeholder="payer@example.com" /><span class="input-group-text split-email-status" style="width:2.5rem"></span><div class="invalid-feedback d-block split-email-error" style="display:none"></div>`;
                        splitEmailsContainer.appendChild(newRow);
                        attachSplitInputHandlers(newRow);
                    }
                    // Recompute displayed price when a split email validated
                    try { computePrice(); } catch (e) { /* ignore */ }
                } else {
                    status.textContent = '✕';
                    status.style.color = '#a00';
                    error.textContent = 'Email not registered on platform';
                    error.style.display = 'block';
                }
            }, 500);
        });
    }

    if (toggleSplitBtn && splitSection && splitEmailsContainer) {
        toggleSplitBtn.addEventListener('click', function () {
            if (splitSection.style.display === 'none') {
                splitSection.style.display = 'block';
                toggleSplitBtn.classList.add('active');
                // Attach handlers to initial input
                const firstRow = splitEmailsContainer.querySelector('.split-email-row');
                if (firstRow) attachSplitInputHandlers(firstRow);
                try { computePrice(); } catch(e){}
            } else {
                splitSection.style.display = 'none';
                toggleSplitBtn.classList.remove('active');
                clearSplitInputs();
                try { computePrice(); } catch(e){}
            }
        });
    }

    // Create payment intent and handle Stripe payment
    async function handleStripePayment() {
        if (!stripe || !bookingData) {
            alert('Payment system not initialized. Please try again.');
            return;
        }

        const payButton = document.getElementById('stripe-pay-button');
        const cardErrors = document.getElementById('card-errors');
        const cardName = document.getElementById('cc_name').value.trim();

        if (!cardName) {
            cardErrors.textContent = 'Please enter the cardholder name';
            cardErrors.style.display = 'block';
            return;
        }

        payButton.disabled = true;
        payButton.textContent = 'Processing...';
        cardErrors.style.display = 'none';

        try {
            const tokenMeta = document.querySelector('meta[name="csrf-token"]');
            const token = tokenMeta ? tokenMeta.getAttribute('content') : '';

            // Create payment intent (without booking_id since booking doesn't exist yet)
            const amount = computePrice();
            
            if (!amount || amount <= 0) {
                throw new Error('Invalid amount. Please check your booking details.');
            }

            // If split emails are present and validated, charge only the initiator's share
            let amountToCharge = amount;
            try {
                const splitInputs = document.querySelectorAll('.split-email-input');
                const validEmails = [];
                splitInputs.forEach(si => {
                    const v = si.value.trim();
                    if (v) validEmails.push(v);
                });
                if (validEmails.length > 0) {
                    const totalCents = Math.round(amount * 100);
                    const splitCount = 1 + validEmails.length;
                    const base = Math.floor(totalCents / splitCount);
                    const remainder = totalCents - base * splitCount;
                    const shares = Array(splitCount).fill(base);
                    for (let i=0;i<remainder;i++) shares[i] += 1;
                    const initiatorShareCents = shares[0];
                    amountToCharge = initiatorShareCents / 100;
                }
            } catch (e) {
                amountToCharge = amount;
            }

            const response = await fetch('/payments/create-intent', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    amount: amountToCharge,
                }),
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.error || data.message || 'Failed to create payment intent');
            }

            // Get client_secret from response
            paymentIntentClientSecret = data.client_secret || data.clientSecret;

            if (!paymentIntentClientSecret) {
                throw new Error('Payment intent response missing client_secret. Please check your Stripe configuration.');
            }

            // Confirm payment with Stripe
            const { error: confirmError, paymentIntent } = await stripe.confirmCardPayment(
                paymentIntentClientSecret,
                {
                    payment_method: {
                        card: cardNumberElement,
                        billing_details: {
                            name: cardName,
                        },
                    },
                }
            );

            if (confirmError) {
                // Payment failed - increment attempt counter
                failedPaymentAttempts++;
                cardErrors.textContent = confirmError.message;
                cardErrors.style.display = 'block';
                payButton.disabled = false;
                payButton.textContent = 'Complete Payment';
                return;
            }

            // Payment succeeded, create booking and confirm payment on server
            const confirmResponse = await fetch('/payments/confirm', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    payment_intent_id: paymentIntent.id,
                    booking_data: bookingData,
                    failed_attempts: failedPaymentAttempts,
                }),
            });

            const confirmData = await confirmResponse.json();

            if (!confirmResponse.ok) {
                throw new Error(confirmData.error || 'Failed to confirm payment');
            }

            // Success - redirect to bookings
            window.location.href = '/bookings?success=Booking Successful';

        } catch (error) {
            cardErrors.textContent = error.message || 'An error occurred during payment';
            cardErrors.style.display = 'block';
            payButton.disabled = false;
            payButton.textContent = 'Complete Payment';
        }
    }

    paymentOptions.forEach(btn => {
        btn.addEventListener('click', function () {
            paymentOptions.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const method = btn.dataset.method;
            paymentBoxes.forEach(pb => pb.style.display = 'none');
            
            if (method === 'pay_on_arrival') {
                document.getElementById('pay_on_arrival_box').style.display = 'block';
            } else if (method === 'credit_card') {
                document.getElementById('credit_card_box').style.display = 'block';
                
                // Initialize Stripe if not already done
                if (!stripe) {
                    initializeStripe();
                }
                // show split toggle if available
                if (splitSection) splitSection.style.display = 'none';
            } else if (method === 'bank_transfer') {
                document.getElementById('bank_transfer_box').style.display = 'block';
                const ref = generateBankRef();
                const dl = generateDeadline();
                if (bankRefEl) bankRefEl.textContent = ref;
                if (bankDeadlineEl) bankDeadlineEl.textContent = dl;
                if (bankInput) bankInput.value = ref;
            }
            
            // refresh displayed price when switching methods
            computePrice();
            // if switching away from credit card, clear split UI
            if (method !== 'credit_card' && splitSection) {
                splitSection.style.display = 'none';
                if (toggleSplitBtn) toggleSplitBtn.classList.remove('active');
                clearSplitInputs();
            }
        });
    });

    if (backBtn) backBtn.addEventListener('click', function () {
        paymentStep.style.display = 'none';
        bookingControls.style.display = 'block';
        if (bookingInputs) bookingInputs.style.display = 'block';
    });

    // Handle payment completion
    completeBtns.forEach(cb => {
        cb.addEventListener('click', async function () {
            const method = cb.dataset.method;
            let pm = document.querySelector('input[name="payment_method"]');
            if (!pm) {
                pm = document.createElement('input');
                pm.type = 'hidden';
                pm.name = 'payment_method';
                bookingForm.appendChild(pm);
            }
            pm.value = method;

            if (method === 'credit_card') {
                // For Stripe payments, validate booking first but don't create it yet
                // Booking will be created only after successful payment
                const tokenMeta = document.querySelector('meta[name="csrf-token"]');
                const token = tokenMeta ? tokenMeta.getAttribute('content') : '';

                // Ensure price is computed
                computePrice();

                // Validate booking first
                try {
                    const isValid = await validateBooking();
                    if (!isValid) {
                        return;
                    }

                    // Store booking data for later creation after successful payment
                    // Only update booking data if it's the first attempt or booking details changed
                    const currentBookingKey = `${bookingForm.querySelector('input[name="listing_id"]').value}-${availabilitySelect ? availabilitySelect.value : ''}-${checkInEl ? checkInEl.value : ''}-${checkOutEl ? checkOutEl.value : ''}-${document.getElementById('num_guests') ? document.getElementById('num_guests').value : ''}`;
                    
                    if (!bookingData || bookingData._key !== currentBookingKey) {
                        // Reset failed attempts when booking details change
                        failedPaymentAttempts = 0;
                    }
                    
                    bookingData = {
                        listing_id: bookingForm.querySelector('input[name="listing_id"]').value,
                        availability_id: availabilitySelect ? availabilitySelect.value : '',
                        check_in_date: checkInEl ? checkInEl.value : '',
                        check_out_date: checkOutEl ? checkOutEl.value : '',
                        num_guests: document.getElementById('num_guests') ? document.getElementById('num_guests').value : '',
                        // Collect split emails from the UI (if any)
                        split_emails: (function(){
                            const out = [];
                            const rows = document.querySelectorAll('.split-email-input');
                            rows.forEach(r => {
                                const v = r.value.trim();
                                if (v) out.push(v);
                            });
                            return out;
                        })(),
                        total_price: computePrice(),
                        _key: currentBookingKey, // Internal key to track if booking changed
                    };

                    // Enable pay button
                    const payButton = document.getElementById('stripe-pay-button');
                    if (payButton) {
                        payButton.disabled = false;
                    }

                    // Process Stripe payment
                    await handleStripePayment();

                } catch (error) {
                    alert(error.message || 'Failed to validate booking');
                }
            } else {
                // For other payment methods, submit form normally
                computePrice();
                bookingForm.submit();
            }
        });
    });

    // Handle cardholder name input for Stripe
    const ccNameEl = document.getElementById('cc_name');
    if (ccNameEl) {
        ccNameEl.addEventListener('input', function() {
            // Update pay button state when name changes
            const payButton = document.getElementById('stripe-pay-button');
            if (payButton && stripe) {
                // Check if card elements are valid
                const cardName = this.value.trim();
                if (cardName.length > 0) {
                    // Enable button - Stripe will validate card on submit
                    payButton.disabled = false;
                } else {
                    payButton.disabled = true;
                }
            }
        });
    }
});
