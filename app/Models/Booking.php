<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $table = 'bookings';
    
    public $timestamps = false;
    
    protected $fillable = [
        'check_in_date',
        'check_out_date',
        'num_guests',
        'status',
        'total_price',
        'availability_id',
        'guest_id',
    ];
    
    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'created_at' => 'datetime',
        'total_price' => 'decimal:2',
    ];
    
    /**
     * Get the availability this booking is for.
     */
    public function availability(): BelongsTo
    {
        return $this->belongsTo(Availability::class);
    }
    
    /**
     * Get the user who made this booking.
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_id');
    }

    
    public function review()
    {
        return $this->hasOne(Review::class);
    }

    public function canBeReviewed()
    {
        return $this->status === 'Completed' 
            && !$this->review 
            && $this->check_out_date < now();
    }

    public function listing()
{
    return $this->belongsTo(Listing::class);
}
}
