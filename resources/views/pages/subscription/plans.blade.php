@extends('layouts.app')
@section('title', 'Choose Your Plan')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            
            <!-- Header -->
            <div class="text-center mb-5">
                <h1 class="fw-bold text-white">Choose Your Plan</h1>
                <p class="lead text-white">Select a tier and billing cycle to get started</p>
            </div>

            <!-- Plan Selection Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-5">
                    
                    <!-- Tier Selection -->
                    <h5 class="fw-bold mb-4 text-black">1. Select Your Tier</h5>
                    <div class="row g-3 mb-5">
                        <div class="col-md-4">
                            <div class="form-check card h-100 border-2 p-0 m-0">
                                <input type="radio" class="form-check-input d-none" name="tier" id="tierSilver" value="Silver">
                                <label class="form-check-label card-body text-center py-4 cursor-pointer" for="tierSilver">
                                    <i class="fa-solid fa-medal fs-2 d-block mb-2 text-secondary"></i>
                                    <span class="fw-bold d-block">Silver</span>
                                    <small class="text-muted">5% discount</small>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check card h-100 border-2 selected-card p-0 m-0">
                                <input type="radio" class="form-check-input d-none" name="tier" id="tierGold" value="Gold" checked>
                                <label class="form-check-label card-body text-center py-4 cursor-pointer" for="tierGold">
                                    <i class="fa-solid fa-crown fs-2 d-block mb-2 text-warning"></i>
                                    <span class="fw-bold d-block">Gold</span>
                                    <small class="text-muted">10% discount</small>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check card h-100 border-2 p-0 m-0">
                                <input type="radio" class="form-check-input d-none" name="tier" id="tierPlatinum" value="Platinum">
                                <label class="form-check-label card-body text-center py-4 cursor-pointer" for="tierPlatinum">
                                    <i class="fa-solid fa-gem fs-2 d-block mb-2 text-dark"></i>
                                    <span class="fw-bold d-block">Platinum</span>
                                    <small class="text-muted">20% discount</small>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Billing Cycle -->
                    <h5 class="fw-bold mb-4 text-black">2. Select Billing Cycle</h5>
                    <div class="row g-3 mb-5">
                        <div class="col-md-6">
                            <div class="form-check card h-100 border-2 selected-card p-0 m-0">
                                <input type="radio" class="form-check-input d-none" name="billing" id="billingMonthly" value="Monthly" checked>
                                <label class="form-check-label card-body text-center py-4 cursor-pointer" for="billingMonthly">
                                    <span class="fw-bold fs-5 d-block">Monthly</span>
                                    <small class="text-muted">Pay month by month</small>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check card h-100 border-2 p-0 m-0">
                                <input type="radio" class="form-check-input d-none" name="billing" id="billingAnnual" value="Annual">
                                <label class="form-check-label card-body text-center py-4 cursor-pointer" for="billingAnnual">
                                    <span class="fw-bold fs-5 d-block">Annual</span>
                                    <small class="text-muted">Save 2 months!</small>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Price Summary -->
                    <div class="card bg-light border-0 mb-4">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="fw-bold mb-1">Total</h5>
                                    <small class="text-muted" id="planSummary">Gold · Monthly</small>
                                </div>
                                <div class="text-end">
                                    <span class="display-6 fw-bold text-wine" id="planPrice">€14.99</span>
                                    <small class="text-muted" id="planPeriod">/month</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="button" class="btn btn-primary w-100 py-3 fw-bold fs-5" disabled>
                        <i class="fa-solid fa-lock me-2"></i>Proceed to Payment
                    </button>
                    <p class="text-center text-muted mt-3 mb-0">
                        <i class="fa-solid fa-shield-halved me-1"></i>
                        Payment integration coming soon
                    </p>

                </div>
            </div>

            <!-- Back Link -->
            <div class="text-center mt-4">
                <a href="{{ route('subscription.info') }}" class="text-decoration-none text-white">
                    <i class="fa-solid fa-arrow-left me-1"></i>Back to Premium Info
                </a>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const prices = {
        Silver: { Monthly: 7.99, Annual: 79.99 },
        Gold: { Monthly: 14.99, Annual: 149.99 },
        Platinum: { Monthly: 24.99, Annual: 249.99 }
    };

    // Check for tier in URL query parameter
    const urlParams = new URLSearchParams(window.location.search);
    const tierParam = urlParams.get('tier');
    if (tierParam && ['Silver', 'Gold', 'Platinum'].includes(tierParam)) {
        const tierInput = document.getElementById('tier' + tierParam);
        if (tierInput) {
            // Uncheck all tier inputs first
            document.querySelectorAll('input[name="tier"]').forEach(input => input.checked = false);
            tierInput.checked = true;
        }
    }

    function updatePrice() {
        const tier = document.querySelector('input[name="tier"]:checked').value;
        const billing = document.querySelector('input[name="billing"]:checked').value;
        const price = prices[tier][billing];
        const period = billing === 'Monthly' ? '/month' : '/year';
        
        document.getElementById('planPrice').textContent = '€' + price.toFixed(2);
        document.getElementById('planPeriod').textContent = period;
        document.getElementById('planSummary').textContent = tier + ' · ' + billing;
    }

    function updateSelection() {
        // Reset all tier cards
        document.querySelectorAll('input[name="tier"]').forEach(input => {
            const card = input.closest('.card');
            if (input.checked) {
                card.classList.add('selected-card');
            } else {
                card.classList.remove('selected-card');
            }
        });

        // Reset all billing cards
        document.querySelectorAll('input[name="billing"]').forEach(input => {
            const card = input.closest('.card');
            if (input.checked) {
                card.classList.add('selected-card');
            } else {
                card.classList.remove('selected-card');
            }
        });

        updatePrice();
    }

    document.querySelectorAll('input[name="tier"], input[name="billing"]').forEach(input => {
        input.addEventListener('change', updateSelection);
    });

    // Initialize
    updateSelection();
});
</script>
@endpush

@push('styles')
<style>
    .cursor-pointer { cursor: pointer; }
    .form-check.card label:hover { background-color: rgba(128, 0, 32, 0.05); }
    .selected-card { border-color: var(--color-wine) !important; }
    .selected-card label { background-color: rgba(128, 0, 32, 0.05); }
</style>
@endpush
@endsection
