@extends('layouts.app')

@section('content')
    <div class="container py-4">
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

    <!-- Breadcrumbs -->
        @include('partials.page_header', [
            'breadcrumbs' => [
                ['title' => 'Search Listings', 'url' => route('search')],
                ['title' => $listing->title, 'url' => null]
            ]
        ])
    <div class="card mb-4 shadow-sm">

        
        <div class="card-body p-0">
            @php $mainId = 'listingCarousel'; @endphp
            <div id="{{ $mainId }}" class="carousel slide" data-bs-ride="carousel">
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
                @if(!empty($photos) && count($photos) > 1)
                <button class="carousel-control-prev" type="button" data-bs-target="#{{ $mainId }}" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#{{ $mainId }}" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
                @endif
            </div>

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

    <div class="row g-4 mb-4">
        <div class="col-12 col-md-8">
            <div class="card p-3 h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h2 class="mb-1">{{ $listing->title }}</h2>
                        <p class="mb-1 text-muted">{{ $listing->location_address }}</p>
                        
                        {{-- Show host info --}}
                        @if($host)
                            <p class="mb-0">
                                <i class="fa-solid fa-user me-1"></i>
                                @if(Auth::check() && Auth::id() === $listing->host_id)
                                    Hosted by <span class="fw-semibold" style="color: #722F37;">You</span>
                                @else
                                    Hosted by 
                                    <a href="{{ route('profile.public', $host->id) }}" class="text-decoration-none fw-semibold" style="color: #722F37;">
                                        {{ explode(' ', $host->full_name)[0] }}
                                    </a>
                                @endif
                            </p>
                        @endif
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
                    <div class="mt-2">
                        <h6>Tags</h6>
                        <div class="d-flex gap-2 flex-wrap mb-2">
                            @foreach($listing_tags ?? [] as $t)
                                <span class="badge bg-secondary">{{ $t['name'] }}</span>
                            @endforeach
                        </div>

                        <h6>Amenities</h6>
                        <div class="d-flex gap-2 flex-wrap mb-3">
                            @php
                                $amenList = [];
                                if (!empty($listing->amenities)) {
                                    $amenList = array_filter(array_map('trim', explode(',', $listing->amenities)));
                                }
                            @endphp
                            @forelse($amenList as $am)
                                <span class="badge bg-warning text-dark">{{ $am }}</span>
                            @empty
                                <span class="text-muted small">No amenities listed.</span>
                            @endforelse
                        </div>

                    <div class="mt-3">
                        <h5>Availability</h5>
                        @if(isset($availPaginator) && $availPaginator->total() > 0)
                            <ul class="list-group" aria-live="polite">
                                @foreach($availPaginator as $av)
                                            @include('partials.availability_item', ['availability' => $av, 'showActions' => false])
                                        @endforeach
                            </ul>

                            @if($availPaginator->lastPage() > 1)
                                <div class="mt-2 d-flex justify-content-end">
                                    <nav aria-label="Availability pages">
                                        <ul class="pagination mb-0">
                                            @if ($availPaginator->onFirstPage())
                                                <li class="page-item disabled"><span class="page-link"><i class="fas fa-chevron-left"></i></span></li>
                                            @else
                                                <li class="page-item"><a class="page-link" href="{{ $availPaginator->previousPageUrl() }}" rel="prev"><i class="fas fa-chevron-left"></i></a></li>
                                            @endif

                                            @foreach ($availPaginator->getUrlRange(1, $availPaginator->lastPage()) as $p => $url)
                                                @if ($p == $availPaginator->currentPage())
                                                    <li class="page-item active"><span class="page-link">{{ $p }}</span></li>
                                                @else
                                                    <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $p }}</a></li>
                                                @endif
                                            @endforeach

                                            @if ($availPaginator->hasMorePages())
                                                <li class="page-item"><a class="page-link" href="{{ $availPaginator->nextPageUrl() }}" rel="next"><i class="fas fa-chevron-right"></i></a></li>
                                            @else
                                                <li class="page-item disabled"><span class="page-link"><i class="fas fa-chevron-right"></i></span></li>
                                            @endif
                                        </ul>
                                    </nav>
                                </div>
                            @endif
                        @else
                            <p class="text-muted">No availabilities listed.</p>
                        @endif
                    </div>
                </div>
                </div>
            </div>
        </div>
        {{-- End of col-12 col-md-8 --}}

        <div class="col-12 col-md-4">
            @php
                $isPremium = Auth::check() && Auth::user()->isPremium();
                $discount = $isPremium ? Auth::user()->getDiscountPercentage() : 0;
                $discountedMinPrice = $min_price !== null ? $min_price * (1 - $discount / 100) : null;
            @endphp
            <div class="card p-3 shadow-sm mb-3" id="priceSimulatorCard">
                <h5 class="mb-2">Price Simulator</h5>
                @if($isPremium && $discount > 0)
                    <div class="alert alert-success py-2 mb-3">
                        <i class="fa-solid fa-crown me-1"></i>
                        <small class="fw-bold">Premium Member: {{ $discount }}% discount applied!</small>
                    </div>
                @endif
                <div class="mb-2">
                    <label class="form-label small">Check-in</label>
                    <input type="date" id="sim_checkin" class="form-control" />
                </div>
                <div class="mb-2">
                    <label class="form-label small">Check-out</label>
                    <input type="date" id="sim_checkout" class="form-control" />
                </div>
                <div class="mb-2">
                    <label class="form-label small">Guests</label>
                    <input type="number" id="sim_guests" class="form-control" min="1" max="{{ $listing->max_occupancy }}" value="1" />
                    <div class="form-text small">Max: {{ $listing->max_occupancy }}</div>
                </div>
                <hr />
                <div class="mb-2">
                    <div class="d-flex justify-content-between">
                        <div class="small text-muted">Price per night</div>
                        <div class="fw-bold" id="sim_price_per_night">
                            @if($min_price !== null)
                                @if($isPremium && $discount > 0)
                                    <small class="text-decoration-line-through text-muted">${{ number_format($min_price, 2) }}</small>
                                    <span class="text-success">${{ number_format($discountedMinPrice, 2) }}</span>
                                @else
                                    ${{ number_format($min_price, 2) }}
                                @endif
                            @else
                                N/A
                            @endif
                        </div>
                    </div>
                </div>
                <div id="sim_warning" class="text-danger small mb-2" style="display:none"></div>
                <div class="mb-2">
                    <div class="d-flex justify-content-between">
                        <div class="small text-muted">Nights</div>
                        <div class="fw-bold" id="sim_nights">0</div>
                    </div>
                </div>
                <div class="mb-2">
                    <div class="d-flex justify-content-between">
                        <div class="small text-muted">Total</div>
                        <div class="fw-bold text-wine" id="sim_total">$0.00</div>
                    </div>
                </div>
                <div class="d-grid mt-2">
                    <button type="button" class="btn btn-outline-danger" id="sim_calc">Calculate</button>
                </div>
            </div>

            <div class="card p-3 shadow-sm text-center">
                <h5 class="mb-3">
                    @auth
                        @if(Auth::id() === $listing->host_id)
                            Options
                        @else
                            Reserve
                        @endif
                    @endauth
                </h5>
                <div class="d-grid">
                    @auth
                        @if(Auth::id() === $listing->host_id)
                            <div class="d-flex justify-content-center gap-2">
                                <a href="{{ route('listing.edit', ['id' => $listing->id]) }}" class="btn btn-secondary btn-lg">Edit Listing</a>
                                <form method="POST" action="{{ route('listing.destroy', ['id' => $listing->id]) }}" onsubmit="return confirm('Are you sure you want to delete this listing?');">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-lg">Delete Listing</button>
                                </form>
                            </div>
                        @else
                            <a href="/booking/{{ $listing->id }}" class="btn btn-primary btn-lg">Reserve Now</a>
                        @endif
                    @else
                        <a href="/booking/{{ $listing->id }}" class="btn btn-primary btn-lg">Reserve Now</a>
                    @endauth
                </div>
            </div>
        </div>
        {{-- End of col-12 col-md-4 --}}
    </div>
    {{-- End of row g-4 mb-4 --}}

    {{-- Reviews Section - Full Width --}}
    <div class="row">
        <div class="col-12">
            <div class="card mb-4 shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-4">
                        <i class="fas fa-star text-warning me-2" style="font-size: 1.5rem;"></i>
                        <h5 class="card-title mb-0 fw-bold">Guest Reviews</h5>
                        @if($reviews->total() > 0)
                            <span class="badge bg-primary ms-auto">{{ $reviews->total() }} {{ Str::plural('Review', $reviews->total()) }}</span>
                        @endif
                    </div>

                    @if($reviews->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-comment-slash text-muted mb-3" style="font-size: 3rem; opacity: 0.3;"></i>
                            <p class="text-muted mb-0">No reviews yet. Be the first to share your experience!</p>
                        </div>
                    @else
                        <div class="reviews-list">
                            @foreach($reviews as $review)
                                @include('partials.review_item', ['review' => $review])
                            @endforeach
                        </div>

                        @if($reviews->hasPages())
                        <div class="d-flex justify-content-center mt-4 pt-3 border-top">
                            <nav aria-label="Reviews pagination">
                                <ul class="pagination pagination-sm mb-0">
                                    {{-- Previous Button --}}
                                    @if ($reviews->onFirstPage())
                                        <li class="page-item disabled">
                                            <span class="page-link rounded-start">
                                                <i class="fas fa-chevron-left"></i>
                                            </span>
                                        </li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link rounded-start" href="{{ $reviews->previousPageUrl() }}" rel="prev">
                                                <i class="fas fa-chevron-left"></i>
                                            </a>
                                        </li>
                                    @endif

                                    {{-- Page Numbers with Smart Range --}}
                                    @php
                                        $start = max(1, $reviews->currentPage() - 2);
                                        $end = min($reviews->lastPage(), $reviews->currentPage() + 2);
                                    @endphp

                                    {{-- First Page + Ellipsis --}}
                                    @if($start > 1)
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $reviews->url(1) }}">1</a>
                                        </li>
                                        @if($start > 2)
                                            <li class="page-item disabled">
                                                <span class="page-link">...</span>
                                            </li>
                                        @endif
                                    @endif

                                    {{-- Page Range --}}
                                    @for($p = $start; $p <= $end; $p++)
                                        @if($p == $reviews->currentPage())
                                            <li class="page-item active" aria-current="page">
                                                <span class="page-link">{{ $p }}</span>
                                            </li>
                                        @else
                                            <li class="page-item">
                                                <a class="page-link" href="{{ $reviews->url($p) }}">{{ $p }}</a>
                                            </li>
                                        @endif
                                    @endfor

                                    {{-- Ellipsis + Last Page --}}
                                    @if($end < $reviews->lastPage())
                                        @if($end < $reviews->lastPage() - 1)
                                            <li class="page-item disabled">
                                                <span class="page-link">...</span>
                                            </li>
                                        @endif
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $reviews->url($reviews->lastPage()) }}">{{ $reviews->lastPage() }}</a>
                                        </li>
                                    @endif

                                    {{-- Next Button --}}
                                    @if ($reviews->hasMorePages())
                                        <li class="page-item">
                                            <a class="page-link rounded-end" href="{{ $reviews->nextPageUrl() }}" rel="next">
                                                <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </li>
                                    @else
                                        <li class="page-item disabled">
                                            <span class="page-link rounded-end">
                                                <i class="fas fa-chevron-right"></i>
                                            </span>
                                        </li>
                                    @endif
                                </ul>
                            </nav>
                        </div>

                        {{-- Page Info --}}
                        <div class="text-center mt-2">
                            <small class="text-muted">
                                Showing {{ $reviews->firstItem() }} to {{ $reviews->lastItem() }} of {{ $reviews->total() }} reviews
                            </small>
                        </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function(){
        function initThumbs() {
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
            const carouselEl = document.getElementById('listingCarousel');
            if (!carouselEl) return;
            let carousel = null;
            if (window.bootstrap && bootstrap.Carousel) {
                if (typeof bootstrap.Carousel.getOrCreateInstance === 'function') {
                    carousel = bootstrap.Carousel.getOrCreateInstance(carouselEl);
                } else if (typeof bootstrap.Carousel.getInstance === 'function') {
                    carousel = bootstrap.Carousel.getInstance(carouselEl) || new bootstrap.Carousel(carouselEl);
                } else {
                    try { carousel = new bootstrap.Carousel(carouselEl); } catch(e) { carousel = null; }
                }
            }

            const thumbs = document.querySelectorAll('.thumb-img');
            thumbs.forEach(function(t){
                t.addEventListener('click', function(e){
                    e.preventDefault();
                    e.stopPropagation();
                    const idx = parseInt(this.getAttribute('data-index'));
                    if (isNaN(idx)) return;

                    try {
                        if (window.bootstrap && bootstrap.Carousel) {
                            const getInst = bootstrap.Carousel.getOrCreateInstance || bootstrap.Carousel.getInstance;
                            const inst = getInst ? getInst.call(bootstrap.Carousel, carouselEl) || new bootstrap.Carousel(carouselEl) : new bootstrap.Carousel(carouselEl);
                            if (inst) {
                                if (typeof inst.to === 'function') { inst.to(idx); this.scrollIntoView({behavior: 'smooth', block: 'nearest', inline: 'center'}); return; }
                                if (typeof inst.slide === 'function') { inst.slide(idx); this.scrollIntoView({behavior: 'smooth', block: 'nearest', inline: 'center'}); return; }
                            }
                        }
                    } catch (err) {
                        console.debug('Bootstrap carousel API call failed, falling back', err);
                    }

                    const items = Array.from(carouselEl.querySelectorAll('.carousel-item'));
                    if (!items[idx]) return;
                    items.forEach(it => {
                        it.classList.remove('active', 'carousel-item-start', 'carousel-item-end', 'carousel-item-left', 'carousel-item-right');
                        it.style.display = '';
                    });
                    items[idx].classList.add('active');
                    const inn = carouselEl.querySelector('.carousel-inner');
                    if (inn) { void inn.offsetHeight; }
                    this.scrollIntoView({behavior: 'smooth', block: 'nearest', inline: 'center'});
                });
            });

            const availabilities = {!! json_encode($availabilities ?? []) !!};
            const premiumDiscount = {{ Auth::check() && Auth::user()->isPremium() ? Auth::user()->getDiscountPercentage() : 0 }};
            function todayISO(){ const d=new Date(); return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`; }
            function localISODate(d) { const y=d.getFullYear(); const m=String(d.getMonth()+1).padStart(2,'0'); const dd=String(d.getDate()).padStart(2,'0'); return `${y}-${m}-${dd}`; }
            function parseLocalDate(s){ if(!s) return null; const parts = s.split('-'); if(parts.length!==3) return null; return new Date(parts[0], parseInt(parts[1],10)-1, parts[2]); }
            function addDaysToLocal(dateStr, days){ const d = parseLocalDate(dateStr); if(!d) return null; d.setDate(d.getDate() + days); return localISODate(d); }

            function findPriceForRange(checkin, checkout){
                const hasCheckin = !!checkin;
                const hasCheckout = !!checkout;
                const today = todayISO();

                const rows = availabilities.slice().sort((a,b)=> a.start_date.localeCompare(b.start_date));

                let selected = null;
                if (hasCheckin && hasCheckout) {
                    for (const r of rows) {
                        if (r.start_date <= checkin && r.end_date >= checkout) { selected = r; break; }
                    }
                    if (selected) return { price: parseFloat(selected.price_per_night), matched: true, fallback: false };
                    return { price: null, matched: false, fallback: false };
                } else if (hasCheckin) {
                    for (const r of rows) { if (r.start_date >= checkin) { selected = r; break; } }
                } else if (hasCheckout) {
                    for (let i = rows.length-1; i>=0; i--){ const r = rows[i]; if (r.start_date <= checkout) { selected = r; break; } }
                } else {
                    for (const r of rows) { if (r.start_date >= today) { selected = r; break; } }
                }

                if (selected) return { price: parseFloat(selected.price_per_night), matched: true, fallback: false };
                let min = null;
                for (const r of rows){ if (min === null || parseFloat(r.price_per_night) < min) min = parseFloat(r.price_per_night); }
                return { price: min, matched: true, fallback: true };
            }

            function daysBetween(a,b){ if(!a||!b) return 0; const da=new Date(a); const db=new Date(b); const diff=(db - da)/(1000*60*60*24); return Math.max(0, Math.floor(diff)); }

            function formatMoney(v){ if (v === null || v === undefined) return 'N/A'; return '$' + Number(v).toFixed(2); }

            function updateSimulator(){
                const checkin = document.getElementById('sim_checkin').value;
                const checkout = document.getElementById('sim_checkout').value;
                let guests = parseInt(document.getElementById('sim_guests').value) || 1;
                const maxGuests = {{ (int)$listing->max_occupancy }};
                if (guests < 1) guests = 1;
                if (guests > maxGuests) guests = maxGuests;

                const warningEl = document.getElementById('sim_warning');
                const today = todayISO();
                if (checkin) {
                    if (checkin < today) {
                        warningEl.style.display = 'block';
                        warningEl.textContent = 'Check-in cannot be before today.';
                        document.getElementById('sim_price_per_night').textContent = 'N/A';
                        document.getElementById('sim_nights').textContent = 0;
                        document.getElementById('sim_total').textContent = 'N/A';
                        return;
                    }
                    if (checkout && checkin > checkout) {
                        warningEl.style.display = 'block';
                        warningEl.textContent = 'Check-in must be the same or before the check-out date.';
                        document.getElementById('sim_price_per_night').textContent = 'N/A';
                        document.getElementById('sim_nights').textContent = 0;
                        document.getElementById('sim_total').textContent = 'N/A';
                        return;
                    }
                }
                if (checkout) {
                    if (checkout < today) {
                        warningEl.style.display = 'block';
                        warningEl.textContent = 'Check-out cannot be before today.';
                        document.getElementById('sim_price_per_night').textContent = 'N/A';
                        document.getElementById('sim_nights').textContent = 0;
                        document.getElementById('sim_total').textContent = 'N/A';
                        return;
                    }
                    if (checkin && checkout < checkin) {
                        warningEl.style.display = 'block';
                        warningEl.textContent = 'Check-out must be the same or after the check-in date.';
                        document.getElementById('sim_price_per_night').textContent = 'N/A';
                        document.getElementById('sim_nights').textContent = 0;
                        document.getElementById('sim_total').textContent = 'N/A';
                        return;
                    }
                }

                if (warningEl) { warningEl.style.display = 'none'; warningEl.textContent = ''; }

                const result = findPriceForRange(checkin, checkout);
                const nights = (checkin && checkout) ? daysBetween(checkin, checkout) : 0;

                if (result && result.matched === false) {
                    warningEl.style.display = 'block';
                    warningEl.textContent = 'Selected dates do not fall within a single availability period. Please choose dates inside the same availability.';
                    document.getElementById('sim_price_per_night').textContent = 'N/A';
                    document.getElementById('sim_nights').textContent = nights;
                    document.getElementById('sim_total').textContent = 'N/A';
                    return;
                }

                const perNight = result ? result.price : null;
                let finalPerNight = perNight;
                
                if (premiumDiscount > 0 && perNight !== null) {
                    finalPerNight = perNight * (1 - premiumDiscount / 100);
                }

                const total = (finalPerNight !== null && nights > 0) ? (finalPerNight * nights * guests) : null;

                if (premiumDiscount > 0 && perNight !== null) {
                    document.getElementById('sim_price_per_night').innerHTML = `
                        <small class="text-decoration-line-through text-muted">${formatMoney(perNight)}</small>
                        <span class="text-success">${formatMoney(finalPerNight)}</span>
                    `;
                } else {
                    document.getElementById('sim_price_per_night').textContent = perNight ? formatMoney(perNight) : 'N/A';
                }
                
                document.getElementById('sim_nights').textContent = nights;
                document.getElementById('sim_total').textContent = total !== null ? formatMoney(total) : 'N/A';
            

                const todayStr = localISODate(new Date());
                const checkinEl = document.getElementById('sim_checkin');
                const checkoutEl = document.getElementById('sim_checkout');

                if (checkinEl) checkinEl.min = todayStr;
                if (checkoutEl) checkoutEl.min = todayStr;

                function onCheckinChange() {
                    if (!checkinEl) return;
                    if (checkinEl.value) {
                        const nextDay = addDaysToLocal(checkinEl.value, 1);
                        if (checkoutEl && nextDay) checkoutEl.min = nextDay;
                        if (checkoutEl && checkoutEl.value) {
                            const ci = parseLocalDate(checkinEl.value);
                            const co = parseLocalDate(checkoutEl.value);
                            if (ci && co && ci >= co) checkoutEl.value = '';
                        }
                        checkinEl.max = '';
                    } else {
                        if (checkoutEl) checkoutEl.min = todayStr;
                        checkinEl.max = '';
                    }
                }

                function onCheckoutChange() {
                    if (!checkoutEl) return;
                    if (checkoutEl.value) {
                        const prevDay = addDaysToLocal(checkoutEl.value, -1);
                        if (checkinEl && prevDay) checkinEl.max = prevDay;
                        if (checkinEl && checkinEl.value) {
                            const ci = parseLocalDate(checkinEl.value);
                            const co = parseLocalDate(checkoutEl.value);
                            if (ci && co && ci >= co) checkinEl.value = '';
                        }
                    } else {
                        if (checkinEl) checkinEl.max = '';
                    }
                }

                if (checkinEl) checkinEl.addEventListener('change', onCheckinChange);
                if (checkoutEl) checkoutEl.addEventListener('change', onCheckoutChange);
            
            }


            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function(){
                    document.getElementById('sim_calc').addEventListener('click', updateSimulator);
                    ['sim_checkin','sim_checkout','sim_guests'].forEach(id => {
                        const el = document.getElementById(id);
                        if (el) el.addEventListener('change', updateSimulator);
                    });
                    updateSimulator();
                });
            } else {
                document.getElementById('sim_calc').addEventListener('click', updateSimulator);
                ['sim_checkin','sim_checkout','sim_guests'].forEach(id => { const el=document.getElementById(id); if (el) el.addEventListener('change', updateSimulator); });
                updateSimulator();
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initThumbs);
        } else {
            initThumbs();
        }
    })();
</script>
@endpush

@endsection
