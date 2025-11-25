@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row">
        <!-- Listing Info -->
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
        
        <!-- Booking Form -->
        <div class="col-md-6">
            <div class="card shadow-lg border-0">
                <div class="card-body p-4">
                    <h3 class="card-title mb-4">
                        <i class="fa-solid fa-calendar-check"></i> Book Your Stay
                    </h3>
                    
                    @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    
                    <form action="{{ route('bookings.store') }}" method="POST" id="bookingForm">
                        @csrf
                        
                        <input type="hidden" name="listing_id" value="{{ $listing->id }}">
                        
                <!-- Input Fields -->
                        <div class="mb-3">
                            <label for="availability_id" class="form-label">
                                <i class="fa-solid fa-calendar-days"></i> Select Availability Period
                            </label>
                            <select class="form-select @error('availability_id') is-invalid @enderror" 
                                    id="availability_id" 
                                    name="availability_id" 
                                    required>
                                <option value="">Choose available dates...</option>
                                @foreach($listing->availabilities as $availability)
                                    <option value="{{ $availability->id }}" 
                                            {{ old('availability_id') == $availability->id ? 'selected' : '' }}>
                                        {{ $availability->start_date->format('M d, Y') }} - 
                                        {{ $availability->end_date->format('M d, Y') }} 
                                        (${{ number_format($availability->price_per_night, 2) }}/night)
                                    </option>
                                @endforeach
                            </select>
                            @error('availability_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        

                        <div class="mb-3">
                            <label for="check_in_date" class="form-label">
                                <i class="fa-solid fa-right-to-bracket"></i> Check-in Date
                            </label>
                            <input type="date" 
                                   class="form-control @error('check_in_date') is-invalid @enderror" 
                                   id="check_in_date" 
                                   name="check_in_date" 
                                   value="{{ old('check_in_date') }}"
                                   required>
                            @error('check_in_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        

                        <div class="mb-3">
                            <label for="check_out_date" class="form-label">
                                <i class="fa-solid fa-right-from-bracket"></i> Check-out Date
                            </label>
                            <input type="date" 
                                   class="form-control @error('check_out_date') is-invalid @enderror" 
                                   id="check_out_date" 
                                   name="check_out_date" 
                                   value="{{ old('check_out_date') }}"
                                   required>
                            @error('check_out_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        

                        <div class="mb-3">
                            <label for="num_guests" class="form-label">
                                <i class="fa-solid fa-user-group"></i> Number of Guests
                            </label>
                            <input type="number" 
                                   class="form-control @error('num_guests') is-invalid @enderror" 
                                   id="num_guests" 
                                   name="num_guests" 
                                   min="1" 
                                   max="{{ $listing->max_occupancy }}"
                                   value="{{ old('num_guests', 1) }}"
                                   required>
                            @error('num_guests')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>                        
                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="fa-solid fa-check"></i> Confirm Booking
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection
