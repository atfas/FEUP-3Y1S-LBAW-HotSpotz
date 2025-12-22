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
                
                                <div id="booking-inputs">
                                    <!-- Calendar Based Selection -->
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fa-solid fa-calendar-days"></i> Select Your Dates
                                        </label>
                                        <div id="booking_calendar_container" style="max-width:100%;"></div>
                                        <input type="hidden" id="check_in_date" name="check_in_date" value="{{ old('check_in_date') }}">
                                        <input type="hidden" id="check_out_date" name="check_out_date" value="{{ old('check_out_date') }}">
                                        <input type="hidden" id="availability_id" name="availability_id" value="{{ old('availability_id') }}">
                                        <div class="form-text small">Only dates inside an availability are selectable.</div>
                                        <div id="booking_calendar_error_message" class="text-danger small mt-1" style="min-height: 1.2em;"></div>
                                    </div>
                
                                    <!-- Price Summary -->
                                    <div id="booking_price_summary" class="mb-3" style="display: none;">
                                        <hr>
                                        <h5 class="mb-3">Price Summary</h5>
                                        <div class="d-flex justify-content-between">
                                            <div class="small text-muted">Price per night</div>
                                            <div class="fw-bold" id="booking_price_per_night">N/A</div>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <div class="small text-muted">Days</div>
                                            <div class="fw-bold" id="booking_days">0</div>
                                        </div>
                                        <hr>
                                        <div class="d-flex justify-content-between">
                                            <div class="fw-bold">Total</div>
                                            <div class="fw-bold text-wine" id="booking_total">$0.00</div>
                                        </div>
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
                </div>
                <div id="booking-controls">                    <button type="button" id="selectPaymentBtn" class="btn btn-primary btn-lg w-100">
                        <i class="fa-solid fa-credit-card"></i> Select Payment
                    </button>
                </div>

                @include('partials.payment_form')
            </form>
        </div>
    </div>
</div>
