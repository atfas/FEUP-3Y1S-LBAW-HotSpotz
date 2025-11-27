@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-white">
                    <h3 class="mb-0 text-wine">
                        <i class="fas fa-edit"></i> Edit Booking
                    </h3>
                </div>
                <div class="card-body">
                    <!-- Listing Info -->
                    <div class="border rounded p-3 mb-3">
                        <h5 class="text-wine">{{ $booking->availability->listing->title }}</h5>
                        <p class="mb-0">
                            <i class="fas fa-map-marker-alt text-wine"></i> {{ $booking->availability->listing->location_address }}
                        </p>
                        <hr>
                        <small>
                            <strong>Availability Period:</strong> 
                            {{ $booking->availability->start_date->format('M d, Y') }} - 
                            {{ $booking->availability->end_date->format('M d, Y') }}
                            <br>
                            <strong>Price:</strong> ${{ number_format($booking->availability->price_per_night, 2) }}/night
                            <br>
                            <strong>Min Stay:</strong> {{ $booking->availability->listing->minimum_stay }} night(s)
                            <br>
                            <strong>Max Guests:</strong> {{ $booking->availability->listing->max_occupancy }}
                        </small>
                    </div>

                    @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <form action="{{ route('bookings.update', $booking->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="check_in_date" class="form-label">
                                <i class="fas fa-calendar-check"></i> Check-in Date
                            </label>
                            <input type="date" 
                                   class="form-control @error('check_in_date') is-invalid @enderror" 
                                   id="check_in_date" 
                                   name="check_in_date" 
                                   value="{{ old('check_in_date', $booking->check_in_date->format('Y-m-d')) }}"
                                   required>
                            @error('check_in_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="check_out_date" class="form-label">
                                <i class="fas fa-calendar-times"></i> Check-out Date
                            </label>
                            <input type="date" 
                                   class="form-control @error('check_out_date') is-invalid @enderror" 
                                   id="check_out_date" 
                                   name="check_out_date" 
                                   value="{{ old('check_out_date', $booking->check_out_date->format('Y-m-d')) }}"
                                   required>
                            @error('check_out_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="num_guests" class="form-label">
                                <i class="fas fa-users"></i> Number of Guests
                            </label>
                            <input type="number" 
                                   class="form-control @error('num_guests') is-invalid @enderror" 
                                   id="num_guests" 
                                   name="num_guests" 
                                   min="1" 
                                   max="{{ $booking->availability->listing->max_occupancy }}"
                                   value="{{ old('num_guests', $booking->num_guests) }}"
                                   required>
                            @error('num_guests')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Booking
                            </button>
                            <a href="{{ route('bookings.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
