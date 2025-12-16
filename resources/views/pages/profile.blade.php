@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm border-0">
                <div class="card-body p-5">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show mb-5" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Header Section -->
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-5 gap-3">
                        <div class="d-flex align-items-center gap-4">
                            <!-- Avatar Container -->
                            <div class="position-relative rounded-circle" style="width: 120px; height: 120px; border: 4px solid #722F37;">
                                @if($user->profile_picture_url)
                                    <img src="{{ $user->profile_picture_url }}" alt="Profile" class="rounded-circle w-100 h-100" style="object-fit: cover;">
                                @else
                                    <div class="rounded-circle d-flex align-items-center justify-content-center w-100 h-100" style="font-size: 3rem; background-color: #f8f9fa; color: #722F37">
                                        {{ strtoupper(substr($user->full_name, 0, 1)) }}
                                    </div>
                                @endif

                                @if($isOwner)
                                    <!-- Edit/Add Picture Button -->
                                    <a href="{{ route('profile.picture.edit') }}" class="btn btn-sm btn-dark position-absolute bottom-0 end-0 rounded-circle d-flex align-items-center justify-content-center shadow-sm border-2 border-white" style="width: 35px; height: 35px; background-color: #722F37; border-color: #fff !important;">
                                        <i class="fa-solid fa-pencil" style="font-size: 0.9rem;"></i>
                                    </a>

                                    <!-- Delete Picture Button -->
                                    @if($user->profile_picture_url)
                                        <form action="{{ route('profile.picture.delete') }}" method="POST" class="position-absolute bottom-0 start-0">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger rounded-circle d-flex align-items-center justify-content-center shadow-sm border-2 border-white" style="width: 35px; height: 35px; border-color: #fff !important;" onclick="return confirm('Are you sure you want to remove your profile picture?')">
                                                <i class="fa-solid fa-trash" style="font-size: 0.9rem;"></i>
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                            
                            <!-- Name and Email -->
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <h4 class="fw-bold mb-0 text-wine">{{ explode(' ', $user->full_name)[0] }}</h4>
                                    @if($user->is_admin)
                                        <span class="badge bg-danger">Admin</span>
                                    @elseif($user->application_status === 'Approved')
                                        <span class="badge bg-warning">Host</span>
                                    @else
                                        <span class="badge bg-secondary">User</span>
                                    @endif
                                </div>
                                @if($isOwner)
                                    <p class="text-muted mb-0">{{ $user->email }}</p>
                                @endif
                            </div>
                        </div>

                        <!-- Action Buttons (only for owner) -->
                        @if($isOwner)
                            <div class="d-flex gap-2 w-100 w-md-auto justify-content-center justify-content-md-end">
                                <a href="{{ route('profile.edit') }}" class="btn btn-primary px-4">Edit</a>
                                <a href="{{ route('bookings.index') }}" class="btn btn-primary px-4">Show Bookings</a>
                            </div>
                        @endif
                    </div>

                    <!-- Fields Grid -->
                    <div class="row g-4">
                        <!-- Full Name -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted">Full Name</label>
                            <input type="text" class="form-control bg-light" value="{{ $user->full_name }}" readonly>
                        </div>

                        @if($isOwner)
                            <!-- Email (only visible to owner) -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-muted">Email Address</label>
                                <input type="text" class="form-control bg-light" value="{{ $user->email }}" readonly>
                            </div>

                            <!-- Phone (only visible to owner) -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-muted">Phone Number</label>
                                <input type="text" class="form-control bg-light" value="{{ $user->phone ?? 'Not provided' }}" readonly>
                            </div>

                            <!-- Date of Birth (only visible to owner) -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-muted">Date of Birth</label>
                                <input type="text" class="form-control bg-light" value="{{ \Carbon\Carbon::parse($user->date_of_birth)->format('F j, Y') }}" readonly>
                            </div>
                        @else
                            <!-- Member Since (visible to others) -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-muted">Member Since</label>
                                <input type="text" class="form-control bg-light" value="{{ \Carbon\Carbon::parse($user->registration_date)->format('F Y') }}" readonly>
                            </div>
                        @endif
                    </div>

                    <!-- Host Details Section (only for approved hosts) -->
                    @if($user->application_status === 'Approved')
                        <div class="mt-5 pt-4 border-top">
                            <h5 class="fw-bold mb-4 text-wine">
                                <i class="fa-solid fa-house me-2"></i>Host Information
                            </h5>
                            
                            @if($user->answers)
                                <div class="mb-4">
                                    <label class="form-label fw-bold text-muted">About Me</label>
                                    <div class="form-control bg-light rounded">
                                        <p class="mb-0">{{ $user->answers }}</p>
                                    </div>
                                </div>
                            @endif

                            @if($user->verified_at)
                                <div class="d-flex align-items-center gap-2 text-success">
                                    <i class="fa-solid fa-circle-check"></i>
                                    <span>Verified Host since {{ \Carbon\Carbon::parse($user->verified_at)->format('F Y') }}</span>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Premium Subscription Promo (only for users who are not premium) -->
                    @if((!isset($user->subscription) || !$user->subscription || $user->subscription->member_status !== 'Active'))
                        @include('partials.subscription_promo')
                    @endif

                    <!-- Account Deletion (only for owner) -->
                    @if($isOwner)
                        <div class="mt-5 pt-4 border-top">
                            <div class="alert alert-danger d-flex align-items-start gap-3" role="alert">
                                <i class="fa-solid fa-triangle-exclamation fs-4 mt-1"></i>
                                <div class="flex-grow-1">
                                    <p class="mb-2">Once you delete your account, there is no going back.</p>
                                    <form action="{{ route('profile.destroy') }}" method="POST" class="mt-3" onsubmit="return confirm('Are you absolutely sure you want to delete your account? This action cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">
                                            <i class="fa-solid fa-trash me-2"></i>Delete My Account
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

