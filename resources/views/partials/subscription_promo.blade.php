<!-- Premium Subscription Promo Card - Shows for non-premium users -->
<div class="mt-5 pt-4 border-top">
    <a href="{{ route('subscription.info') }}" class="card border-0 shadow btn-primary text-decoration-none d-block">
        <div class="card-body p-4 text-white">
            <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="fa-solid fa-crown fs-4 text-warning"></i>
                        <h5 class="fw-bold mb-0">Become a Premium Member</h5>
                    </div>
                    <p class="mb-3 opacity-75">Unlock exclusive discounts on all bookings and save more on your travels!</p>
                    
                    <!-- Tier Overview -->
                    <div class="d-flex flex-wrap gap-3 mb-0">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-secondary">Silver</span>
                            <small>5% off</small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-warning text-dark">Gold</span>
                            <small>10% off</small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-light text-dark">Platinum</span>
                            <small>20% off</small>
                        </div>
                    </div>
                </div>
                
                <!-- Arrow Icon -->
                <div class="d-flex align-items-center">
                    <i class="fa-solid fa-arrow-right fs-4"></i>
                </div>
            </div>
        </div>
    </a>
</div>
