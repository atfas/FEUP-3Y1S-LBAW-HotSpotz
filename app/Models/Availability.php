<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Availability extends Model
{
    protected $table = 'availability';
    
    public $timestamps = false;
    
    protected $fillable = [
        'start_date',
        'end_date',
        'price_per_night',
        'check_in_time',
        'check_out_time',
        'listing_id',
    ];
    
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'price_per_night' => 'decimal:2',
    ];
    
    /**
     * Get the listing this availability belongs to.
     */
    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }
    
    /**
     * Get all bookings for this availability.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
