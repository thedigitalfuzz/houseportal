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
                ->latest()
                ->first();

            $data[] = [
                'channel_id' => $channel->id,
                'name' => $name,
                'last_message' => $lastMessage?->message,
                'unread' => $unread,
            ];
        }

        // ✅ Sort latest first
        usort($data, function ($a, $b) {
            return strcmp($b['last_message'] ?? '', $a['last_message'] ?? '');
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
