<?php

namespace App\Events;

use App\Models\Message;
use App\Models\Conversation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $conversation;

    public function __construct(Message $message, Conversation $conversation)
    {
        $this->message = $message;
        $this->conversation = $conversation;
    }

    public function broadcastOn()
    {
        // Broadcast to both guest and host channels
        return [
            new PrivateChannel('user.' . $this->conversation->guest_id),
            new PrivateChannel('user.' . $this->conversation->host_id),
        ];
    }

    public function broadcastWith()
    {
        return [
            'message' => [
                'id' => $this->message->id,
                'author' => $this->message->author,
                'content' => $this->message->content,
                'send_at' => $this->message->send_at->toIso8601String(),
                'conversation_id' => $this->message->conversation_id,
            ],
            'conversation_id' => $this->conversation->id,
        ];
    }

    public function broadcastAs()
    {
        return 'message.sent';
    }
}

