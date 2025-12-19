@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row">
        @include('partials.listing_info')
        @include('partials.booking_form')
    </div>
</div>

@push('scripts')
    <script src="{{ asset('js/payment.js') }}" defer></script>
@endpush

@endsection
