<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use App\Models\Message;

class MessageReacted implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function broadcastOn()
    {
        return new Channel('chat.' . (string)$this->message->channel_id);
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->message->id,
            'channel_id' => $this->message->channel_id,
            'reactions' => $this->message->reactions,
        ];
    }
}
