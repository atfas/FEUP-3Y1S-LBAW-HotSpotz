<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    protected $table = 'conversations';
    
    public $timestamps = false;

    protected $fillable = [
        'guest_id',
        'host_id',
        'listing_id',
        'booking_id',
        'created_at',
        'concluded_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'concluded_at' => 'datetime',
    ];

    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_id');
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class, 'listing_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'conversation_id')->orderBy('send_at', 'asc');
    }

    public function unreadMessagesCount($userId): int
    {
        $isGuest = $this->guest_id === $userId;
        $author = $isGuest ? 'Host' : 'Guest';
        
        return $this->messages()
            ->where('author', $author)
            ->where('is_read', false)
            ->count();
    }
}

