<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $table = 'messages';
    
    public $timestamps = false;

    protected $fillable = [
        'author',
        'content',
        'send_at',
        'attachments',
        'is_read',
        'conversation_id',
    ];

    protected $casts = [
        'send_at' => 'datetime',
        'is_read' => 'boolean',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class, 'conversation_id');
    }
}

