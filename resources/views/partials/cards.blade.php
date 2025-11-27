@php
    $isPaginator = (isset($listings) && $listings instanceof \Illuminate\Pagination\LengthAwarePaginator);
    $items = $listings;
@endphp

<div class="row g-4" id="search-results-grid">
    @forelse($items as $listing)
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm">
                @php $carouselId = 'carousel-search-'.$listing->id; @endphp
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
    @empty
        <div class="col-12">
            <div class="alert alert-info">No listings match your search.</div>
        </div>
    @endforelse
    @if($isPaginator && $listings->lastPage() > 1)
        <div class="col-12">
            <nav>
                <ul class="pagination">
                    @for ($p = 1; $p <= $listings->lastPage(); $p++)
                        <li class="page-item @if($p == $listings->currentPage()) active @endif">
                            <a class="page-link" href="{{ $listings->url($p) }}">{{ $p }}</a>
                        </li>
                    @endfor
                </ul>
            </nav>
        </div>
    @endif
</div>