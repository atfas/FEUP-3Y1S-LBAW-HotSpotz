@php
    $r = $review;
@endphp

<div class="review-item p-3 rounded border">
    <div class="d-flex align-items-start gap-3">
        {{-- Profile Picture --}}
        <div class="flex-shrink-0">
            @if(!empty($r->guest_photo))
                <img src="{{ $r->guest_photo }}" 
                     alt="{{ $r->guest_name ?? 'Guest' }}" 
                     class="review-avatar rounded-circle"
                     onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($r->guest_name ?? 'Guest') }}&background=0d6efd&color=fff&size=128'">
            @else
                <div class="review-avatar rounded-circle bg-primary text-white d-flex align-items-center justify-content-center">
                    <span class="fw-bold">{{ strtoupper(substr($r->guest_name ?? 'G', 0, 1)) }}</span>
                </div>
            @endif
        </div>

        {{-- Review Content --}}
        <div class="flex-grow-1">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <h6 class="mb-0 fw-bold">{{ $r->guest_name ?? 'Anonymous Guest' }}</h6>
                    <div class="review-rating mt-1">
                        @for($i = 1; $i <= 5; $i++)
                            @if($i <= ($r->rating ?? 0))
                                <i class="fas fa-star text-warning"></i>
                            @else
                                <i class="far fa-star text-muted"></i>
                            @endif
                        @endfor
                        <span class="ms-1 text-muted small">{{ $r->rating ?? 0 }}/5</span>
                    </div>
                </div>
                <div class="text-muted small">
                    <i class="far fa-clock me-1"></i>
                    {{ isset($r->created_at) ? \Illuminate\Support\Carbon::parse($r->created_at)->diffForHumans() : '' }}
                </div>
            </div>

            @if(!empty($r->comment))
                <p class="mb-0 text-secondary">{{ $r->comment }}</p>
            @else
                <p class="mb-0 text-muted fst-italic">No written review provided.</p>
            @endif

            {{-- Host Response (if exists) --}}
            @if(!empty($r->answer_content))
                <div class="host-response mt-3 p-3 bg-light rounded">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="fas fa-reply text-primary"></i>
                        <strong class="small text-primary">Host Response</strong>
                        @if(!empty($r->answered_at))
                            <span class="text-muted small ms-auto">
                                {{ \Illuminate\Support\Carbon::parse($r->answered_at)->diffForHumans() }}
                            </span>
                        @endif
                    </div>
                    <p class="mb-0 small">{{ $r->answer_content }}</p>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.review-item {
    transition: all 0.2s ease;
    background-color: #fff;
}

    .review-item:hover {
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        transform: translateY(-2px);
    }

.review-avatar {
    width: 48px;
    height: 48px;
    object-fit: cover;
    font-size: 1.25rem;
}

.review-rating i {
    font-size: 0.875rem;
}

.host-response {
    border-left: 3px solid #0d6efd;
}

@media (max-width: 576px) {
    .review-avatar {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }
    
    .review-item {
        padding: 0.75rem !important;
    }
}
</style>