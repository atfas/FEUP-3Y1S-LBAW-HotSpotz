@extends('layouts.app')
@section('title', 'Reset Password')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-lg">
                <div class="card-header bg-white text-center py-4">
                    <h4 class="mb-0 fw-bold" style="color: #722F37;">Create New Password</h4>
                </div>
                <div class="card-body p-4">
                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    <p class="text-center mb-4 text-muted">Enter your new password below.</p>

                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf

                        <input type="hidden" name="token" value="{{ $token }}">
                        <input type="hidden" name="email" value="{{ $email }}">

                        <div class="form-floating mb-3">
                            <input
                                id="password"
                                type="password"
                                class="form-control @error('password') is-invalid @enderror"
                                name="password"
                                required
                                autocomplete="new-password"
                                autofocus
                                placeholder="New Password"
                            >
                            <label for="password">New Password</label>
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-floating mb-3">
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

                        @error('email')
                            <div class="alert alert-danger" role="alert">
                                {{ $message }}
                            </div>
                        @enderror

                        <div class="form-text text-muted mb-3">
                            <i class="fa-solid fa-info-circle me-1"></i>
                            Password must be at least 8 characters long.
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                Reset Password
                            </button>
                        </div>

                        <div class="text-center mt-3">
                            <a class="text-decoration-none" href="{{ route('login') }}" style="color: #722F37;">
                                <i class="fa-solid fa-arrow-left me-1"></i>Back to Login
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
