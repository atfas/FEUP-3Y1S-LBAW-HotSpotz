@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="/search" class="d-flex flex-row flex-wrap gap-2 align-items-end">
                <div class="form-group flex-fill" style="min-width:160px">
                    <label class="form-label">Where to</label>
                    <input type="text" name="where" class="form-control" placeholder="City, address or landmark">
                </div>

                <div class="form-group" style="min-width:150px">
                    <label class="form-label">Check-In</label>
                    <input type="date" name="checkin" id="checkin" class="form-control">
                </div>

                <div class="form-group" style="min-width:150px">
                    <label class="form-label">Check-Out</label>
                    <input type="date" name="checkout" id="checkout" class="form-control">
                </div>

                <div class="form-group" style="min-width:160px">
                    <label class="form-label">Price</label>
                    <select name="price" class="form-select">
                        <option value="">Any</option>
                        <option value="0">Up to $50</option>
                        <option value="50">$50 - $100</option>
                        <option value="100">$100 - $200</option>
                        <option value="200">$200 - $500</option>
                        <option value="500">$500+</option>
                    </select>
                </div>

                <div class="form-group" style="min-width:120px">
                    <label class="form-label">Guests</label>
                    <input type="number" name="guests" class="form-control" min="1" value="1">
                </div>

                <div class="form-group ms-auto">
                    <button type="submit" class="btn btn-primary btn-lg">Search</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4">
        @foreach($listings as $listing)
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                    @php $carouselId = 'carousel-'.$listing->id; @endphp
                    @if(!empty($listing->photos) && count($listing->photos) > 0)
                    <div id="{{ $carouselId }}" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            @foreach($listing->photos as $idx => $photo)
                                <div class="carousel-item @if($idx==0) active @endif">
                                    <img src="{{ $photo }}" class="d-block w-100" style="height:200px; object-fit:cover;" alt="Listing photo">
                                </div>
                            @endforeach
                        </div>
                        @if(count($listing->photos) > 1)
                        <button class="carousel-control-prev" type="button" data-bs-target="#{{ $carouselId }}" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#{{ $carouselId }}" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                        @endif
                    </div>
                    @else
                        <img src="https://via.placeholder.com/400x200?text=No+Image" class="card-img-top" style="height:200px; object-fit:cover;" alt="No image">
                    @endif

                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">{{ $listing->title }}</h5>
                        <p class="card-text mb-1"><i class="fa-solid fa-location-dot"></i> {{ $listing->location_address }}</p>
                        <p class="card-text mb-1">Rating: <strong>{{ number_format($listing->average_rating, 2) }}</strong></p>
                        <p class="card-text mb-3">Price per night: <strong>
                            @if($listing->min_price !== null)
                                ${{ number_format($listing->min_price, 2) }}
                            @else
                                N/A
                            @endif
                        </strong></p>

                        <div class="mt-auto">
                            <a href="/listing/{{ $listing->id }}" class="btn btn-primary w-100">More Info</a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

@push('scripts')
<script>
    (function(){
        function localISODate(d) {
            const y = d.getFullYear();
            const m = String(d.getMonth() + 1).padStart(2, '0');
            const dd = String(d.getDate()).padStart(2, '0');
            return `${y}-${m}-${dd}`;
        }
        function parseLocalDate(s) {
            if (!s) return null;
            const parts = s.split('-');
            if (parts.length !== 3) return null;
            return new Date(parts[0], parseInt(parts[1],10)-1, parts[2]);
        }
        function addDaysToLocal(dateStr, days) {
            const d = parseLocalDate(dateStr);
            if (!d) return null;
            d.setDate(d.getDate() + days);
            return localISODate(d);
        }

        const todayStr = localISODate(new Date());

        const form = document.querySelector('form[action="/search"]');
        const whereInput = form ? form.querySelector('input[name="where"]') : null;
        const priceSelect = form ? form.querySelector('select[name="price"]') : null;
        const guestsInput = form ? form.querySelector('input[name="guests"]') : null;

        const checkin = document.getElementById('checkin');
        const checkout = document.getElementById('checkout');

        if (checkin) checkin.min = todayStr;
        if (checkout) checkout.min = todayStr;

        function onCheckinChange() {
            if (!checkin) return;
            if (checkin.value) {
                const nextDay = addDaysToLocal(checkin.value, 1);
                if (checkout && nextDay) checkout.min = nextDay;
                if (checkout && checkout.value) {
                    const ci = parseLocalDate(checkin.value);
                    const co = parseLocalDate(checkout.value);
                    if (ci && co && ci >= co) checkout.value = '';
                }
                checkin.max = '';
            } else {
                if (checkout) checkout.min = todayStr;
                checkin.max = '';
            }
        }

        function onCheckoutChange() {
            if (!checkout) return;
            if (checkout.value) {
                const prevDay = addDaysToLocal(checkout.value, -1);
                if (checkin && prevDay) checkin.max = prevDay;
                if (checkin && checkin.value) {
                    const ci = parseLocalDate(checkin.value);
                    const co = parseLocalDate(checkout.value);
                    if (ci && co && ci >= co) checkin.value = '';
                }
            } else {
                if (checkin) checkin.max = '';
            }
        }

        if (checkin) checkin.addEventListener('change', onCheckinChange);
        if (checkout) checkout.addEventListener('change', onCheckoutChange);

        if (form) {
            form.addEventListener('submit', function(e){
                e.preventDefault();

                const whereVal = whereInput ? whereInput.value.trim() : '';

                const ci = parseLocalDate(checkin ? checkin.value : '');
                const co = parseLocalDate(checkout ? checkout.value : '');
                const today = parseLocalDate(todayStr);

                if (ci && ci < today) { alert('Check-in cannot be before today.'); return; }
                if (co && co < today) { alert('Check-out cannot be before today.'); return; }
                if (ci && co && ci >= co) { alert('Check-in must be before check-out.'); return; }

                const params = new URLSearchParams();
                if (whereVal) params.set('where', whereVal);
                if (checkin && checkin.value) params.set('checkin', checkin.value);
                if (checkout && checkout.value) params.set('checkout', checkout.value);
                if (priceSelect && priceSelect.value) params.set('price', priceSelect.value);
                if (guestsInput && guestsInput.value) params.set('guests', guestsInput.value);

                const target = form.action + (params.toString() ? ('?' + params.toString()) : '');
                window.location.href = target;
            });
        }
    })();
</script>
@endpush

@endsection