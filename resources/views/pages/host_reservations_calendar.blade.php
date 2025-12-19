@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <h4 class="mb-0">Reservations calendar for {{ $target->full_name }}</h4>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div id="calendar" style="min-height:500px;"></div>
        </div>
    </div>
</div>

@php
    $safeEvents = collect($events)->map(callback: function($ev) {
        $arr = is_array($ev) ? $ev : (array) $ev;
        foreach ($arr as $k => $v) {
            if (is_string($v)) {
                $arr[$k] = strip_tags($v);
            }
        }
        return $arr;
    })->all();
@endphp

<script>
    window.hostReservationsEvents = @json($safeEvents);
    (function(){
        var meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) {
            window.csrfToken = meta.getAttribute('content');
        } else {
            window.csrfToken = '';
        }
    })();
</script>

@push('styles')
<link href="{{ asset('css/reservations_calendar.css') }}" rel="stylesheet">
@endpush

@push('scripts')
<script src="{{ asset('vendor/fullcalendar/core.global.min.js') }}" defer></script>
<script src="{{ asset('vendor/fullcalendar/daygrid.global.min.js') }}" defer></script>
<script src="{{ asset('js/reservations_calendar.js') }}" defer></script>
@endpush

@endsection
