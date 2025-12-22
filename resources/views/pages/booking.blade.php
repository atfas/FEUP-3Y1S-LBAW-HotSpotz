@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row">
        @include('partials.listing_info')
        @include('partials.booking_form')
    </div>
</div>

@push('styles')
<link href="{{ asset('css/reservations_calendar.css') }}" rel="stylesheet">
@endpush

@push('scripts')
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        window.stripePublishableKey = '{{ config('services.stripe.key') }}';
    </script>
    <script src="{{ asset('js/payment.js') }}" defer></script>
    
    <!-- Calendar scripts -->
    <script src="{{ asset('vendor/fullcalendar/core.global.min.js') }}" defer></script>
    <script src="{{ asset('vendor/fullcalendar/daygrid.global.min.js') }}" defer></script>
    <script src="{{ asset('vendor/fullcalendar/interaction.global.min.js') }}" defer></script>
    <script src="{{ asset('js/booking_calendar/booking_calendar.js') }}" defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.availabilities = {!! json_encode($availabilities ?? []) !!};
            window.bookings = {!! json_encode($bookings ?? []) !!};
            const premiumDiscount = {{ Auth::check() && Auth::user()->isPremium() ? Auth::user()->getDiscountPercentage() : 0 }};

            function parseLocalDate(s){ if(!s) return null; const parts = s.split('-'); if(parts.length!==3) return null; return new Date(parts[0], parseInt(parts[1],10)-1, parts[2]); }
            function countDays(a,b){ if(!a||!b) return 0; const da=parseLocalDate(a); const db=parseLocalDate(b); if (!da || !db) return 0; const diff=(db - da)/(1000*60*60*24); return Math.max(0, Math.round(diff)) + 1; }
            function formatMoney(v){ if (v === null || v === undefined) return 'N/A'; return '$' + Number(v).toFixed(2); }

            window.updateBookingPrice = function(selection) {
                const summary = document.getElementById('booking_price_summary');
                const pricePerNightEl = document.getElementById('booking_price_per_night');
                const daysEl = document.getElementById('booking_days');
                const totalEl = document.getElementById('booking_total');
                const totalPriceInput = document.getElementById('total_price_input');
                const availabilityIdInput = document.getElementById('availability_id');

                if (!summary || !pricePerNightEl || !daysEl || !totalEl || !totalPriceInput || !availabilityIdInput) return;

                if (!selection || !selection.checkin || !selection.checkout || !selection.availability) {
                    summary.style.display = 'none';
                    totalPriceInput.value = '';
                    availabilityIdInput.value = '';
                    availabilityIdInput.removeAttribute('data-price');
                    return;
                }

                const { checkin, checkout, availability } = selection;
                
                const perNight = parseFloat(availability.price_per_night);
                const days = countDays(checkin, checkout);
                const nights = days > 0 ? days - 1 : 0;
                
                let finalPerNight = perNight;
                if (premiumDiscount > 0 && perNight !== null) {
                    finalPerNight = perNight * (1 - premiumDiscount / 100);
                }
                const total = (finalPerNight !== null && nights > 0) ? (finalPerNight * nights) : 0;
                
                // Update hidden inputs for form submission
                availabilityIdInput.value = availability.id;
                availabilityIdInput.dataset.price = perNight; // for payment.js
                totalPriceInput.value = total;

                // Update visual summary
                pricePerNightEl.textContent = formatMoney(perNight);
                daysEl.textContent = days;
                totalEl.textContent = formatMoney(total);
                summary.style.display = 'block';
            }
        });
    </script>
@endpush

@endsection
