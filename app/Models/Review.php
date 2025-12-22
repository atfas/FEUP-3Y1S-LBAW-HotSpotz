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
        'booking_id',
        'reported',
        'report_reason',
        'reported_at',
        'reported_by',
        'report_decision',
        'reviewed_by',
        'reviewed_at'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'answered_at' => 'datetime',
        'reported_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'reported' => 'boolean',
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

    // NEW: Add these relationships
    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // NEW: Add these scope methods
    public function scopeReported($query)
    {
        return $query->where('reported', true);
    }

    public function scopePendingReports($query)
    {
        return $query->where('reported', true)
                    ->where(function($q) {
                        $q->whereNull('report_decision')
                          ->orWhere('report_decision', 'pending');
                    });
    }
}