<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Message;
use App\Models\Channel;
use Illuminate\Support\Facades\Auth;

class ChatBell extends Component
{
    public $conversations = [];
    public $isOpen = false;

    public function mount()
    {
        $this->loadChats();
    }

    protected function currentUser()
    {
        return Auth::guard('web')->user() ?? Auth::guard('staff')->user();
    }

    public function loadChats()
    {
        $user = $this->currentUser();
        if (!$user) return;

        $userId = $user->id;
        $userType = get_class($user);

        // ✅ Get channels user belongs to
        if ($user instanceof \App\Models\User) {
            $channels = Channel::where(function ($q) use ($userId) {
                $q->where('type', 'public')
                    ->orWhereHas('adminUsers', fn($q) => $q->where('user_id', $userId));
            })->get();
        } else {
            $channels = Channel::where(function ($q) use ($userId) {
                $q->where('type', 'public')
                    ->orWhereHas('staffUsers', fn($q) => $q->where('user_id', $userId));
            })->get();
        }

        $data = [];

        foreach ($channels as $channel) {

            // ✅ unread count (only messages NOT sent by me)
            $unread = Message::where('channel_id', $channel->id)
                ->where('is_read', false)
                ->whereNot(function ($q) use ($userId, $userType) {
                    $q->where('sender_id', $userId)
                        ->where('sender_type', $userType);
                })
                ->count();

            if ($unread === 0) continue;

            // ✅ Get display name
            if ($channel->type === 'private') {

                $other = $channel->adminUsers->firstWhere('id', '!=', $userId)
                    ?? $channel->staffUsers->firstWhere('id', '!=', $userId);

                $name = $other?->staff_name ?? $other?->name ?? 'Private Chat';

            } else {
                $name = $channel->name;
            }

            // ✅ Last message
            $lastMessage = Message::where('channel_id', $channel->id)
                ->with('sender')
                ->latest()
                ->first();

            $formattedMessage = '';

            if ($lastMessage) {

                // ✅ sender name
                $senderName = 'Someone';

                if ($lastMessage->sender_type === \App\Models\Staff::class) {
                    $senderName = $lastMessage->sender->staff_name ?? 'Staff';
                } elseif ($lastMessage->sender_type === \App\Models\User::class) {
                    $senderName = $lastMessage->sender->name ?? 'Admin';
                }

                // ✅ media label
                $mediaText = null;

                if ($lastMessage->type === 'image') {
                    $mediaText = "$senderName sent a photo";
                } elseif ($lastMessage->type === 'video') {
                    $mediaText = "$senderName sent a video";
                } elseif ($lastMessage->type === 'file') {
                    $mediaText = "$senderName sent a file";
                }

                // ✅ combine text + media
                if (!empty($lastMessage->message) && $mediaText) {
                    $formattedMessage = $lastMessage->message . "\n" . $mediaText;
                } elseif ($mediaText) {
                    $formattedMessage = $mediaText;
                } else {
                    $formattedMessage = $lastMessage->message;
                }
            }

            $data[] = [
                'channel_id' => $channel->id,
                'name' => $name,
                'last_message' => $formattedMessage,
                'last_message_time' => $lastMessage?->created_at,
                'unread' => $unread,
            ];
        }

        // ✅ Sort latest first
        usort($data, function ($a, $b) {
            return strtotime($b['last_message_time'] ?? 0)
                <=> strtotime($a['last_message_time'] ?? 0);
        });

        $this->conversations = $data;
    }

    public function toggleDropdown()
    {
        $this->isOpen = !$this->isOpen;

        if ($this->isOpen) {
            $this->loadChats();
        }
    }

    public function goToChat($channelId)
    {
        return redirect()->to('/chat?channel=' . $channelId);
    }

    public function getTotalUnreadProperty()
    {
        return collect($this->conversations)->sum('unread');
    }

    public function render()
    {
        return view('livewire.chat-bell');
    }
}
