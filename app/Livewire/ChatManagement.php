<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Channel;
use App\Models\Staff;

class ChatManagement extends Component
{
    public $channels;
    public $staffs;

    public $newChannelName;

    public $selectedStaffId;
    public $selectedChannelId;

    public $viewChannelId = null;
    public $channelStaffList = [];

    public $alertMessage = null;

    public function mount()
    {
        $this->channels = Channel::where('type', 'public')->get();
        $this->staffs = Staff::all();
    }

    public function createChannel()
    {
        if (!$this->newChannelName) return;

        $name = strtolower(trim($this->newChannelName));

        // Prevent duplicate
        if (Channel::where('name', $name)->exists()) {
            $this->alertMessage = "Channel already exists";
            return;
        }

        $channel = Channel::create([
            'name' => $name,
            'type' => 'public',
        ]);

        $adminId = auth()->id();

        $channel->users()->attach($adminId, [
            'user_type' => \App\Models\User::class,
            'role' => 'admin'
        ]);

        $this->channels = Channel::where('type','public')->get();
        $this->newChannelName = '';

        $this->alertMessage = "Channel created successfully";
    }

    public function assignStaffToChannel()
    {
        if (!$this->selectedStaffId || !$this->selectedChannelId) return;

        $channel = Channel::find($this->selectedChannelId);

        // Check if already exists
        if ($channel->users()->where('user_id', $this->selectedStaffId)->exists()) {
            $this->alertMessage = "Staff already exists in this channel";
            return;
        }

        $channel->users()->attach($this->selectedStaffId, [
            'user_type' => \App\Models\Staff::class,
            'role' => 'member'
        ]);

        $channelName = $channel->name;
        $this->alertMessage = "Staff added to {$channelName} channel successfully";

        $this->loadChannelStaff($this->selectedChannelId);
    }

    public function loadChannelStaff($channelId)
    {
        $this->viewChannelId = $channelId;

        $channel = Channel::find($channelId);
        $this->channelStaffList = $channel->users()->get();
    }

    public function removeStaff($staffId)
    {
        $channel = Channel::find($this->viewChannelId);
        $channel->users()->detach($staffId);

        $this->loadChannelStaff($this->viewChannelId);

        $this->alertMessage = "Staff removed from channel";
    }

    public function deleteChannel($channelId)
    {
        $channel = Channel::where('type','public')->find($channelId);
        if ($channel) {
            $channel->delete();
        }

        $this->channels = Channel::where('type','public')->get();
        $this->viewChannelId = null;

        $this->alertMessage = "Channel deleted";
    }

    public function render()
    {
        return view('livewire.chat-management');
    }
}
