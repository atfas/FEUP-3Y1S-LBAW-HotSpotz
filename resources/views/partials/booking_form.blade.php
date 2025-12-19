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
                <div id="booking-inputs">
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
                            <option value="{{ $availability->id }}" data-price="{{ $availability->price_per_night }}"
                                    {{ old('availability_id') == $availability->id ? 'selected' : '' }}>
                                {{ $availability->start_date->format('M d, Y') }} - 
                                {{ $availability->end_date->format('M d, Y') }} 
                                (${{ number_format($availability->price_per_night, 2) }}/night)
                            </option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback d-block" id="availability_id_error" style="display:none"></div>
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
                    <div class="invalid-feedback d-block" id="check_in_date_error" style="display:none"></div>
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
                    <div class="invalid-feedback d-block" id="check_out_date_error" style="display:none"></div>
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
                    <div class="invalid-feedback d-block" id="num_guests_error" style="display:none"></div>
                </div>                        
                <input type="hidden" name="total_price" id="total_price_input" value="">
                </div>
                <div id="booking-controls">
                    <button type="button" id="selectPaymentBtn" class="btn btn-primary btn-lg w-100">
                        <i class="fa-solid fa-credit-card"></i> Select Payment
                    </button>
                </div>

                @include('partials.payment_form')
            </form>
        </div>
    </div>
</div>
