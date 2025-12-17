<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    public $timestamps = false;

    protected $table = 'premium_subscriptions';

    protected $fillable = [
        'user_id',
        'member_status',
        'tier',
        'plan_type',
        'price_paid',
        'discount_percentage',
        'member_since',
        'next_payment_date',
        'cancelled_at',
        'total_savings',
    ];

    protected function casts(): array
    {
        return [
            'price_paid' => 'decimal:2',
            'discount_percentage' => 'integer',
            'member_since' => 'datetime',
            'next_payment_date' => 'date',
            'cancelled_at' => 'datetime',
            'total_savings' => 'decimal:2',
        ];
    }

    /**
     * Get the user that owns the subscription.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if subscription is active.
     */
    public function isActive(): bool
    {
        return $this->member_status === 'Active';
    }

    /**
     * Get the tier badge color class.
     */
    public function getTierColorClass(): string
    {
        return match($this->tier) {
            'Silver' => 'bg-secondary',
            'Gold' => 'bg-warning text-dark',
            'Platinum' => 'bg-light text-dark',
            default => 'bg-secondary',
        };
    }

    /**
     * Get the tier icon class.
     */
    public function getTierIcon(): string
    {
        return match($this->tier) {
            'Silver' => 'fa-solid fa-medal',
            'Gold' => 'fa-solid fa-crown',
            'Platinum' => 'fa-solid fa-gem',
            default => 'fa-solid fa-medal',
        };
    }
}
