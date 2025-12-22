<!-- Premium Subscription Status Panel - Shows for active premium members -->
<div class="mt-5 pt-4 border-top">
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <!-- Header with badge -->
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center {{ $user->subscription->getTierColorClass() }}" 
                         style="width: 50px; height: 50px;">
                        <i class="{{ $user->subscription->getTierIcon() }} fs-4"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0">{{ $user->subscription->tier }} Member</h5>
                        <small class="text-muted">{{ $user->subscription->discount_percentage }}% discount on all bookings</small>
                    </div>
                </div>
                <span class="badge bg-success px-3 py-2">
                    <i class="fa-solid fa-check-circle me-1"></i>Active
                </span>
            </div>

            <!-- Subscription Details -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="bg-light rounded p-3 text-center">
                        <small class="text-muted d-block mb-1">Plan Type</small>
                        <span class="fw-bold">{{ $user->subscription->plan_type }}</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="bg-light rounded p-3 text-center">
                        <small class="text-muted d-block mb-1">Next Payment</small>
                        <span class="fw-bold">{{ $user->subscription->next_payment_date->format('M d, Y') }}</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="bg-light rounded p-3 text-center">
                        <small class="text-muted d-block mb-1">Total Savings</small>
                        <span class="fw-bold text-success">€{{ number_format($user->subscription->total_savings, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Member Since -->
            <div class="d-flex align-items-center gap-2 text-muted mb-4">
                <i class="fa-solid fa-calendar-check"></i>
                <small>Member since {{ $user->subscription->member_since->format('F Y') }}</small>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('subscription.info') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-arrows-rotate me-1"></i>Change Plan
                </a>
                <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#cancelSubscriptionModal">
                    <i class="fa-solid fa-xmark me-1"></i>Cancel Subscription
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Subscription Modal -->
<div class="modal fade" id="cancelSubscriptionModal" tabindex="-1" aria-labelledby="cancelSubscriptionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="cancelSubscriptionModalLabel">Cancel Subscription</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning mb-3">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>
                    Are you sure you want to cancel your {{ $user->subscription->tier }} subscription?
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Keep Subscription</button>
                <form action="{{ route('subscription.cancel') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger">
                        <i class="fa-solid fa-xmark me-1"></i>Cancel Subscription
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
