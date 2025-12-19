<div class="col-md-6">
    <div class="card shadow-sm">
        <div class="card-body">
            <h2 class="card-title">{{ $listing->title }}</h2>
            <p class="text-muted">
                <i class="fa-solid fa-location-dot"></i> {{ $listing->location_address }}
            </p>
            
            <hr>
            
            <div class="mb-3">
                <p><strong>Description:</strong></p>
                <p>{{ $listing->description }}</p>
            </div>
            
            <div class="row mb-3">
                <div class="col-6">
                    <p><strong><i class="fa-solid fa-users"></i> Max Occupancy:</strong></p>
                    <p>{{ $listing->max_occupancy }} guest(s)</p>
                </div>
                <div class="col-6">
                    <p><strong><i class="fa-solid fa-moon"></i> Minimum Stay:</strong></p>
                    <p>{{ $listing->minimum_stay }} night(s)</p>
                </div>
            </div>
            
            @if($listing->average_rating > 0)
            <div class="mb-3">
                <p><strong><i class="fa-solid fa-star text-warning"></i> Rating:</strong></p>
                <p>{{ number_format($listing->average_rating, 2) }} / 5.00 ({{ $listing->num_reviews }} reviews)</p>
            </div>
            @endif
            
            <div class="mb-3">
                <p><strong><i class="fa-solid fa-shield-halved"></i> Cancellation Policy:</strong></p>
                <p class="small">{{ $listing->cancellation_policy }}</p>
            </div>
        </div>
    </div>
</div>
