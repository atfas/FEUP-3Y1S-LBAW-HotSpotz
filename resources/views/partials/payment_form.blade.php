<div id="payment-step" style="display:none; margin-top:1rem;">
    <div class="mb-3 d-flex gap-2">
        <button type="button" class="btn btn-outline-secondary w-100 payment-option" data-method="pay_on_arrival">Pay On Arrival</button>
        <button type="button" class="btn btn-outline-secondary w-100 payment-option" data-method="credit_card">Credit Card</button>
        <button type="button" class="btn btn-outline-secondary w-100 payment-option" data-method="bank_transfer">Bank Transfer</button>
    </div>

    <div id="payment-forms">
        <div id="pay_on_arrival_box" class="payment-box" style="display:none;">
            <p class="mb-2">Final price: <strong class="final-price">$0.00</strong></p>
            <button type="button" class="btn btn-success w-100 complete-payment" data-method="pay_on_arrival">Complete Payment</button>
        </div>

        <div id="credit_card_box" class="payment-box" style="display:none;">
            <p class="mb-2">Final price: <strong class="final-price">$0.00</strong></p>
            <div class="mb-2">
                <label class="form-label">Cardholder Name</label>
                <input type="text" class="form-control" id="cc_name" placeholder="Full name on card">
                <div class="invalid-feedback d-block" id="cc_name_error" style="display:none"></div>
            </div>
            <div class="mb-2">
                <label class="form-label">Card Number</label>
                <input type="text" class="form-control" id="cc_number" placeholder="4242 4242 4242 4242" inputmode="numeric">
                <div class="invalid-feedback d-block" id="cc_number_error" style="display:none"></div>
            </div>
            <div class="row g-2">
                <div class="col-6">
                    <label class="form-label">Expiry (MM/YY)</label>
                    <input type="text" class="form-control" id="cc_expiry" placeholder="MM/YY" inputmode="numeric">
                    <div class="invalid-feedback d-block" id="cc_expiry_error" style="display:none"></div>
                </div>
                <div class="col-6">
                    <label class="form-label">CVC</label>
                    <input type="text" class="form-control" id="cc_cvc" placeholder="CVC" inputmode="numeric">
                    <div class="invalid-feedback d-block" id="cc_cvc_error" style="display:none"></div>
                </div>
            </div>
            <div class="mt-3">
                <button type="button" class="btn btn-success w-100 complete-payment" data-method="credit_card">Complete Payment</button>
            </div>
        </div>

        <div id="bank_transfer_box" class="payment-box" style="display:none;">
            <p>Entity: <strong>PT50BANK00000000000000</strong></p>
            <p>Reference: <strong id="bank_ref">-</strong></p>
            <p>Deadline: <strong id="bank_deadline">-</strong></p>
            <p class="mb-2">Final price: <strong class="final-price">$0.00</strong></p>
            <input type="hidden" name="bank_reference" id="bank_reference_input" value="">
            <div class="mt-3">
                <button type="button" class="btn btn-success w-100 complete-payment" data-method="bank_transfer">Complete Payment</button>
            </div>
        </div>
    </div>

    <div class="mt-3 d-flex gap-2">
        <button type="button" id="backToBooking" class="btn btn-outline-secondary">Back to booking selection</button>
    </div>
</div>
