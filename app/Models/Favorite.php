<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    const UPDATED_AT = null; // Only use created_at
    
    protected $fillable = [
        'user_id',
        'listing_id'
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Get the user that owns this favorite
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the listing that was favorited
     */
    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }
}