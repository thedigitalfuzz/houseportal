<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel; // use PresenceChannel for online users
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TypingEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $channelId;
    public $senderName;

    public function __construct($channelId, $senderName)
    {
        $this->channelId = $channelId;
        $this->senderName = $senderName;
    }

    public function broadcastOn()
    {
        return new Channel("chat.$this->channelId");
    }

    public function broadcastWith()
    {
        return [
            'senderName' => $this->senderName
        ];
    }
    public function broadcastAs()
    {
        return 'Typing';
    }

}
