@extends('layouts.app')
@section('title', 'Host Dashboard')

@section('content')
<link rel="stylesheet" href="{{ asset('css/admin.panel.css') }}">

<div class="container my-4">

    {{-- Breadcrumbs --}}
    @include('partials.page_header', [
        'breadcrumbs' => [
            ['title' => 'Profile', 'url' => route('profile.show')],
            ['title' => 'Host Dashboard', 'url' => null],
        ]
    ])

    {{-- Header --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body text-center">
            <h1 class="h3 mb-1" style="color: var(--color-dark-purple);">
                Host Dashboard
            </h1>
            <p class="text-muted mb-0">
                Manage your hosting activity
            </p>
        </div>
    </div>

    {{-- Navigation Cards --}}
    <div class="row g-4 justify-content-center">

        {{-- Host Statistics --}}
        <div class="col-md-5">
            <a href="{{ route('profile.hostStatistics') }}"
               class="text-decoration-none">
                <div class="card shadow-sm h-100 hover-card">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-chart-line fa-3x mb-3"
                           style="color: var(--color-gold);"></i>

                        <h4 style="color: var(--color-dark-purple);">
                            Host Statistics
                        </h4>

                        <p class="text-muted mb-0">
                            View earnings, listings performance, and trends
                        </p>
                    </div>
                </div>
            </a>
        </div>

        {{-- Reservations --}}
        <div class="col-md-5">
            <a href="{{ route('profile.hostReservations') }}"
               class="text-decoration-none">
                <div class="card shadow-sm h-100 hover-card">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-calendar-alt fa-3x mb-3"
                           style="color: var(--color-light-purple);"></i>

                        <h4 style="color: var(--color-dark-purple);">
                            Reservations
                        </h4>

                        <p class="text-muted mb-0">
                            View and manage your reservations
                        </p>
                    </div>
                </div>
            </a>
        </div>

    </div>
</div>

{{-- Hover Effect --}}
<style>
.hover-card {
    transition: transform .2s ease, box-shadow .2s ease;
}
.hover-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 .75rem 1.5rem rgba(0,0,0,.12);
}
</style>

@endsection
