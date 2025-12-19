<?php

namespace App\Events;

use App\Models\Notification;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Notification $notification;

    /**
     * Create a new event instance.
     */
    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }

    /**
     * Get the channels the event should broadcast on.
     * 
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->notification->user_id),
        ];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'notification' => [
                'id' => $this->notification->id,
                'type' => $this->notification->type,
                'content' => $this->notification->content,
                'created_at' => $this->notification->created_at->diffForHumans(),
                'is_read' => $this->notification->is_read,
                'booking_id' => $this->notification->booking_id,
            ],
        ];
    }

    /**
     * The event's broadcast name.
     * Dynamically generates based on notification type.
     */
    public function broadcastAs(): string
    {
        // Maps notification types to broadcast event names
        $eventMap = [
            'BookingConfirmed' => 'booking.confirmed',
            'BookingCanceled' => 'booking.cancelled',
            'NewBookingForHost' => 'booking.new',
            'PaymentReceived' => 'payment.received',
            'MessageReceived' => 'message.received',
            'ReviewLeft' => 'review.left',
            'AdminNotice' => 'admin.notice',
        ];

        return $eventMap[$this->notification->type] ?? 'notification.created';
    }
}
