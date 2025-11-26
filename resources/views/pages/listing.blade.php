@extends('layouts.app')

@section('content')
<div class="container py-4">

    <!-- Main slideshow -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body p-0">
            @php $mainId = 'listingCarousel'; @endphp
            <div id="{{ $mainId }}" class="carousel slide" data-bs-ride="false">
                <div class="carousel-inner" style="height:520px;">
                    @if(!empty($photos) && count($photos) > 0)
                        @foreach($photos as $idx => $photo)
                            <div class="carousel-item @if($idx==0) active @endif">
                                <img src="{{ $photo }}" class="d-block w-100" style="height:520px; object-fit:cover;" alt="Listing image">
                            </div>
                        @endforeach
                    @else
                        <div class="carousel-item active">
                            <img src="https://via.placeholder.com/1200x520?text=No+Image" class="d-block w-100" style="height:520px; object-fit:cover;" alt="No image">
                        </div>
                    @endif
                </div>
            </div>

            <!-- Thumbnail navigation (scrollable) -->
            <div class="border-top p-2" style="background:#fff;">
                <div class="d-flex gap-2 overflow-auto" id="thumbnailStrip" style="-webkit-overflow-scrolling:touch;">
                    @foreach($photos as $idx => $photo)
                        <div class="thumb-item" style="flex:0 0 auto; width:100px;">
                            <img src="{{ $photo }}" data-index="{{ $idx }}" class="img-thumbnail thumb-img" style="width:100px; height:60px; object-fit:cover; cursor:pointer;" alt="thumb">
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Details and Reservation -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-8">
            <div class="card p-3 h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h2 class="mb-1">{{ $listing->title }}</h2>
                        <p class="mb-1 text-muted">{{ $listing->location_address }}</p>
                    </div>
                    <div class="text-end">
                        <h4 class="text-wine">@if($min_price !== null) ${{ number_format($min_price,2) }} @else N/A @endif <small class="text-muted">/ night</small></h4>
                    </div>
                </div>

                <hr>

                <div>
                    <h5>Description</h5>
                    <p>{{ $listing->description }}</p>

                    <div class="row">
                        <div class="col-6">
                            <p><strong>Max occupancy:</strong> {{ $listing->max_occupancy }}</p>
                        </div>
                        <div class="col-6">
                            <p><strong>Minimum stay:</strong> {{ $listing->minimum_stay }} nights</p>
                        </div>
                    </div>

                    <p><strong>Cancellation policy:</strong> {{ $listing->cancellation_policy }}</p>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card p-3 shadow-sm">
                <h5 class="mb-3">Reserve</h5>
                <form method="POST" action="#" id="reserveForm">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label">Check-In</label>
                        <input type="date" id="res_checkin" name="checkin" class="form-control">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Check-Out</label>
                        <input type="date" id="res_checkout" name="checkout" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Guests</label>
                        <input type="number" name="guests" class="form-control" min="1" value="1">
                    </div>

                    <div>
                        <button type="button" id="reserveBtn" class="btn btn-primary w-100">Reserve Now</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reviews -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Reviews</h5>

            @if($reviews->isEmpty())
                <p class="text-muted">No reviews yet.</p>
            @else
                @foreach($reviews as $review)
                    <div class="mb-3 border-bottom pb-2">
                        <div class="d-flex justify-content-between">
                            <div>
                                <strong>Rating: {{ $review->rating }}/5</strong>
                            </div>
                            <div class="text-muted">{{ \Illuminate\Support\Carbon::parse($review->created_at)->toDateString() }}</div>
                        </div>
                        <p class="mb-1">{{ $review->comment }}</p>
                    </div>
                @endforeach

                <!-- Pagination -->
                <nav>
                    <ul class="pagination">
                        @for($p = 1; $p <= $totalPages; $p++)
                            <li class="page-item @if($p == $currentPage) active @endif">
                                <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $p]) }}">{{ $p }}</a>
                            </li>
                        @endfor
                    </ul>
                </nav>
            @endif
        </div>
    </div>

</div>

@push('scripts')
<script>
    (function(){
        // Thumbnail click -> change main carousel
        const carouselEl = document.getElementById('listingCarousel');
        const carousel = bootstrap.Carousel.getOrCreateInstance(carouselEl);
        const thumbs = document.querySelectorAll('.thumb-img');
        thumbs.forEach(function(t){
            t.addEventListener('click', function(){
                const idx = parseInt(this.getAttribute('data-index'));
                if (!isNaN(idx)) {
                    carousel.to(idx);
                }
                // ensure thumbnail is visible/centered
                this.scrollIntoView({behavior: 'smooth', inline: 'center'});
            });
        });

        // Reservation date logic (local-date-aware)
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

        const tomorrow = (function(){ const d = new Date(); d.setDate(d.getDate()+1); return localISODate(d); })();
        const checkin = document.getElementById('res_checkin');
        const checkout = document.getElementById('res_checkout');
        if (checkin) checkin.min = tomorrow;
        if (checkout) checkout.min = tomorrow;

        function ensureOrder() {
            if (!checkin || !checkout) return true;
            const ci = parseLocalDate(checkin.value);
            const co = parseLocalDate(checkout.value);
            if (ci && co) {
                if (ci >= co) {
                    alert('Check-out must be after check-in.');
                    return false;
                }
            }
            return true;
        }

        if (checkin) checkin.addEventListener('change', function(){
            if (checkout) checkout.min = addDaysToLocal(this.value, 1) || tomorrow;
        });

        document.getElementById('reserveBtn').addEventListener('click', function(){
            if (!ensureOrder()) return;
            const ci = parseLocalDate(checkin ? checkin.value : '');
            const co = parseLocalDate(checkout ? checkout.value : '');
            const minStay = {{ (int)$listing->minimum_stay }};
            if (!ci || !co) { alert('Please select both check-in and check-out dates.'); return; }
            const diffDays = Math.round((co - ci) / (1000*60*60*24));
            if (diffDays < minStay) { alert('This listing requires a minimum stay of ' + minStay + ' nights.'); return; }
            alert('Reserve action not implemented yet.');
        });
    })();
</script>
@endpush

@endsection
