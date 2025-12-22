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
                    <div class="flex-grow-1">
                        <h2 class="mb-1">{{ $listing->title }}</h2>
                        <p class="mb-1 text-muted">{{ $listing->location_address }}</p>
                        
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

                    
                    {{-- Favorite Button --}}
                    @auth
                        @if(Auth::id() !== $listing->host_id)
                            <form method="POST" action="{{ route('favorites.toggle', $listing->id) }}">
                                @csrf
                                <button 
                                    type="submit" 
                                    class="btn btn-link p-0"
                                    style="font-size: 1.5rem; color: #722F37;"
                                    title="{{ Auth::user()->hasFavorited($listing->id) ? 'Remove from favorites' : 'Add to favorites' }}">
                                    <i class="fa-{{ Auth::user()->hasFavorited($listing->id) ? 'solid' : 'regular' }} fa-star"></i>
                                </button>
                            </form>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="btn btn-link p-0" style="font-size: 1.5rem; color: #722F37;">
                            <i class="fa-regular fa-star"></i>
                        </a>
                    @endauth
                    
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

        <div class="col-12 col-md-4">
            @php
                $isPremium = Auth::check() && Auth::user()->isPremium();
                $discount = $isPremium ? Auth::user()->getDiscountPercentage() : 0;
                $discountedMinPrice = $min_price !== null ? $min_price * (1 - $discount / 100) : null;
            @endphp
            <div class="card p-3 shadow-sm mb-3" id="priceSimulatorCard">
                <h5 class="mb-2 d-flex justify-content-between">
                    <span>Price Simulator</span>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="sim_clear" style="display:none;">Clear</button>
                </h5>
                @if($isPremium && $discount > 0)
                    <div class="alert alert-success py-2 mb-3">
                        <i class="fa-solid fa-crown me-1"></i>
                        <small class="fw-bold">Premium Member: {{ $discount }}% discount applied!</small>
                    </div>
                @endif
                <div class="mb-2">
                    <label class="form-label small">Select dates</label>
                    <div id="sim_calendar_container" style="max-width:100%;"></div>
                    <input type="hidden" id="sim_checkin" />
                    <input type="hidden" id="sim_checkout" />
                    <div class="form-text small">Only dates inside an availability are selectable.</div>
                    <div id="calendar_error_message" class="text-danger small mt-1" style="min-height: 1.2em;"></div>
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
                        <div class="small text-muted">Days</div>
                        <div class="fw-bold" id="sim_days">0</div>
                    </div>
                </div>
                <div class="mb-2">
                    <div class="d-flex justify-content-between">
                        <div class="small text-muted">Total</div>
                        <div class="fw-bold text-wine" id="sim_total">$0.00</div>
                    </div>
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
                                <a href="{{ route('listing.reservations', ['id' => $listing->id]) }}" class="btn btn-primary btn-lg">View Reservations</a>
                                <a href="{{ route('listing.edit', ['id' => $listing->id]) }}" class="btn btn-secondary btn-lg">Edit Listing</a>
                                <form method="POST" action="{{ route('listing.destroy', ['id' => $listing->id]) }}" onsubmit="return confirm('Are you sure you want to delete this listing?');">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-lg">Delete Listing</button>
                                </form>
                            </div>
                        @else
                            <div class="d-flex flex-column gap-2">
                                <a href="/booking/{{ $listing->id }}" class="btn btn-primary btn-lg">Reserve Now</a>
                                <form action="{{ route('chat.create') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="listing_id" value="{{ $listing->id }}">
                                    <button type="submit" class="btn btn-outline-primary btn-lg w-100">
                                        <i class="fa-solid fa-message"></i> Start Chat
                                    </button>
                                </form>
                            </div>
                        @endif
                    @else
                        <div class="d-flex flex-column gap-2">
                            <a href="/booking/{{ $listing->id }}" class="btn btn-primary btn-lg">Reserve Now</a>
                            <a href="{{ route('login') }}" class="btn btn-outline-primary btn-lg w-100">
                                <i class="fa-solid fa-message"></i> Start Chat
                            </a>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </div>
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

                                    @php
                                        $start = max(1, $reviews->currentPage() - 2);
                                        $end = min($reviews->lastPage(), $reviews->currentPage() + 2);
                                    @endphp

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

@push('styles')
<link href="{{ asset('css/reservations_calendar.css') }}" rel="stylesheet">
@endpush

@push('scripts')
<script src="{{ asset('vendor/fullcalendar/core.global.min.js') }}" defer></script>
<script src="{{ asset('vendor/fullcalendar/daygrid.global.min.js') }}" defer></script>
<script src="{{ asset('vendor/fullcalendar/interaction.global.min.js') }}" defer></script>
<script src="{{ asset('js/booking_calendar/booking_calendar.js') }}" defer></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        window.availabilities = {!! json_encode($availabilities ?? []) !!};
        window.bookings = {!! json_encode( $bookings ?? []) !!};
        const premiumDiscount = {{ Auth::check() && Auth::user()->isPremium() ? Auth::user()->getDiscountPercentage() : 0 }};
        function parseLocalDate(s){ if(!s) return null; const parts = s.split('-'); if(parts.length!==3) return null; return new Date(parts[0], parseInt(parts[1],10)-1, parts[2]); }
        function countDays(a,b){ if(!a||!b) return 0; const da=parseLocalDate(a); const db=parseLocalDate(b); if (!da || !db) return 0; const diff=(db - da)/(1000*60*60*24); return Math.max(0, Math.round(diff)) + 1; }
        function formatMoney(v){ if (v === null || v === undefined) return 'N/A'; return '$' + Number(v).toFixed(2); }

        function findPriceForRange(checkin, checkout){
            const hasCheckin = !!checkin;
            const hasCheckout = !!checkout;
            function todayISO(){ const d=new Date(); return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`; }
            const today = todayISO();

            const rows = (window.availabilities || []).slice().sort((a,b)=> a.start_date.localeCompare(b.start_date));

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

        function updateSimulator(){
            const checkin = document.getElementById('sim_checkin').value;
            const checkout = document.getElementById('sim_checkout').value;

            document.getElementById('sim_clear').style.display = (checkin || checkout) ? 'inline-block' : 'none';

            const days = (checkin && checkout) ? countDays(checkin, checkout) : 0;
            const nights = days > 0 ? days - 1 : 0;
            
            const result = findPriceForRange(checkin, checkout);
            const warningEl = document.getElementById('sim_warning');
            if (warningEl) { warningEl.style.display = 'none'; warningEl.textContent = ''; }

            if (result && result.matched === false) {
                if(warningEl) {
                    warningEl.style.display = 'block';
                    warningEl.textContent = 'Selected dates must be within a single availability period.';
                }
                document.getElementById('sim_price_per_night').textContent = 'N/A';
                document.getElementById('sim_days').textContent = days;
                document.getElementById('sim_total').textContent = 'N/A';
                return;
            }

            const perNight = result ? result.price : null;
            let finalPerNight = perNight;

            if (premiumDiscount > 0 && perNight !== null) {
                finalPerNight = perNight * (1 - premiumDiscount / 100);
            }

            const total = (finalPerNight !== null && nights > 0) ? (finalPerNight * nights) : 0;

            if (premiumDiscount > 0 && perNight !== null) {
                document.getElementById('sim_price_per_night').innerHTML = `
                    <small class="text-decoration-line-through text-muted">${formatMoney(perNight)}</small>
                    <span class="text-success">${formatMoney(finalPerNight)}</span>
                `;
            } else {
                document.getElementById('sim_price_per_night').textContent = perNight ? formatMoney(perNight) : 'N/A';
            }

            document.getElementById('sim_days').textContent = days;
            document.getElementById('sim_total').textContent = total > 0 ? formatMoney(total) : '$0.00';
        }

        window.updateSimulator = updateSimulator;

        function clearSimSelection() {
            if (window.clearCalendarSelection) {
                window.clearCalendarSelection();
            }
            updateSimulator();
        }
        
        const carouselEl = document.getElementById('listingCarousel');
        if (carouselEl && window.bootstrap && bootstrap.Carousel) {
            const carousel = bootstrap.Carousel.getOrCreateInstance(carouselEl);
            document.querySelectorAll('.thumb-img').forEach(thumb => {
                thumb.addEventListener('click', function(e) {
                    const index = parseInt(this.getAttribute('data-index'), 10);
                    if (!isNaN(index)) {
                        carousel.to(index);
                    }
                });
            });
        }

        const clearBtn = document.getElementById('sim_clear');
        if (clearBtn) {
            clearBtn.addEventListener('click', clearSimSelection);
        }

        updateSimulator();

        window.toggleResponseForm = function(reviewId) {
            const form = document.getElementById(`response-form-${reviewId}`);
            if (form) {
                form.style.display = form.style.display === 'none' ? 'block' : 'none';
            }
        }

        window.toggleEditResponse = function(reviewId) {
            const text = document.getElementById(`response-text-${reviewId}`);
            const form = document.getElementById(`edit-response-form-${reviewId}`);
            
            if (text && form) {
                const isFormVisible = form.style.display !== 'none';
                text.style.display = isFormVisible ? 'block' : 'none';
                form.style.display = isFormVisible ? 'none' : 'block';
            }
        }
    });
</script>

@endpush
@endsection
