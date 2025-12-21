@extends('layouts.app')
@section('title', 'Host Statistics')

@section('content')
<link rel="stylesheet" href="{{ asset('css/admin.panel.css') }}">

<div class="container my-4">
    @include('partials.page_header', [
        'breadcrumbs' => [
            ['title' => 'Profile', 'url' => route('profile.show')],
            ['title' => 'Host Dashboard', 'url' => route('profile.host-dashboard')],
            ['title' => 'Statistics', 'url' => null],
        ]
    ])

    {{-- Header --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
            <h1 class="h3 mb-0" style="color: var(--color-dark-purple);">
                Host Statistics
            </h1>

            <div class="d-flex gap-4 align-items-center">
                <div class="text-center">
                    <small class="text-muted">Listings</small>
                    <h5>{{ $summary['total_listings'] }}</h5>
                </div>
                <div class="text-center">
                    <small class="text-muted">Bookings</small>
                    <h5>{{ $summary['total_bookings'] }}</h5>
                </div>
                <div class="text-center">
                    <small class="text-muted">Revenue</small>
                    <h5>€{{ number_format($summary['total_revenue'], 2) }}</h5>
                </div>
                <div class="text-center">
                    <small class="text-muted">Avg Rating</small>
                    <h5>{{ $summary['average_rating'] }}</h5>
                </div>
            </div>
        </div>
    </div>

    {{-- Listings Table --}}
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-3">
            <h5 class="mb-0" style="color: var(--color-dark-purple);">
                Listings Performance
            </h5>

            <div class="input-group" style="max-width: 300px;">
                <input type="text" class="form-control" id="listingSearch" placeholder="Search listings...">
                <span class="input-group-text">
                    <i class="fas fa-search"></i>
                </span>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 text-center" id="listingsTable">
                    <thead>
                        <tr>
                            <th>Listing</th>
                            <th>Bookings</th>
                            <th>Revenue</th>
                            <th>Rating</th>
                            <th>Reviews</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($listings as $listing)
                            <tr>
                                <td>
                                    <strong>{{ $listing->title }}</strong>
                                </td>

                                <td>
                                    <span>
                                        {{ $listing->total_bookings }}
                                    </span>
                                </td>

                                <td>
                                    <span>
                                        €{{ number_format($listing->total_revenue, 2) }}
                                    </span>
                                </td>

                                <td>
                                    <span>
                                        {{ $listing->average_rating }}
                                    </span>
                                </td>

                                <td>
                                    <span>
                                        {{ $listing->num_reviews }}
                                    </span>
                                </td>

                                <td>
                                    <a href="{{ route('listing.show', $listing->id) }}"
                                    class="action-btn action-view"
                                    title="View listing">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    <a href="{{ route('listing.edit', $listing->id) }}"
                                    class="action-btn action-edit"
                                    title="Edit listing">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <a href="{{ route('listing.reservations', $listing->id) }}"
                                    class="action-btn action-calendar"
                                    title="View reservations">
                                        <i class="fas fa-calendar"></i>
                                    </a>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fas fa-chart-line fa-2x mb-2"></i>
                                    <p class="mb-0">No listings statistics available</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>


<script>
document.getElementById('listingSearch').addEventListener('keyup', function () {
    const value = this.value.toLowerCase();
    document.querySelectorAll('#listingsTable tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(value) ? '' : 'none';
    });
});
</script>
@endsection
