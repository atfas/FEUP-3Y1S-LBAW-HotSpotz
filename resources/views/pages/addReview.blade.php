@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Breadcrumbs -->
            @include('partials.page_header', [
                'breadcrumbs' => [
                    ['title' => 'My Bookings', 'url' => route('bookings.index')],
                    ['title' => $booking->availability->listing->title, 'url' => route('listing.show', $booking->availability->listing->id)],
                    ['title' => 'Add Review', 'url' => null],
                ]
            ])
             <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h4 class="mb-0 fw-bold text-wine">{{ $booking->availability->listing->title }} - Add Your Review</h4>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h5>{{ $booking->availability->listing->title }}</h5>
                        <p class="text-muted">{{ $booking->availability->listing->location_address }}</p>
                        <p><strong>Stay:</strong> {{ $booking->check_in_date->format('M d, Y') }} - {{ $booking->check_out_date->format('M d, Y') }}</p>
                    </div>

                    <form action="{{ route('reviews.store', $booking) }}" method="POST">
                        @csrf

                        <!-- Rating -->
                        <div class="mb-3">
                            <label class="form-label">Rating *</label>
                            <div class="star-rating">
                                @for($i = 5; $i >= 1; $i--)
                                <input type="radio" id="star{{ $i }}" name="rating" value="{{ $i }}" {{ old('rating') == $i ? 'checked' : '' }}>
                                <label for="star{{ $i }}">
                                    <i class="fas fa-star"></i>
                                </label>
                                @endfor
                            </div>
                            @error('rating')
                            <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Comment -->
                        <div class="mb-3">
                            <label for="comment" class="form-label">Your Review</label>
                            <textarea class="form-control @error('comment') is-invalid @enderror" 
                                      id="comment" 
                                      name="comment" 
                                      rows="5" 
                                      placeholder="Share your experience...">{{ old('comment') }}</textarea>
                            @error('comment')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-5">
                            <a href="{{ route('bookings.index') }}" class="text-decoration-none text-muted">
                                <i class="fa-solid fa-arrow-left me-1"></i> Back to Bookings
                            </a>
                            <button type="submit" class="btn btn-primary px-4">
                                Submit Review
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection