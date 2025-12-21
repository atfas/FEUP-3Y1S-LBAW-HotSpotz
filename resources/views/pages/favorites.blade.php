@extends('layouts.app')

@section('content')
<div class="container py-4">
    @include('partials.page_header', [
        'breadcrumbs' => [
            ['title' => 'My Favorites', 'url' => null]
        ]
    ])

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="d-flex align-items-center mb-4">
                <i class="fas fa-star text-warning me-2" style="font-size: 1.5rem;"></i>
                <h4 class="mb-0">My Favorite Listings</h4>
                @if($favorites->total() > 0)
                    <span class="badge bg-primary ms-auto">{{ $favorites->total() }} {{ Str::plural('Favorite', $favorites->total()) }}</span>
                @endif
            </div>

            @if($favorites->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-heart text-muted mb-3" style="font-size: 4rem; opacity: 0.3;"></i>
                    <h5 class="text-muted">No favorites yet</h5>
                    <p class="text-muted mb-4">Start exploring and save your favorite listings!</p>
                    <a href="{{ route('search') }}" class="btn btn-primary">
                        <i class="fas fa-search me-2"></i>Browse Listings
                    </a>
                </div>
            @else
                <div class="row g-4">
                    @foreach($favorites as $favorite)
                        @php
                            $listing = $favorite->listing;
                            $minPrice = $listing->availabilities->min('price_per_night');
                            $photos = [];
                            $photo = $listing->photos->first();
                            $imageUrl = $photo
                                ? $photo->url
                                : 'https://via.placeholder.com/400x300?text=No+Image';
                        @endphp
                        
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="card h-100 shadow-sm">
                                <div class="position-relative">
                                    <img 
                                        src="{{ $imageUrl }}"
                                        class="card-img-top"
                                        style="height: 200px; object-fit: cover;"
                                        alt="{{ $listing->title }}"
                                    >
                                    <form
                                        method="POST"
                                        action="{{ route('favorites.toggle', $listing->id) }}"
                                        class="position-absolute top-0 end-0 m-2"
                                    >
                                        @csrf
                                        <button
                                            type="submit"
                                            class="btn btn-link p-1"
                                            style="background: rgba(255,255,255,0.9);
                                                border-radius: 50%;
                                                width: 40px;
                                                height: 40px;
                                                font-size: 1.2rem;
                                                color: #722F37;"
                                            title="Remove from favorites"
                                        >
                                            <i class="fa-solid fa-star"></i>
                                        </button>
                                    </form>
                                </div>
                                
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title">{{ $listing->title }}</h5>
                                    <p class="text-muted small mb-2">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        {{ $listing->location_address }}
                                    </p>
                                    
                                    @if($listing->host)
                                        <p class="text-muted small mb-2">
                                            <i class="fas fa-user me-1"></i>
                                            Hosted by {{ explode(' ', $listing->host->full_name)[0] }}
                                        </p>
                                    @endif
                                    
                                    <p class="card-text text-truncate mb-3">{{ $listing->description }}</p>
                                    
                                    <div class="mt-auto">
                                        @if($minPrice)
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <span class="text-muted small">From</span>
                                                <span class="h5 mb-0" style="color: #722F37;">
                                                    ${{ number_format($minPrice, 2) }}<small class="text-muted">/night</small>
                                                </span>
                                            </div>
                                        @endif
                                        
                                        <div class="d-grid gap-2">
                                            <a href="{{ route('listing.show', $listing->id) }}" class="btn btn-primary">
                                                <i class="fas fa-eye me-2"></i>View Details
                                            </a>
                                        </div>
                                    </div>
                                    
                                    <div class="text-muted small mt-2">
                                        <i class="fas fa-clock me-1"></i>
                                        Added {{ $favorite->created_at->diffForHumans() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($favorites->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $favorites->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>


@endsection