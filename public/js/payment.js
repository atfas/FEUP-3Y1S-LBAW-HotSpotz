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
        if (availabilitySelect) {
            const opt = availabilitySelect.options[availabilitySelect.selectedIndex];
            if (opt && opt.dataset && opt.dataset.price) pricePerNight = parsePrice(opt.dataset.price);
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

        document.querySelectorAll('.final-price').forEach(el => el.textContent = '$' + formatted);
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
            if (data && data.errors) {
                Object.keys(data.errors).forEach(k => setBookingError(k, data.errors[k]));
            } else {
                alert('Validation failed');
            }
            return false;
        } catch (e) {
            console.error('Validation request failed', e);
            alert('Unable to validate booking');
            return false;
        }
    }

    paymentOptions.forEach(btn => {
        btn.addEventListener('click', function () {
            paymentOptions.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const method = btn.dataset.method;
            paymentBoxes.forEach(pb => pb.style.display = 'none');
            if (method === 'pay_on_arrival') document.getElementById('pay_on_arrival_box').style.display = 'block';
            if (method === 'credit_card') document.getElementById('credit_card_box').style.display = 'block';
            if (method === 'bank_transfer') {
                document.getElementById('bank_transfer_box').style.display = 'block';
                const ref = generateBankRef();
                const dl = generateDeadline();
                if (bankRefEl) bankRefEl.textContent = ref;
                if (bankDeadlineEl) bankDeadlineEl.textContent = dl;
                if (bankInput) bankInput.value = ref;
            }
            // refresh displayed price when switching methods
            computePrice();
        });
    });

    if (backBtn) backBtn.addEventListener('click', function () {
        paymentStep.style.display = 'none';
        bookingControls.style.display = 'block';
        if (bookingInputs) bookingInputs.style.display = 'block';
    });

    // create persistent hidden inputs for cc fields so we can update live
    function ensureHiddenField(name) {
        let hi = document.querySelector('input[name="' + name + '"]');
        if (!hi) {
            hi = document.createElement('input');
            hi.type = 'hidden';
            hi.name = name;
            bookingForm.appendChild(hi);
        }
        return hi;
    }
    const hiddenCcName = ensureHiddenField('cc_name');
    const hiddenCcNumber = ensureHiddenField('cc_number');
    const hiddenCcExpiry = ensureHiddenField('cc_expiry');
    const hiddenCcCvc = ensureHiddenField('cc_cvc');

    const ccNameEl = document.getElementById('cc_name');
    const ccNumberEl = document.getElementById('cc_number');
    const ccExpiryEl = document.getElementById('cc_expiry');
    const ccCvcEl = document.getElementById('cc_cvc');

    function setError(id, msg) {
        const err = document.getElementById(id + '_error');
        const el = document.getElementById(id);
        if (err) { err.textContent = msg; err.style.display = msg ? 'block' : 'none'; }
        if (el) el.classList.toggle('is-invalid', !!msg);
    }

    function normalizeNameRaw(raw) {
        const normalized = raw.normalize ? raw.normalize('NFD').replace(/\p{Diacritic}/gu, '') : raw;
        return normalized.toUpperCase().replace(/[^A-Z ]/g, '');
    }

    function formatCardNumberDisplay(digits) {
        return digits.replace(/(\d{4})(?=\d)/g, '$1 ').trim();
    }

    function handleNameInput() {
        if (!ccNameEl) return;
        const raw = ccNameEl.value || '';
        const fixed = normalizeNameRaw(raw);
        if (ccNameEl.value !== fixed) ccNameEl.value = fixed;
        hiddenCcName.value = fixed;
        setError('cc_name', fixed.length === 0 ? 'Cardholder name required' : '');
    }

    function handleNumberInput() {
        if (!ccNumberEl) return;
        const raw = ccNumberEl.value || '';
        const digits = raw.replace(/\D/g, '').slice(0,16);
        const disp = formatCardNumberDisplay(digits);
        if (ccNumberEl.value !== disp) ccNumberEl.value = disp;
        hiddenCcNumber.value = digits;
        setError('cc_number', digits.length === 16 ? '' : 'Card number must be 16 digits');
    }

    function handleExpiryInput() {
        if (!ccExpiryEl) return;
        const raw = ccExpiryEl.value || '';
        const digits = raw.replace(/\D/g, '').slice(0,4);
        let disp = digits;
        if (digits.length > 2) disp = digits.slice(0,2) + '/' + digits.slice(2);
        if (ccExpiryEl.value !== disp) ccExpiryEl.value = disp;
        hiddenCcExpiry.value = digits;

        if (digits.length < 4) {
            setError('cc_expiry', 'Expiry must be MMYY');
            return;
        }
        const mm = parseInt(digits.slice(0,2), 10);
        const yy = parseInt(digits.slice(2,4), 10);
        if (!(mm >= 1 && mm <= 12)) {
            setError('cc_expiry', 'Invalid expiry month');
            return;
        }
        const now = new Date();
        const fullYear = 2000 + yy;
        const expDate = new Date(fullYear, mm - 1, 1);
        expDate.setMonth(expDate.getMonth() + 1);
        if (expDate <= now) {
            setError('cc_expiry', 'Card expired');
            return;
        }
        setError('cc_expiry', '');
    }

    function handleCvcInput() {
        if (!ccCvcEl) return;
        const raw = ccCvcEl.value || '';
        const digits = raw.replace(/\D/g, '').slice(0,3);
        if (ccCvcEl.value !== digits) ccCvcEl.value = digits;
        hiddenCcCvc.value = digits;
        setError('cc_cvc', digits.length === 3 ? '' : 'CVC must be 3 digits');
    }

    if (ccNameEl) ccNameEl.addEventListener('input', handleNameInput);
    if (ccNumberEl) ccNumberEl.addEventListener('input', handleNumberInput);
    if (ccExpiryEl) ccExpiryEl.addEventListener('input', handleExpiryInput);
    if (ccCvcEl) ccCvcEl.addEventListener('input', handleCvcInput);

    function validateCardFieldsInline() {
        handleNameInput(); handleNumberInput(); handleExpiryInput(); handleCvcInput();
        const ok = !document.getElementById('cc_name').classList.contains('is-invalid') &&
                   !document.getElementById('cc_number').classList.contains('is-invalid') &&
                   !document.getElementById('cc_expiry').classList.contains('is-invalid') &&
                   !document.getElementById('cc_cvc').classList.contains('is-invalid');
        return ok;
    }

    completeBtns.forEach(cb => {
        cb.addEventListener('click', function () {
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
                if (!validateCardFieldsInline()) {
                    // focus first invalid
                    const first = bookingForm.querySelector('.is-invalid');
                    if (first) first.focus();
                    return;
                }
            }

            // ensure price included
            computePrice();

            bookingForm.submit();
        });
    });
});
