@php
    $a = is_array($availability) ? $availability : (array)$availability;
    $idx = isset($index) ? $index : (isset($a['index']) ? $a['index'] : null);
    $showActions = isset($showActions) ? (bool)$showActions : false;
    $price = isset($a['price_per_night']) ? number_format((float)$a['price_per_night'], 2) : 'N/A';
@endphp

<li class="list-group-item d-flex justify-content-between align-items-center availability-item" @if($idx !== null) data-index="{{ $idx }}" @endif>
    <div>
        <strong>{{ $a['start_date'] ?? '' }}</strong> → <strong>{{ $a['end_date'] ?? '' }}</strong>
        <div class="small text-muted">Price: ${{ $price }} — Check-in: {{ $a['check_in_time'] ?? '' }} — Check-out: {{ $a['check_out_time'] ?? '' }}</div>
    </div>

    @if($showActions)
        <div>
            <button type="button" class="btn btn-sm btn-outline-primary me-1" data-edit-index="{{ $idx ?? '' }}">Edit</button>
            <button type="button" class="btn btn-sm btn-outline-danger" data-remove-index="{{ $idx ?? '' }}">Remove</button>
        </div>
    @else
        <div class="text-end"><strong>${{ $price }}</strong></div>
    @endif
</li>
