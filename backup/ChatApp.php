<?php
namespace App\Livewire;

use Livewire\Component;
use App\Models\Channel;
use App\Models\Message;
use App\Models\User;
use App\Models\Staff;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;

class ChatApp extends Component
{
public $channels = [];
public $selectedChannel;
public $messages = [];
public $newMessage = '';
public $newChannelName = '';
    public $activeReactionMessage = null; // currently active message for reaction bar

    public $deletedMessageAlert = null; // holds alert
    public $deletedMessages = [];

    protected $listeners = ['handleIncomingMessage', 'clearDeletedMessageAlert' => 'clearDeletedMessageAlert',];

public function handleIncomingMessage($payload)
    {
        if($payload['channel_id'] == $this->selectedChannel){
            $this->messages[] =(object) $payload;
        }
    }
    public function clearDeletedMessageAlert()
    {
        $this->deletedMessageAlert = null;
    }

public function mount()
{
$this->channels = Channel::all();
$this->selectedChannel = $this->channels->first()?->id;
$this->loadMessages();
}

public function loadMessages()
{
$this->messages = Message::where('channel_id', $this->selectedChannel)
->with('sender')
->orderBy('created_at','asc')
->get();
}

    public function react($messageId, $emoji)
    {
        $message = Message::find($messageId);
        if (!$message) return;

        $reactions = json_decode($message->reactions, true) ?? [];

        $userId = Auth::guard('staff')->check() ? Auth::guard('staff')->id() : 0;

        // Remove this user from all emojis first
        foreach ($reactions as $key => $users) {
            if (($key !== $emoji) && in_array($userId, $users)) {
                $reactions[$key] = array_filter($users, fn($id) => $id != $userId);
                if (empty($reactions[$key])) unset($reactions[$key]);
            }
        }

        // Toggle reaction
        if (isset($reactions[$emoji]) && in_array($userId, $reactions[$emoji])) {
            // Remove the reaction
            $reactions[$emoji] = array_filter($reactions[$emoji], fn($id) => $id != $userId);
            if (empty($reactions[$emoji])) unset($reactions[$emoji]);
        } else {
            // Add reaction
            $reactions[$emoji][] = $userId;
        }

        $message->reactions = json_encode($reactions);
        $message->save();

        // Hide reaction bar after reacting
        $this->activeReactionMessage = null;

        // Mark message deleted locally
        $this->loadMessages();
    }

    public function toggleReactionBar($messageId)
    {
        // toggle reaction bar
        if ($this->activeReactionMessage === $messageId) {
            $this->activeReactionMessage = null;
        } else {
            $this->activeReactionMessage = $messageId;
        }
    }
public function selectChannel($channelId)
{
    if(Auth::guard('staff')->check()){
        $staffId = Auth::guard('staff')->id();
        $allowed = \App\Models\Channel::find($channelId)
            ->users()->where('user_id', $staffId)->exists();
        if(!$allowed) return; // staff cannot switch
    }
$this->selectedChannel = $channelId;
$this->loadMessages();
}

    public function sendMessage()
    {
        if (trim($this->newMessage) === '') return;

        // Determine who is sending
        if (Auth::guard('staff')->check()) {
            $authUser = Auth::guard('staff')->user();
            $senderType = \App\Models\Staff::class;
            $senderId = $authUser->id; // keep staff's real ID
        } else {
            $authUser = Auth::user(); // admin
            $senderType = \App\Models\User::class;
            $senderId = 0; // admin always id=0
        }

        $message = Message::create([
            'channel_id' => $this->selectedChannel,
            'sender_id' => $senderId,
            'sender_type' => $senderType,
            'message' => $this->newMessage,
            'type' => 'text',
        ]);

        broadcast(new \App\Events\MessageSent($message))->toOthers();
        $this->newMessage = '';
        $this->loadMessages();
    }
    public function createChannel()
    {
        if (trim($this->newChannelName) === '') return;

        $channel = Channel::create([
            'name' => $this->newChannelName,
            'type' => 'public', // default
            'created_by' => Auth::guard('staff')->check() ? Auth::guard('staff')->id() : null, // admin=0
        ]);

        // assign creator to the channel
        if(Auth::guard('staff')->check()) {
            $channel->users()->attach(Auth::guard('staff')->id(), ['role' => 'admin']);
        }

        $this->channels->push($channel);
        $this->newChannelName = '';
    }
    public function deleteChannel($channelId)
    {
        $channel = \App\Models\Channel::find($channelId);

        if (!$channel) return;

        // Only admin can delete
        if (!auth()->check()) return;

        $channel->delete();

        $this->channels = \App\Models\Channel::all();

        // Reset selected channel
        $this->selectedChannel = $this->channels->first()?->id;

        $this->loadMessages();
    }
    public function deleteMessage($messageId)
    {
        $message = Message::find($messageId);
        if(!$message) return;

        // Only sender can "delete"
        $userId = Auth::guard('staff')->check() ? Auth::guard('staff')->id() : 0;
        if($message->sender_id !== $userId) return;

        $message->message = 'This message has been deleted by the sender';
        $message->reactions = json_encode([]);
        $message->save();


        // Mark message deleted locally
        $this->deletedMessages[$messageId] = true;

        // 4. Close reaction bar
        if ($this->activeReactionMessage === $messageId) {
            $this->activeReactionMessage = null;
        }
// Show alert immediately
        $this->deletedMessageAlert = "Message deleted successfully";

        // Hide alert automatically after 5 seconds
        $this->dispatch('hideDeletedMessageAlert', ['timeout' => 5000]);
        // Show alert immediately
        $this->loadMessages();

    }
public function render()
{
    return view('livewire.chat-app');
}
}
