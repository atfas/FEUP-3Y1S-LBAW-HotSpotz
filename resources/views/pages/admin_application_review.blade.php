@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-9">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-4 border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1 fw-bold text-wine">Host Application Review</h3>
                            <p class="text-muted mb-0">Reviewing application for {{ $user->full_name }}</p>
                        </div>
                        <span class="badge bg-warning fs-6">Pending Review</span>
                    </div>
                </div>
                <div class="card-body p-4">
                    <!-- Applicant Information -->
                    <div class="mb-4">
                        <h5 class="fw-bold mb-3">Applicant Information</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-muted">Full Name</label>
                                <p class="mb-0">{{ $user->full_name }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-muted">Email</label>
                                <p class="mb-0">{{ $user->email }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-muted">Phone</label>
                                <p class="mb-0">{{ $user->phone ?? 'Not provided' }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-muted">Date of Birth</label>
                                <p class="mb-0">{{ $user->date_of_birth->format('F d, Y') }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-muted">Submitted At</label>
                                <p class="mb-0">{{ $user->submitted_at->format('F d, Y g:i A') }}</p>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Application Answers -->
                    <div class="mb-4">
                        <h5 class="fw-bold mb-3">Motivation</h5>
                        <div class="p-3 bg-light rounded">
                            <p class="mb-0 text-dark">{{ $user->answers }}</p>
                        </div>
                    </div>

                    <!-- Verification Documents -->
                    <div class="mb-4">
                        <h5 class="fw-bold mb-3">Verification Documents</h5>
                        <div class="p-3 bg-light rounded">
                            <a href="{{ $user->verification_docs }}" target="_blank" class="text-decoration-none">
                                <i class="fa-solid fa-link me-2"></i>{{ $user->verification_docs }}
                            </a>
                        </div>
                    </div>

                    <!-- Payment Details -->
                    <div class="mb-4">
                        <h5 class="fw-bold mb-3">Payment Details</h5>
                        <div class="p-3 bg-light rounded">
                            <p class="mb-0 text-dark">{{ $user->payout_details }}</p>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-between align-items-center mt-5 pt-4 border-top">
                        <a href="{{ route('admin.index') }}" class="text-decoration-none text-muted">
                            <i class="fa-solid fa-arrow-left me-2"></i>Back to Admin Panel
                        </a>
                        <div class="d-flex gap-2">
                            <form action="{{ route('admin.applications.reject', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to reject this application?');">
                                @csrf
                                @method('POST')
                                <button type="submit" class="btn btn-outline-danger px-4">
                                    <i class="fa-solid fa-times me-2"></i>Reject
                                </button>
                            </form>
                            <form action="{{ route('admin.applications.approve', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to approve this application?');">
                                @csrf
                                @method('POST')
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="fa-solid fa-check me-2"></i>Approve
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
