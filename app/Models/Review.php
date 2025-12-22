<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    const UPDATED_AT = null; // Only use created_at
    
    protected $fillable = [
        'rating',
        'comment',
        'answer_content',
        'answered_at',
        'guest_id',
        'host_id',
        'booking_id'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'answered_at' => 'datetime',
    ];

    public function guest()
    {
        return $this->belongsTo(User::class, 'guest_id');
    }

    public function host()
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}