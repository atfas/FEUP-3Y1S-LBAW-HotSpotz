@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-4">
                    <h3 class="mb-0 fw-bold text-wine">Become a Host</h3>
                    <p class="text-muted mb-0 mt-2">Join our community of hosts and start earning by sharing your space</p>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('host.apply.store') }}">
                        @csrf

                        <!-- Why do you want to become a host? -->
                        <div class="mb-4">
                            <label for="answers" class="form-label fw-bold">
                                Why do you want to become a host? <span class="text-danger">*</span>
                            </label>
                            <textarea 
                                class="form-control @error('answers') is-invalid @enderror" 
                                id="answers" 
                                name="answers" 
                                rows="6"
                                placeholder="Tell us about your motivation, experience, and what makes your space special..."
                                required
                            >{{ old('answers') }}</textarea>
                            @error('answers')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <div class="form-text">Minimum 50 characters. Help us understand why you'd be a great host.</div>
                        </div>

                        <!-- Verification Documents -->
                        <div class="mb-4">
                            <label for="verification_docs" class="form-label fw-bold">
                                Verification Documents URL <span class="text-danger">*</span>
                            </label>
                            <input 
                                type="text" 
                                class="form-control @error('verification_docs') is-invalid @enderror" 
                                id="verification_docs" 
                                name="verification_docs" 
                                value="{{ old('verification_docs') }}"
                                placeholder="https://example.com/my-documents"
                                required
                            >
                            @error('verification_docs')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <div class="form-text">
                                Please upload your ID or verification documents to a cloud service (Google Drive, Dropbox, etc.) 
                                and provide the shareable link here.
                            </div>
                        </div>

                        <!-- Payout Details -->
                        <div class="mb-4">
                            <label for="payout_details" class="form-label fw-bold">
                                Payout Details <span class="text-danger">*</span>
                            </label>
                            <input 
                                type="text" 
                                class="form-control @error('payout_details') is-invalid @enderror" 
                                id="payout_details" 
                                name="payout_details" 
                                value="{{ old('payout_details') }}"
                                placeholder="PayPal email, bank account, or other payment information"
                                required
                            >
                            @error('payout_details')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <div class="form-text">
                                How would you like to receive payments? (e.g., PayPal: your@email.com, Bank Transfer: IBAN...)
                            </div>
                        </div>

                        <!-- Information Notice -->
                        <div class="alert alert-info d-flex align-items-start gap-3 mb-4" role="alert">
                            <i class="fa-solid fa-circle-info fs-5 mt-1"></i>
                            <div class="flex-grow-1">
                                <h6 class="alert-heading fw-bold mb-2">Application Review Process</h6>
                                <p class="mb-0">
                                    After submitting your application, our team will review your information. 
                                    You'll be notified once your application has been reviewed. 
                                </p>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between align-items-center mt-5">
                            <a href="{{ route('profile.show') }}" class="text-decoration-none text-muted">
                                <i class="fa-solid fa-arrow-left me-1"></i> Back to Profile
                            </a>
                            <button type="submit" class="btn btn-primary px-5">
                                <i class="fa-solid fa-paper-plane me-2"></i>Submit Application
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
