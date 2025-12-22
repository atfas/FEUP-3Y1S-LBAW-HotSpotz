<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListingPhoto extends Model
{
    protected $table = 'listing_photos';
    public $timestamps = false;

    protected $fillable = [
        'listing_id',
        'url',
    ];
}
