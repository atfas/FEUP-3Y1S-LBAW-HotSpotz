<tr>
    <td>{{ $b->id }}</td>
    <td>
        <div>{{ $b->guest_name }}</div>
        <small class="text-muted">{{ $b->guest_email }}</small>
    </td>
    <td>{{ $b->check_in_date }}</td>
    <td>{{ $b->check_out_date }}</td>
    <td>{{ $b->num_guests }}</td>
    <td>
        <span class="badge 
            @if($b->status == 'Confirmed') bg-success
            @elseif($b->status == 'PendingPayment') bg-warning text-dark
            @elseif($b->status == 'Cancelled' || $b->status == 'Rejected') bg-danger
            @elseif($b->status == 'Completed') bg-info text-dark
            @else bg-secondary
            @endif">
            {{ $b->status }}
        </span>
    </td>
    <td>{{ $b->total_price !== null ? '$' . number_format($b->total_price,2) : 'N/A' }}</td>
    <td>{{ $b->created_at }}</td>
    <td>
        @php
            $now = \Carbon\Carbon::now();
            $start = \Carbon\Carbon::parse($b->check_in_date)->startOfDay();
            $hours = $now->greaterThanOrEqualTo($start) ? 0 : $now->diffInHours($start);
            $threshold = 48;
        @endphp

        @if($b->status === 'PendingPayment' && ($b->pending_txn_count ?? 0) > 0)
            <form method="POST" action="{{ route('bookings.hostConfirmPayment', ['booking' => $b->id]) }}" onsubmit="return confirm('Confirm that you received the payment? This will set the booking to Confirmed.');" style="display:inline-block">
                @csrf
                <button type="submit" class="btn btn-sm btn-success">Confirm Payment</button>
            </form>
        @endif

        @if($hours < $threshold)
            <div class="text-danger small">Cannot delete within {{ $threshold }} hours of start.</div>
        @else
            <form method="POST" action="{{ route('bookings.hostReject', ['booking' => $b->id]) }}" onsubmit="return confirm('Reject reservation and issue refund?');" style="display:inline-block">
                @csrf
                <button type="submit" class="btn btn-sm btn-danger">Reject & Refund</button>
            </form>
        @endif
    </td>
</tr>
