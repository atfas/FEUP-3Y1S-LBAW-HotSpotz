@php
    $photos = $listing->photos ?? [];
    $carouselId = 'carousel-search-'.$listing->id;
@endphp

<div class="col-12 col-md-6 col-lg-4">
    <div class="card h-100 shadow-sm">
        @if(!empty($photos) && count($photos) > 0)
        <div id="{{ $carouselId }}" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                @foreach($photos as $idx => $photo)
                    <div class="carousel-item @if($idx==0) active @endif">
                        <img src="{{ $photo }}" class="d-block w-100" style="height:200px; object-fit:cover;" alt="Listing photo">
                    </div>
                @endforeach
            </div>
            @if(count($photos) > 1)
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
            <p class="card-text mb-1">Rating: <strong>{{ number_format($listing->average_rating ?? 0, 2) }}</strong></p>
            <p class="card-text mb-3">Price per night: <strong>
                @if(isset($listing->min_price) && $listing->min_price !== null)
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
