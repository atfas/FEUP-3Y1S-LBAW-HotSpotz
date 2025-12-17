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

    <!-- Bookings Panel Header with Breadcrumbs -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <!-- Breadcrumbs -->
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="fas fa-home"></i> Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">My Bookings</li>
                </ol>
            </nav>
            
            <!-- Header Content -->
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
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
                <table class="table">
    <thead>
        <tr>
            <th>Listing</th>
            <th>Location</th>
            <th>Check-in</th>
            <th>Check-out</th>
            <th>Guests</th>
            <th>Status</th>
            <th>Total</th>
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
                    @elseif($booking['status'] == 'Completed') bg-info
                    @else bg-secondary
                    @endif">
                    {{ $booking['status'] }}
                </span>
            </td>
            <td>${{ $booking['total_price'] }}</td>
            <td>
                @if($booking['status'] === 'Completed' && !$booking['has_review'])
                    <!-- Add Review Button -->
                    <a href="{{ route('reviews.create', $booking['id']) }}" 
                       class="action-btn text-success" 
                       title="Leave a review">
                        <i class="fas fa-star"></i>
                    </a>
                @elseif($booking['has_review'])
                    <!-- Edit Review Button -->
                    <a href="{{ route('reviews.edit', $booking['review_id']) }}" 
                       class="action-btn text-warning" 
                       title="Edit review">
                        <i class="fas fa-edit"></i>
                    </a>
                    
                    <!-- Delete Review Form -->
                    <form action="{{ route('reviews.destroy', $booking['review_id']) }}" 
                          method="POST" 
                          style="display: inline;"
                          onsubmit="return confirm('Are you sure you want to delete this review?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="action-btn text-danger" title="Delete review">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </form>
                @endif

                @if(in_array($booking['status'], ['PendingPayment', 'Confirmed']))
                    <!-- Edit Booking Button -->
                    <a href="{{ route('bookings.edit', $booking['id']) }}" 
                       class="action-btn text-primary" 
                       title="Edit booking">
                        <i class="fas fa-edit"></i>
                    </a>

                    <!-- Delete Booking Form -->
                    <form action="{{ route('bookings.destroy', $booking['id']) }}" 
                          method="POST" 
                          style="display: inline;"
                          onsubmit="return confirm('Are you sure you want to cancel this booking?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="action-btn text-danger" title="Cancel booking">
                            <i class="fas fa-times-circle"></i>
                        </button>
                    </form>
                @endif
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

<style>
    /* Breadcrumb Styles */
    .breadcrumb {
        background-color: transparent;
        padding: 0;
        font-size: 0.9rem;
    }
    
    .breadcrumb-item + .breadcrumb-item::before {
        content: "›";
        font-size: 1.2rem;
        color: #6c757d;
    }
    
    .breadcrumb-item a {
        color: #722F37;
        text-decoration: none;
        transition: all 0.2s;
    }
    
    .breadcrumb-item a:hover {
        color: #501f25;
        text-decoration: underline;
    }
    
    .breadcrumb-item.active {
        color: #6c757d;
    }

    .nav-tabs {
        border-bottom: none;
    }
    
    .nav-tabs .nav-link {
        border-top-left-radius: 0.5rem;
        border-top-right-radius: 0.5rem;
        color: var(--color-light-purple, #722F37);
        font-weight: 500;
        border: 1px solid transparent;
    }
    
    .nav-tabs .nav-link.active {
        color: #722F37;
        background-color: white;
        border-color: #dee2e6 #dee2e6 white #dee2e6;
        border-bottom: none;
        font-weight: 600;
    }
    
    .rounded-top-0 {
        border-top-left-radius: 0 !important;
        border-top-right-radius: 0 !important;
    }
    
    .table thead {
        background-color: white;
        border-bottom: 2px solid #dee2e6;
    }
    
    .table thead th {
        background-color: white;
        color: #722F37;
        font-weight: 600;
        padding: 1rem;
        border-bottom: 2px solid #dee2e6;
        vertical-align: middle;
    }
    
    .table {
        border-top: none;
    }
    
    .action-btn {
        border: none;
        background: transparent;
        padding: 0.25rem 0.5rem;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 1.1rem;
    }
    
    .action-btn:hover {
        background-color: #f8f9fa;
        border-radius: 4px;
        transform: scale(1.1);
    }
    
    .pagination {
        margin: 0;
    }
    
    .page-link {
        color: #722F37;
        border-color: #dee2e6;
        padding: 0.5rem 0.75rem;
        transition: all 0.2s;
    }
    
    .page-link:hover {
        background-color: #f8f9fa;
        border-color: #722F37;
        color: #722F37;
        transform: translateY(-1px);
    }
    
    .page-item.active .page-link {
        background-color: #722F37;
        border-color: #722F37;
        color: white;
    }
    
    .page-item.disabled .page-link {
        color: #6c757d;
        background-color: #fff;
        border-color: #dee2e6;
    }
</style>
@endsection