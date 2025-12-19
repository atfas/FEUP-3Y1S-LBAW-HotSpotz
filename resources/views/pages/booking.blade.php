@extends('layouts.app')

@section('content')
<div class="container mt-5">
    @include('partials.page_header', [
        'breadcrumbs' => [
            ['title' => $listing->title, 'url' => route('listing.show', $listing->id)],
            ['title' => 'Booking', 'url' => null],
        ]
    ])
    <div class="row">
        @include('partials.listing_info')
        @include('partials.booking_form')
    </div>
</div>

@push('scripts')
    <script src="{{ asset('js/payment.js') }}" defer></script>
@endpush

@endsection
