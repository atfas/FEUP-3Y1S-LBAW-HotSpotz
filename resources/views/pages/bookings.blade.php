@extends('layouts.app')

@section('content')
<div class="container my-4">
    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Bookings Panel Header -->
    <div class="card shadow-sm mb-4">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
            <h1 class="h3 mb-0 text-wine">My Bookings</h1>
            <div class="d-flex gap-3 align-items-center">
                <div class="text-center">
                    <small class="text-muted">Total Bookings</small>
                    <h5>{{ $totalBookings }}</h5>
                </div>
                <div class="text-center">
                    <small class="text-muted">Active</small>
                    <h5>{{ $activeBookings }}</h5>
                </div>
            </div>
        </div>
    </div>

    <!-- Bookings Tab -->
    <ul class="nav nav-tabs mb-0">
        <li class="nav-item">
            <button class="nav-link active" style="margin: 0 !important; border-bottom-left-radius: 0 !important; border-bottom-right-radius: 0 !important;">Bookings</button>
        </li>
    </ul>

    <!-- Bookings Table -->
    <div class="card shadow-sm rounded-top-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-3">
            <h5 class="mb-0 text-wine">Manage Your Bookings</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 text-center">
                    <thead>
                        <tr>
                            <th>Listing</th>
                            <th>Location</th>
                            <th>Check-in</th>
                            <th>Check-out</th>
                            <th>Guests</th>
                            <th>Status</th>
                            <th>Total Price</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bookings as $booking)
                        <tr>
                            <td>{{ $booking['listing_title'] }}</td>
                            <td>{{ $booking['listing_location'] }}</td>
                            <td>{{ $booking['check_in_date'] }}</td>
                            <td>{{ $booking['check_out_date'] }}</td>
                            <td>{{ $booking['num_guests'] }}</td>
                            <td>
                                <span class="badge 
                                    @if($booking['status'] == 'Confirmed') bg-success
                                    @elseif($booking['status'] == 'PendingPayment') bg-warning
                                    @elseif($booking['status'] == 'Cancelled') bg-danger
                                    @else bg-secondary
                                    @endif">
                                    {{ $booking['status'] }}
                                </span>
                            </td>
                            <td>${{ $booking['total_price'] }}</td>
                            <td>
                                <!-- Edit Button -->
                                <a href="{{ route('bookings.edit', $booking['id']) }}" 
                                   class="action-btn text-primary" 
                                   title="Edit booking">
                                    <i class="fas fa-edit"></i>
                                </a>

                                <!-- Delete Form -->
                                <form action="{{ route('bookings.destroy', $booking['id']) }}" 
                                      method="POST" 
                                      style="display: inline;"
                                      onsubmit="return confirm('Are you sure you want to delete this booking?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="action-btn text-danger" title="Delete booking">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-calendar-times fa-2x mb-2"></i>
                                <p>No bookings found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Pagination Footer -->
        @if($bookings->hasPages())
        <div class="card-footer bg-white">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="text-muted small">
                    Showing {{ $bookings->firstItem() }} to {{ $bookings->lastItem() }} of {{ $bookings->total() }} entries
                </div>
                
                <nav aria-label="Page navigation">
                    <ul class="pagination mb-0">
                        @if ($bookings->onFirstPage())
                            <li class="page-item disabled">
                                <span class="page-link"><i class="fas fa-chevron-left"></i></span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $bookings->previousPageUrl() }}" rel="prev">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                        @endif

                        @foreach ($bookings->getUrlRange(1, $bookings->lastPage()) as $page => $url)
                            @if ($page == $bookings->currentPage())
                                <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                            @else
                                <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                            @endif
                        @endforeach

                        @if ($bookings->hasMorePages())
                            <li class="page-item">
                                <a class="page-link" href="{{ $bookings->nextPageUrl() }}" rel="next">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        @else
                            <li class="page-item disabled">
                                <span class="page-link"><i class="fas fa-chevron-right"></i></span>
                            </li>
                        @endif
                    </ul>
                </nav>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
