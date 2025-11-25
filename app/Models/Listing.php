<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Listing extends Model
{
    protected $table = 'listings';
    
    public $timestamps = false;
    
    protected $fillable = [
        'title',
        'description',
        'max_occupancy',
        'location_address',
        'minimum_stay',
        'cancellation_policy',
        'host_id',
    ];

    /**
     * The attributes that should be cast to a specific type.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'is_active' => 'boolean',
            'average_rating' => 'decimal:2',
        ];
    }
    
    /**
     * Get the host (user) who owns this listing.
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    /**
     * Get all availability periods for this listing.
     */
    public function availabilities(): HasMany
    {
        return $this->hasMany(Availability::class);
    }
}
