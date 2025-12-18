<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $table = 'notifications';
    
    public $timestamps = false;
    
    protected $fillable = [
        'type',
        'content',
        'is_read',
        'user_id',
        'admin_id',
        'admin_notification_id',
        'booking_id',
        'transaction_id',
        'message_id',
        'review_id',
    ];
    
    protected $casts = [
        'created_at' => 'datetime',
        'is_read' => 'boolean',
    ];
    
    /**
     * Notification type constants matching database CHECK constraint.
     */
    const TYPE_BOOKING_CONFIRMED = 'BookingConfirmed';
    const TYPE_BOOKING_CANCELED = 'BookingCanceled';
    const TYPE_PAYMENT_RECEIVED = 'PaymentReceived';
    const TYPE_REMAINDER = 'Remainder';
    const TYPE_MESSAGE_RECEIVED = 'MessageReceived';
    const TYPE_REVIEW_LEFT = 'ReviewLeft';
    const TYPE_ADMIN_NOTICE = 'AdminNotice';
    
    /**
     * Get the user who receives this notification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the admin who sent this notification (if applicable).
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
    
    /**
     * Get the booking associated with this notification.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
    
    /**
     * Get the transaction associated with this notification.
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
    
    /**
     * Get the message associated with this notification.
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }
    
    /**
     * Get the review associated with this notification.
     */
    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }
    
    /**
     * Scope to get unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }
    
    /**
     * Mark this notification as read.
     */
    public function markAsRead(): bool
    {
        return $this->update(['is_read' => true]);
    }
}
