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
    public $deleteModal = false;
    public $deleteType = null; // 'channel' or 'staff'
    public $deleteId = null;

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
        $this->channels = Channel::where('type','public')->get();
        $this->viewChannelId = $this->selectedChannelId;
        $this->refreshChannelStaff();

    }

    public function toggleChannelStaff($channelId)
    {
        if ($this->viewChannelId === $channelId) {
            $this->viewChannelId = null;
            $this->channelStaffList = [];
            return;
        }

        $this->viewChannelId = $channelId;
        $this->refreshChannelStaff();
    }
    public function refreshChannelStaff()
    {
        if (!$this->viewChannelId) return;

        $channel = Channel::find($this->viewChannelId);
        $this->channelStaffList = $channel->users()->get();
    }

 //   public function removeStaff($staffId)
   // {
     //   $channel = Channel::find($this->viewChannelId);
       // $channel->users()->detach($staffId);

      //  $this->refreshChannelStaff();

        //$this->alertMessage = "Staff removed from channel";
    //}

   // public function deleteChannel($channelId)
   // {
     //   $channel = Channel::where('type','public')->find($channelId);
       // if ($channel) {
         //   $channel->delete();
       // }

       // $this->channels = Channel::where('type','public')->get();
       // $this->viewChannelId = null;

     //   $this->alertMessage = "Channel deleted";
   // }


    public function confirmDeleteChannel($channelId)
    {
        $this->deleteType = 'channel';
        $this->deleteId = $channelId;
        $this->deleteModal = true;
    }

    public function confirmRemoveStaff($staffId)
    {
        $this->deleteType = 'staff';
        $this->deleteId = $staffId;
        $this->deleteModal = true;
    }

    public function deleteConfirmed()
    {
        if ($this->deleteType === 'channel') {
            $channel = Channel::where('type','public')->find($this->deleteId);
            if ($channel) $channel->delete();

            $this->channels = Channel::where('type','public')->get();
            $this->viewChannelId = null;
            $this->alertMessage = "Channel deleted";
        }

        if ($this->deleteType === 'staff') {
            $channel = Channel::find($this->viewChannelId);
            $channel->users()->detach($this->deleteId);

            $this->refreshChannelStaff();
            $this->alertMessage = "Staff removed from channel";
        }

        $this->deleteModal = false;
        $this->deleteId = null;
        $this->deleteType = null;
    }
    public function isChannelOpen($channelId)
    {
        return $this->viewChannelId === $channelId;
    }

    public function render()
    {
        return view('livewire.chat-management');
    }
}
