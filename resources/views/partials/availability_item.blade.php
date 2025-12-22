@php
    $a = is_array($availability) ? $availability : (array)$availability;
    $idx = isset($index) ? $index : (isset($a['index']) ? $a['index'] : null);
    $showActions = isset($showActions) ? (bool)$showActions : false;
    $originalPrice = isset($a['price_per_night']) ? (float)$a['price_per_night'] : 0;
    $isPremium = Auth::check() && Auth::user()->isPremium();
    $discount = $isPremium ? Auth::user()->getDiscountPercentage() : 0;
    $discountedPrice = $originalPrice * (1 - $discount / 100);
@endphp

<li class="list-group-item d-flex justify-content-between align-items-center availability-item" @if($idx !== null) data-index="{{ $idx }}" @endif>
    <div>
        <strong>{{ $a['start_date'] ?? '' }}</strong> → <strong>{{ $a['end_date'] ?? '' }}</strong>
        <div class="small text-muted">
            Price: 
            @if($isPremium && $discount > 0)
                <span class="text-decoration-line-through">${{ number_format($originalPrice, 2) }}</span>
                <span class="text-success fw-bold">${{ number_format($discountedPrice, 2) }}</span>
                <span class="badge bg-success ms-1">-{{ $discount }}%</span>
            @else
                ${{ number_format($originalPrice, 2) }}
            @endif
            — Check-in: {{ $a['check_in_time'] ?? '' }} — Check-out: {{ $a['check_out_time'] ?? '' }}
        </div>
    </div>

    @if($showActions)
        <div>
            <button type="button" class="btn btn-sm btn-outline-primary me-1" data-edit-index="{{ $idx ?? '' }}">Edit</button>
            <button type="button" class="btn btn-sm btn-outline-danger" data-remove-index="{{ $idx ?? '' }}">Remove</button>
        </div>
    @else
        <div class="text-end">
            @if($isPremium && $discount > 0)
                <small class="text-decoration-line-through text-muted">${{ number_format($originalPrice, 2) }}</small>
                <strong class="text-success">${{ number_format($discountedPrice, 2) }}</strong>
            @else
                <strong>${{ number_format($originalPrice, 2) }}</strong>
            @endif
        </div>
    @endif
</li>

