@php
    $r = $review;
@endphp

<div class="mb-3 border-bottom pb-2 review-item">
    <div class="d-flex justify-content-between">
        <div>
            <strong>Rating: {{ $r->rating ?? 'N/A' }}/5</strong>
        </div>
        <div class="text-muted">{{ isset($r->created_at) ? \Illuminate\Support\Carbon::parse($r->created_at)->toDateString() : '' }}</div>
    </div>
    <p class="mb-1">{{ $r->comment ?? '' }}</p>
</div>
