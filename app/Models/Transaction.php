<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Booking;

class Transaction extends Model
{
    protected $table = 'transactions';

    protected $fillable = [
        'amount',
        'transaction_method',
        'type',
        'status',
        'paid_at',
        'refund_id',
        'booking_id',
    ];

    public $timestamps = false;

    protected $dates = ['paid_at'];

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function refund()
    {
        return $this->belongsTo(self::class, 'refund_id');
    }
}
