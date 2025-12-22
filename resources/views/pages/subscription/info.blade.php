@extends('layouts.app')
@section('title', 'Premium Membership')

@section('content')
<div class="container py-5">
    @include('partials.page_header', [
        'breadcrumbs' => [
            ['title' => 'Profile', 'url' => route('profile.show')],
            ['title' => 'Subsription Info', 'url' => null],
        ]
    ])
    <div class="row justify-content-center">
        <div class="col-lg-10">
            
            <!-- Header -->
            <div class="text-center mb-5">
                <h1 class="fw-bold text-white">Premium Membership</h1>
                <p class="lead text-white">Save on every booking with our exclusive membership tiers</p>
            </div>

            <!-- Tier Cards -->
            <div class="row g-4 mb-5">
                <!-- Silver Tier -->
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-header text-center py-4 bg-secondary text-white">
                            <i class="fa-solid fa-medal fs-1 mb-2"></i>
                            <h3 class="fw-bold mb-0">Silver</h3>
                        </div>
                        <div class="card-body text-center p-4">
                            <div class="display-4 fw-bold mb-2">5%</div>
                            <p class="text-muted mb-4">discount on all bookings</p>
                            
                            <div class="border-top pt-4">
                                <h5 class="fw-bold mb-3 text-wine">Monthly</h5>
                                <div class="display-6 fw-bold">€7.99</div>
                                <small class="text-muted">/month</small>
                            </div>
                            
                            <div class="border-top pt-4 mt-4">
                                <h5 class="fw-bold mb-3 text-wine">Annual</h5>
                                <div class="display-6 fw-bold">€79.99</div>
                                <small class="text-muted">/year (save €15.89)</small>
                            </div>
                            
                            <a href="{{ route('subscription.plans', ['tier' => 'Silver']) }}" class="btn btn-outline-secondary w-100 mt-4">
                                Choose Silver
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Gold Tier -->
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-lg position-relative">
                        <span class="position-absolute top-0 start-50 translate-middle badge bg-danger px-3 py-2">Most Popular</span>
                        <div class="card-header text-center py-4 bg-warning text-dark">
                            <i class="fa-solid fa-crown fs-1 mb-2"></i>
                            <h3 class="fw-bold mb-0">Gold</h3>
                        </div>
                        <div class="card-body text-center p-4">
                            <div class="display-4 fw-bold mb-2">10%</div>
                            <p class="text-muted mb-4">discount on all bookings</p>
                            
                            <div class="border-top pt-4">
                                <h5 class="fw-bold mb-3 text-wine">Monthly</h5>
                                <div class="display-6 fw-bold">€14.99</div>
                                <small class="text-muted">/month</small>
                            </div>
                            
                            <div class="border-top pt-4 mt-4">
                                <h5 class="fw-bold mb-3 text-wine">Annual</h5>
                                <div class="display-6 fw-bold">€149.99</div>
                                <small class="text-muted">/year (save €29.89)</small>
                            </div>
                            
                            <a href="{{ route('subscription.plans', ['tier' => 'Gold']) }}" class="btn btn-outline-warning w-100 mt-4 fw-bold">
                                Choose Gold
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Platinum Tier -->
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-header text-center py-4 bg-light text-dark">
                            <i class="fa-solid fa-gem fs-1 mb-2"></i>
                            <h3 class="fw-bold mb-0">Platinum</h3>
                        </div>
                        <div class="card-body text-center p-4">
                            <div class="display-4 fw-bold mb-2">20%</div>
                            <p class="text-muted mb-4">discount on all bookings</p>
                            
                            <div class="border-top pt-4">
                                <h5 class="fw-bold mb-3 text-wine">Monthly</h5>
                                <div class="display-6 fw-bold">€24.99</div>
                                <small class="text-muted">/month</small>
                            </div>
                            
                            <div class="border-top pt-4 mt-4">
                                <h5 class="fw-bold mb-3 text-wine">Annual</h5>
                                <div class="display-6 fw-bold">€249.99</div>
                                <small class="text-muted">/year (save €49.89)</small>
                            </div>
                            
                            <a href="{{ route('subscription.plans', ['tier' => 'Platinum']) }}" class="btn btn-outline-dark w-100 mt-4">
                                Choose Platinum
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Benefits Section -->
            <div class="card border-0 shadow-sm mb-5">
                <div class="card-body p-5">
                    <h3 class="fw-bold text-center mb-4 text-wine">
                        <i class="fa-solid fa-star me-2"></i>Premium Benefits
                    </h3>
                    
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="d-flex gap-3">
                                <div class="text-wine"><i class="fa-solid fa-check-circle fs-4"></i></div>
                                <div>
                                    <h6 class="fw-bold mb-1">Instant Discounts</h6>
                                    <p class="text-muted mb-0">Automatic discounts applied to all your bookings</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-3">
                                <div class="text-wine"><i class="fa-solid fa-check-circle fs-4"></i></div>
                                <div>
                                    <h6 class="fw-bold mb-1">Savings Tracker</h6>
                                    <p class="text-muted mb-0">Track how much you've saved with your membership</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-3">
                                <div class="text-wine"><i class="fa-solid fa-check-circle fs-4"></i></div>
                                <div>
                                    <h6 class="fw-bold mb-1">Priority Support</h6>
                                    <p class="text-muted mb-0">Get faster responses from our support team</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-3">
                                <div class="text-wine"><i class="fa-solid fa-check-circle fs-4"></i></div>
                                <div>
                                    <h6 class="fw-bold mb-1">Exclusive Offers</h6>
                                    <p class="text-muted mb-0">Access to members-only deals and promotions</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-3">
                                <div class="text-wine"><i class="fa-solid fa-check-circle fs-4"></i></div>
                                <div>
                                    <h6 class="fw-bold mb-1">Flexible Plans</h6>
                                    <p class="text-muted mb-0">Choose monthly or annual billing that suits you</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-3">
                                <div class="text-wine"><i class="fa-solid fa-check-circle fs-4"></i></div>
                                <div>
                                    <h6 class="fw-bold mb-1">Cancel Anytime</h6>
                                    <p class="text-muted mb-0">No long-term commitment, cancel when you want</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FAQ Section -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-5">
                    <h3 class="fw-bold text-center mb-4 text-wine">
                        <i class="fa-solid fa-circle-question me-2"></i>Frequently Asked Questions
                    </h3>
                    
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item border-0 border-bottom">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    How does the discount work?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted">
                                    Your tier discount is automatically applied to every booking you make. The discount is calculated on the total booking price before any other promotions.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item border-0 border-bottom">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    Can I upgrade my tier?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted">
                                    Yes! You can upgrade your tier at any time. 
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item border-0 border-bottom">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    What happens when I cancel?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted">
                                    When you cancel, you'll lose all your premium benefits. Be smart when choosing if you want to cancel your subscription.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item border-0">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                    Is the annual plan worth it?
                                </button>
                            </h2>
                            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted">
                                    The annual plan saves you about 2 months worth of subscription fees compared to paying monthly. If you travel frequently, it's definitely worth it!
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
