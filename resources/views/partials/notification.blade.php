<div class="list-group-item notification-item {{ $notification->is_read ? '' : 'bg-light' }}" data-notification-id="{{ $notification->id }}">
    <div class="d-flex w-100 justify-content-between align-items-center">
        <div class="d-flex align-items-start">
            <div class="notification-icon me-3">
                @switch($notification->type)
                    @case('BookingConfirmed')
                        <i class="fa-solid fa-calendar-check fa-lg text-success"></i>
                        @break
                    @case('BookingCanceled')
                        <i class="fa-solid fa-calendar-xmark fa-lg text-danger"></i>
                        @break
                    @case('PaymentReceived')
                        <i class="fa-solid fa-credit-card fa-lg text-info"></i>
                        @break
                    @case('Remainder')
                        <i class="fa-solid fa-clock fa-lg text-warning"></i>
                        @break
                    @case('MessageReceived')
                        <i class="fa-solid fa-message fa-lg text-primary"></i>
                        @break
                    @case('ReviewLeft')
                        <i class="fa-solid fa-star fa-lg text-warning"></i>
                        @break
                    @case('AdminNotice')
                        <i class="fa-solid fa-shield fa-lg text-secondary"></i>
                        @break
                    @default
                        <i class="fa-solid fa-bell fa-lg text-muted"></i>
                @endswitch
            </div>
            <div>
                <p class="mb-1 {{ $notification->is_read ? 'text-muted' : '' }}">
                    {{ $notification->content }}
                </p>
                <small class="text-muted">
                    <i class="fa-regular fa-clock me-1"></i>{{ $notification->created_at->diffForHumans() }}
                </small>
            </div>
        </div>
        <div class="notification-actions d-flex gap-2">
            @if(!$notification->is_read)
                <form action="{{ route('notifications.read', $notification) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm text-primary" title="Mark as read">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </form>
            @endif
            <form action="{{ route('notifications.destroy', $notification) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm text-danger" title="Delete" onclick="return confirm('Delete this notification?')">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </form>
        </div>
    </div>
</div>
