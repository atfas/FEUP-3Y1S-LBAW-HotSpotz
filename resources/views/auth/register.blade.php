@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-lg">
                <div class="card-header bg-white text-center py-4">
                    <h4 class="mb-0 fw-bold" style="color: #722F37;">Create Account</h4>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <p class="text-center mb-4 text-muted">Join HotSpotz to plan your next going away trip</p>

                        <div class="form-floating mb-3">
                            <input
                                id="full_name"
                                type="text"
                                class="form-control @error('full_name') is-invalid @enderror"
                                name="full_name"
                                value="{{ old('full_name') }}"
                                required
                                autocomplete="name"
                                autofocus
                                placeholder="Full Name"
                            >
                            <label for="full_name">Full Name</label>
                            @error('full_name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input
                                        id="date_of_birth"
                                        type="date"
                                        class="form-control @error('date_of_birth') is-invalid @enderror"
                                        name="date_of_birth"
                                        value="{{ old('date_of_birth') }}"
                                        required
                                        placeholder="Date of Birth"
                                    >
                                    <label for="date_of_birth">Date of Birth</label>
                                    @error('date_of_birth')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input
                                        id="phone"
                                        type="tel"
                                        class="form-control @error('phone') is-invalid @enderror"
                                        name="phone"
                                        value="{{ old('phone') }}"
                                        required
                                        placeholder="Phone Number"
                                    >
                                    <label for="phone">Phone Number</label>
                                    @error('phone')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-floating mb-3">
                            <input
                                id="email"
                                type="email"
                                class="form-control @error('email') is-invalid @enderror"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                autocomplete="email"
                                placeholder="name@example.com"
                            >
                            <label for="email">Email Address</label>
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="row g-2 mb-4">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input
                                        id="password"
                                        type="password"
                                        class="form-control @error('password') is-invalid @enderror"
                                        name="password"
                                        required
                                        autocomplete="new-password"
                                        placeholder="Password"
                                    >
                                    <label for="password">Password</label>
                                    @error('password')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input
                                        id="password-confirm"
                                        type="password"
                                        class="form-control"
                                        name="password_confirmation"
                                        required
                                        autocomplete="new-password"
                                        placeholder="Confirm Password"
                                    >
                                    <label for="password-confirm">Confirm Password</label>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                Register
                            </button>
                        </div>

                        <div class="text-center mt-3">
                            <a class="text-decoration-none" href="{{ route('login') }}" style="color: #722F37;">
                                Already have an account? Login
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection