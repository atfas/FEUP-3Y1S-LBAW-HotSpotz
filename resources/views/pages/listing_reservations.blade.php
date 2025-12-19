@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="card mb-3">
        <div class="card-body bg-white p-3">
            <h3 class="mb-0">Reservations for: {{ $listing->title }}</h3>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($bookings->isEmpty())
        <div class="alert alert-info">No reservations found for this listing.</div>
    @else
        <div class="card shadow-sm">
            <div class="card-body p-3">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Guest</th>
                                <th>Check-in</th>
                                <th>Check-out</th>
                                <th>Guests</th>
                                <th>Status</th>
                                <th>Total</th>
                                <th>Booked At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bookings as $b)
                                @include('partials.listing_reservation_row', ['b' => $b])
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <div class="mt-3">
        <a href="{{ route('listing.show', ['id' => $listing->id]) }}" class="btn btn-light">Back to listing</a>
    </div>
</div>
@endsection
