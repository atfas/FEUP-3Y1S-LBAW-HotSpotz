@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Leave a Review</h4>
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
                                <input type="radio" id="star{{ $i }}" name="rating" value="{{ $i }}" {{ old('rating') == $i ? 'checked' : '' }} required>
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

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Submit Review</button>
                            <a href="{{ route('bookings.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.star-rating {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
    gap: 5px;
}

.star-rating input {
    display: none;
}

.star-rating label {
    cursor: pointer;
    font-size: 2rem;
    color: #ddd;
    transition: color 0.2s;
}

.star-rating input:checked ~ label,
.star-rating label:hover,
.star-rating label:hover ~ label {
    color: #ffc107;
}
</style>
@endsection