@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h4 class="mb-0 fw-bold text-wine">Edit Profile Picture</h4>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('profile.picture.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-4 text-center">
                            @if($user->profile_picture_url)
                                <img src="{{ $user->profile_picture_url }}" alt="Profile" class="rounded-circle mb-3" style="width: 120px; height: 120px; object-fit: cover;">
                            @else
                                <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 120px; height: 120px; font-size: 3rem; background-color: #f8f9fa; border: 4px solid #722F37; color: #722F37;">
                                    {{ strtoupper(substr($user->full_name, 0, 1)) }}
                                </div>
                            @endif
                        </div>

                        <div class="mb-4">
                            <label for="profile_picture_url" class="form-label fw-bold">Profile Picture URL</label>
                            <input type="url" class="form-control @error('profile_picture_url') is-invalid @enderror" id="profile_picture_url" name="profile_picture_url" value="{{ old('profile_picture_url', $user->profile_picture_url) }}" placeholder="https://example.com/my-photo.jpg">
                            @error('profile_picture_url')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <div class="form-text">Enter a URL for your profile picture.</div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-5">
                            <a href="{{ route('profile.show') }}" class="text-decoration-none text-muted">
                                <i class="fa-solid fa-arrow-left me-1"></i> Back to Profile
                            </a>
                            <button type="submit" class="btn btn-primary px-4">
                                Save Picture
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
