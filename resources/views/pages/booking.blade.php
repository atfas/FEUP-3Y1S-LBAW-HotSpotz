@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <!-- Listing Info Column -->
        <div class="col-md-8 col-lg-6 mx-auto">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="card-title">{{ $listing->title }}</h2>
                    <p class="text-muted">
                        {{ $listing->location_address }}
                    </p>
                    <div class="mb-3">
                        <p><strong>Description:</strong></p>
                        <p>{{ $listing->description }}</p>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-6">
                            <p><strong>Max Occupancy:</strong></p>
                            <p>{{ $listing->max_occupancy }} guest(s)</p>
                        </div>
                        <div class="col-6">
                            <p><strong> Minimum Stay:</strong></p>
                            <p>{{ $listing->minimum_stay }} night(s)</p>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <p><strong> Rating:</strong></p>
                        <p>{{ number_format($listing->average_rating, 2) }} / 5.00 ({{ $listing->num_reviews }} reviews)</p>
                    </div>
                    
                    <div class="mb-3">
                        <p><strong> Cancellation Policy:</strong></p>
                        <p class="small">{{ $listing->cancellation_policy }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
