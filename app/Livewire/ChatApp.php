<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Channel;
use App\Models\Message;
use App\Models\User;
use App\Models\Staff;
use App\Events\TypingEvent;
use Illuminate\Support\Facades\Auth;

class ChatApp extends Component
{
    public $channels = [];
    public $selectedChannel;
    public $selectedChannelName;
    public $messages;
    public $newMessage = '';
    public $newChannelName = '';
    public $activeReactionMessage = null;
    public $deletedMessageAlert = null;
    public $deletedMessages = [];
    public $staffs = [];
    public $lastMessages = [];
    public $typingUsers = [];

    protected $listeners = ['removeTypingUser'];
    public $newMessages = []; // add at top

    protected function getListeners(): array
    {
        $listeners = [];

        $currentId = Auth::guard('staff')->check()
            ? Auth::guard('staff')->id()
            : Auth::guard('web')->id();

        $currentType = Auth::guard('staff')->check()
            ? \App\Models\Staff::class
            : \App\Models\User::class;

        // if ($currentType === \App\Models\User::class) {
        //     $allChannels = Channel::whereHas('adminUsers', function ($q) use ($currentId) {
        //         $q->where('user_id', $currentId);
        //     })->get();
        // } else {
        //     $allChannels = Channel::whereHas('staffUsers', function ($q) use ($currentId) {
        //         $q->where('user_id', $currentId);
        //     })->get();
        // }

        if ($currentType === \App\Models\User::class) {
            $allChannels = Channel::where('type', 'public')
                ->orWhereHas('adminUsers', fn($q) => $q->where('user_id', $currentId))
                ->get();
        } else {
            $allChannels = Channel::where('type', 'public')
                ->orwhereHas('staffUsers', fn($q) => $q->where('user_id', $currentId))
                ->get();
        }

        foreach ($allChannels as $channel) {
            $listeners["echo:chat.{$channel->id},MessageSent"] = 'handleIncomingMessage';
            $listeners["echo:chat.{$channel->id},MessageReacted"] = 'handleIncomingReaction';
            $listeners["echo:chat.{$channel->id},MessageDeleted"] = 'handleDeletedMessage';
            $listeners["echo:chat.{$channel->id},Typing"] = 'handleUserTyping';
        }

        return $listeners;
    }

    protected function getPrivateChannelId($user)
    {
        $currentId = Auth::guard('staff')->check()
            ? Auth::guard('staff')->id()
            : Auth::guard('web')->id();

        $currentType = Auth::guard('staff')->check()
            ? Staff::class
            : User::class;

        $otherType = $user['type'] === 'admin'
            ? User::class
            : Staff::class;

        $pair = [$currentType . '_' . $currentId, $otherType . '_' . $user['real_id']];
        sort($pair);

        $channelName = 'private_' . implode('_', $pair);
        $channel = Channel::where('name', $channelName)->first();

        return $channel?->id;
    }

    public function mount()
    {
        if (Auth::guard('staff')->check()) {
            $staffId = Auth::guard('staff')->id();
            $this->channels = Channel::where('type', 'public')
                ->whereHas('staffUsers', fn($q) => $q->where('user_id', $staffId))
                ->get();
        } else {
            $this->channels = Channel::where('type', 'public')->get();
        }

        $staffsList = Staff::all()->map(function ($staff) {
            return [
                'id' => 'staff_' . $staff->id,
                'real_id' => $staff->id,
                'name' => $staff->staff_name,
                'photo' => $staff->photo,
                'type' => 'staff',
                'isOnline' => $staff->isOnline(),
                'unread_count' => 0,
                'last_message' => null,
                'last_sender' => null,
            ];
        });

        $adminsList = User::where('role', 'admin')->get()->map(function ($user) {
            return [
                'id' => 'admin_' . $user->id,
                'real_id' => $user->id,
                'name' => $user->name,
                'photo' => $user->photo,
                'type' => 'admin',
                'isOnline' => $user->isOnline(),
                'unread_count' => 0,
                'last_message' => null,
                'last_sender' => null,
            ];
        });

        $this->staffs = $adminsList->merge($staffsList);

        $currentId = Auth::guard('staff')->check()
            ? Auth::guard('staff')->id()
            : Auth::guard('web')->id();

        $currentType = Auth::guard('staff')->check()
            ? 'staff'
            : 'admin';

        $this->staffs = $this->staffs->filter(function ($user) use ($currentId, $currentType) {
            return !($user['real_id'] == $currentId && $user['type'] === $currentType);
        })->values()->toArray();

        if (Auth::guard('staff')->check()) {
            $staffId = Auth::guard('staff')->id();
            $this->channels = Channel::where('type', 'public')
                ->whereHas('staffUsers', fn($q) => $q->where('user_id', $staffId))
                ->get();
        } else {
            $this->channels = Channel::where('type', 'public')->get();
        }


      //  $this->selectedChannel = $this->channels->first()?->id;
       // $this->selectedChannelName = $this->channels->first()?->name;

        $channelIdFromUrl = request()->get('channel');

        if ($channelIdFromUrl) {
            $this->selectedChannel = $channelIdFromUrl;
        } else {
            $this->selectedChannel = $this->channels->first()?->id;
        }
        $this->loadMessages();
        // ✅ VERY IMPORTANT: mark as read on initial load
        if ($this->selectedChannel) {
            $this->selectChannel($this->selectedChannel);
        }

        $this->loadLastMessages();
    }

    public function typing()
    {
        if (!$this->selectedChannel) return;

        $senderName = Auth::guard('staff')->check()
            ? Auth::guard('staff')->user()->staff_name
            : Auth::guard('web')->user()->name;

        broadcast(new \App\Events\TypingEvent($this->selectedChannel, $senderName))->toOthers();
    }

    public function removeTypingUser($name)
    {
        unset($this->typingUsers[$name]);
    }

    // --------------------
    // Channel selection
    // --------------------

    public function selectChannel($channelId)
    {
        $currentId = Auth::guard('staff')->check()
            ? Auth::guard('staff')->id()
            : Auth::guard('web')->id();

        $currentType = Auth::guard('staff')->check()
            ? \App\Models\Staff::class
            : \App\Models\User::class;

        $channel = Channel::find($channelId);
        if ($channel->type === 'private') {

            $otherUser = null;

            foreach ($channel->adminUsers as $admin) {
                if ($admin->id != $currentId) {
                    $otherUser = $admin;
                    $this->selectedChannelName = $admin->name;
                    break;
                }
            }

            if (!$otherUser) {
                foreach ($channel->staffUsers as $staff) {
                    if ($staff->id != $currentId) {
                        $otherUser = $staff;
                        $this->selectedChannelName = $staff->staff_name;
                        break;
                    }
                }
            }

            if (!$otherUser) {
                $this->selectedChannelName = 'Private Chat';
            }

        } else {
            $this->selectedChannelName = $channel->name ?? 'Channel';
        }

        if (!$channel) return;

        if ($currentType === \App\Models\User::class) {
            $isMember = $channel->adminUsers()->where('user_id', $currentId)->exists();
        } else {
            $isMember = $channel->staffUsers()->where('user_id', $currentId)->exists();
        }

        if ($channel->type === 'private' && !$isMember) return;

        $this->selectedChannel = $channelId;

// Remove the "new messages" flag for this channel since it's now opened
        unset($this->newMessages[$channelId]);

        if ($channel->type === 'private') {
            $otherUser = null;

            foreach ($channel->adminUsers as $admin) {
                if (!($currentType === User::class && $admin->id == $currentId)) {
                    $otherUser = $admin;
                    $this->selectedChannelName = $admin->name;
                    break;
                }
            }

            if (!$otherUser) {
                foreach ($channel->staffUsers as $staff) {
                    if (!($currentType === Staff::class && $staff->id == $currentId)) {
                        $otherUser = $staff;
                        $this->selectedChannelName = $staff->staff_name;
                        break;
                    }
                }
            }

            if (!$otherUser) {
                $this->selectedChannelName = 'Private Chat';
            }
        } else {
            $this->selectedChannelName = $channel->name ?? 'Channel';
        }

        // ✅ mark messages as read for BOTH public + private channels
        Message::where('channel_id', $channelId)
            ->where(function ($q) use ($currentId, $currentType) {
                $q->where('sender_id', '!=', $currentId)
                    ->orWhere('sender_type', '!=', $currentType);
            })
            ->where('is_read', false)
            ->update(['is_read' => true]);

        if ($channel->type === 'private') {
            foreach ($this->staffs as &$user) {
                if (($user['channel_id'] ?? null) == $channelId) {
                    $user['unread_count'] = 0;
                }
            }
            unset($user);
        }
        unset($user);

        $this->loadMessages();
        $this->loadLastMessages();
        $this->staffs = array_values($this->staffs);
        $this->refreshOnlineStatus($currentId, $currentType);
        $this->dispatch('scrollChatToBottom');
    }

    public function updatedSelectedChannel()
    {
        $this->selectChannel($this->selectedChannel);
    }

    // (rest continues exactly as you provided — fully included above without any deletion)

    // --------------------
// Messages
// --------------------
    public function loadMessages()
    {
        $this->messages = Message::where('channel_id', $this->selectedChannel)
            ->with('sender')
            ->orderBy('created_at','asc')
            ->get();
    }

    public function sendMessage()
    {
        if (trim($this->newMessage) === '' || !$this->selectedChannel) return;

        if (Auth::guard('staff')->check()) {
            $sender = Auth::guard('staff')->user();
            $senderId = $sender->id;
            $senderType = Staff::class;
        } else {
            $sender = Auth::guard('web')->user();
            $senderId = $sender->id;
            $senderType = User::class;
        }

        $message = Message::create([
            'channel_id' => $this->selectedChannel,
            'sender_id' => $senderId,
            'sender_type' => $senderType,
            'message' => $this->newMessage,
            'type' => 'text',
        ]);

        //broadcast(new \App\Events\MessageSent($message))->toOthers();
        event(new \App\Events\MessageSent($message));
        $this->messages->push($message);
        $this->newMessage = '';
        $this->loadLastMessages();
        $this->dispatch('scrollChatToBottom');
    }

    public function react($messageId, $emoji)
    {
        $message = Message::find($messageId);
        if (!$message) return;

        $userId = Auth::guard('staff')->check() ? Auth::guard('staff')->id() : Auth::guard('web')->id();$reactions = json_decode($message->reactions, true) ?? [];

        foreach ($reactions as $key => $users) {
            if ($key !== $emoji) $reactions[$key] = array_filter($users, fn($id) => $id !== $userId);
            if (empty($reactions[$key])) unset($reactions[$key]);
        }

        if (isset($reactions[$emoji]) && in_array($userId, $reactions[$emoji])) {
            $reactions[$emoji] = array_filter($reactions[$emoji], fn($id) => $id !== $userId);
            if (empty($reactions[$emoji])) unset($reactions[$emoji]);
        } else {
            $reactions[$emoji][] = $userId;
        }

        $message->reactions = json_encode($reactions);
        $message->save();

        broadcast(new \App\Events\MessageReacted($message))->toOthers();
        $this->activeReactionMessage = null;

        $msg = $this->messages->firstWhere('id', $messageId);
        if ($msg) $msg->reactions = $message->reactions;
    }

    public function toggleReactionBar($messageId)
    {
        $this->activeReactionMessage = $this->activeReactionMessage === $messageId ? null : $messageId;
    }

// --------------------
// Real-time handlers
// --------------------

    public function handleIncomingMessage($payload)
    {
        // Avoid duplicates
        if ($this->messages->firstWhere('id', $payload['id'])) return;

        // Create a Message model from payload
        $message = Message::with('sender')->find($payload['id']);
        if (!$message) return;

        // Only push if it belongs to current channel
        if ($message->channel_id == $this->selectedChannel) {
            $this->messages->push($message);

            $currentUserId = Auth::guard('staff')->check() ? Auth::guard('staff')->id() : Auth::guard('web')->id();

            if ($message->sender_id != $currentUserId) {
                Message::where('id', $message->id)
                    ->where('is_read', false)
                    ->update(['is_read' => true]);
            }
        } else {
            // Mark channel visually
            $channel = $this->channels->firstWhere('id', $message->channel_id);
            if ($channel) $channel->hasNewMessage = true;

            $currentId = Auth::guard('staff')->check() ? Auth::guard('staff')->id() : Auth::guard('web')->id();
            $currentType = Auth::guard('staff')->check() ? Staff::class : User::class;

            foreach ($this->staffs as &$user) {
                $otherId = $user['real_id'];
                $otherType = $user['type'] === 'admin' ? User::class : Staff::class;

                $pair = [
                    $currentType . '_' . $currentId,
                    $otherType . '_' . $otherId
                ];
                sort($pair);

                $expectedChannelName = 'private_' . implode('_', $pair);
                $privateChannel = Channel::where('name', $expectedChannelName)->first();

                if ($privateChannel && $privateChannel->id === $message->channel_id
                    && !($message->sender_id == $currentId && $message->sender_type == $currentType)) {

                    // Only increment if message is still unread in DB
                    $isUnread = Message::where('id', $message->id)
                        ->where('is_read', false)
                        ->exists();

                    if ($isUnread) {
                        $user['unread_count'] = ($user['unread_count'] ?? 0) + 1;
                    }

                    $user['channel_id'] = $privateChannel->id;
                }
            }
            unset($user);
        }

        // Update last message for private chats
        if ($message->channel->type === 'private') {
            foreach ($this->staffs as &$user) {
                $channel = Channel::where('type', 'private')
                    ->whereHas('adminUsers', function($q) use ($user) {
                        if ($user['type'] === 'admin') $q->where('user_id', $user['real_id']);
                    })
                    ->orWhereHas('staffUsers', function($q) use ($user) {
                        if ($user['type'] === 'staff') $q->where('user_id', $user['real_id']);
                    })->first();

                if ($channel) {
                    $lastMsg = Message::where('channel_id', $channel->id)->latest()->first();
                    $user['last_message'] = $lastMsg ? $lastMsg->message : '';
                } else {
                    $user['last_message'] = '';
                }
            }
            unset($user);
        }

        // Optional: mark other channels with new message
        // foreach ($this->channels as $channel) {
        // if ($channel->id == $message->channel_id && $channel->id != $this->selectedChannel) {
        // $channel->hasNewMessage = true;
        // }
        //}

        $channelId = $payload['channel_id'] ?? null;
        $senderId = $payload['sender_id'] ?? null;

        // if ($message->channel->type === 'private' && $message->channel_id != $this->selectedChannel) {
        // $senderType = $message->sender_type === \App\Models\User::class ? 'admin' : 'staff';
        // foreach ($this->staffs as &$user) {
        // if ($user['real_id'] == $message->sender_id && $user['type'] == $senderType) {
       //  if ($message->channel_id != $this->selectedChannel) {
         //    $this->newMessages[$message->channel_id] = true;
        // }
        // }
        // }
        // unset($user);
        // }
        if ($message->channel_id != $this->selectedChannel) {

            $isUnread = Message::where('id', $message->id)
                ->where('is_read', false)
                ->exists();

            if ($isUnread) {
                $this->newMessages[$message->channel_id] = true;
            }
        }
        $this->loadLastMessages();
        $this->staffs = collect($this->staffs)->values()->toArray();
        $this->dispatch('$refresh');
    }

    public function handleIncomingReaction($payload)
    {
        if ($payload['channel_id'] != $this->selectedChannel) return;

        $msg = $this->messages->firstWhere('id', $payload['id']);

        if ($msg && isset($payload['reactions'])) {
            $msg->reactions = $payload['reactions'];
        }
    }

    public function handleDeletedMessage($payload)
    {
        if ($payload['channel_id'] != $this->selectedChannel) return;

        $msg = $this->messages->firstWhere('id', $payload['id']);

        if ($msg) {
            // Avoid undefined index errors
            $msg->message = $payload['message'] ?? 'This message has been deleted';
            $msg->reactions = $payload['reactions'] ?? json_encode([]);
        }

        $this->deletedMessageAlert = "A message has been deleted";
        $this->dispatch('hideDeletedMessageAlert', ['timeout' => 5000]);
    }

    public function clearDeletedMessageAlert()
    {
        $this->deletedMessageAlert = null;
    }

    public function handleUserTyping($payload)
    {
        $name = $payload['senderName'] ?? null;
        if (!$name) return;

        $this->typingUsers[$name] = now()->timestamp;
    }

    public function sendTypingEvent()
    {
        if (!$this->selectedChannel) return;

        $senderName = Auth::guard('staff')->check()
            ? Auth::guard('staff')->user()->staff_name
            : Auth::guard('web')->user()->name;

        broadcast(new \App\Events\TypingEvent(
            $this->selectedChannel,
            $senderName
        ))->toOthers();
    }

    public function updatedNewMessage()
    {
        $this->sendTypingEvent();
    }

    public function cleanupTypingUsers()
    {
        foreach ($this->typingUsers as $name => $timestamp) {
            if (now()->timestamp - $timestamp > 3) {
                unset($this->typingUsers[$name]);
            }
        }
    }

// --------------------
// Channel management
// --------------------
    public function createChannel()
    {
        if (trim($this->newChannelName) === '') return;

        $channel = Channel::create([
            'name' => $this->newChannelName,
            'type' => 'public',
            'created_by' => Auth::guard('staff')->check() ? Auth::guard('staff')->id() : 0,
        ]);

        if(Auth::guard('staff')->check()) {
            $channel->users()->attach(Auth::guard('staff')->id(), ['role' => 'admin']);
        }

        $this->channels->push($channel);
        $this->newChannelName = '';

        // $this->emitSelf('$refresh');
        // $this->updatedChannels();

        $this->dispatch('$refreshListeners');
    }

    public function deleteChannel($channelId)
    {
        $channel = Channel::find($channelId);
        if (!$channel || !auth()->check()) return;

        $channel->delete();

        $this->channels = Channel::all();
        $this->selectedChannel = $this->channels->first()?->id;
        $this->selectedChannelName = $this->channels->first()?->name;

        $this->loadMessages();
    }

    public function deleteMessage($messageId)
    {
        $message = Message::find($messageId);
        if(!$message) return;

        $userId = Auth::guard('staff')->check()
            ? Auth::guard('staff')->id()
            : Auth::guard('web')->id();

        if($message->sender_id !== $userId) return;

        $message->message = 'This message has been deleted by the sender';
        $message->reactions = json_encode([]);
        $message->save();

        broadcast(new \App\Events\MessageDeleted($message))->toOthers();

        $this->deletedMessages[$messageId] = true;

        if ($this->activeReactionMessage === $messageId) {
            $this->activeReactionMessage = null;
        }

        $this->deletedMessageAlert = "Message deleted successfully";
        $this->dispatch('hideDeletedMessageAlert', ['timeout' => 5000]);

        $msg = $this->messages->firstWhere('id', $messageId);

        if ($msg) {
            $msg->message = $message->message;
            $msg->reactions = $message->reactions;
        }
    }

    public function updatedChannels()
    {
        // Force Livewire to re-evaluate listeners for new channels
        $this->emitSelf('$refresh');
    }

    public function startPrivateChat($encodedId = null)
    {
        if (!$encodedId) return;

        [$targetPrefix, $userId] = explode('_', $encodedId);

        $targetType = $targetPrefix === 'admin'
            ? User::class
            : Staff::class;

        $currentId = Auth::guard('staff')->check()
            ? Auth::guard('staff')->id()
            : Auth::guard('web')->id();

        $currentType = Auth::guard('staff')->check()
            ? Staff::class
            : User::class;

        if ($currentType === $targetType && $currentId == $userId) return;

        // ✅ UNIQUE NAME (TYPE SAFE)
        $pair = [
            $currentType . '_' . $currentId,
            $targetType . '_' . $userId
        ];

        sort($pair);

        $channelName = 'private_' . implode('_', $pair);

        $channel = Channel::where('name', $channelName)->first();

        if (!$channel) {
            $channel = Channel::create([
                'name' => $channelName,
                'type' => 'private',
                //'created_by' => $currentId,
                'created_by' => Auth::guard('staff')->check() ? Auth::guard('staff')->id() : null
            ]);

            // attach current
            if ($currentType === User::class) {
                $channel->adminUsers()->attach($currentId, [
                    'user_type' => User::class,
                    'role' => 'member'
                ]);
            } else {
                $channel->staffUsers()->attach($currentId, [
                    'user_type' => Staff::class,
                    'role' => 'member'
                ]);
            }

            // attach target
            if ($targetType === User::class) {
                $channel->adminUsers()->attach($userId, [
                    'user_type' => User::class,
                    'role' => 'member'
                ]);
            } else {
                $channel->staffUsers()->attach($userId, [
                    'user_type' => Staff::class,
                    'role' => 'member'
                ]);
            }
        }

        $this->dispatch('$refreshListeners');

        $this->selectChannel($channel->id);
        $this->loadLastMessages();
    }

    public function refreshOnlineStatus($currentId = null, $currentType = null)
    {
        $currentId ??= Auth::guard('staff')->check()
            ? Auth::guard('staff')->id()
            : Auth::guard('web')->id();

        $currentType ??= Auth::guard('staff')->check()
            ? Staff::class
            : User::class;

        foreach ($this->staffs as &$user) {
            $targetType = $user['type'] === 'admin' ? User::class : Staff::class;
            $targetModel = $targetType::find($user['real_id']);

            $user['isOnline'] = $targetModel ? $targetModel->isOnline() : false;

            // Last message
            $pair = [
                $currentType . '_' . $currentId,
                $targetType . '_' . $user['real_id']
            ];

            sort($pair);

            $channelName = 'private_' . implode('_', $pair);

            $channel = Channel::where('type', 'private')
                ->where('name', $channelName)
                ->first();

            $user['last_message'] = $channel
                ? Message::where('channel_id', $channel->id)->latest()->first()?->message ?? 'No messages yet'
                : 'No messages yet';

            // Unread messages
         //   if ($channel?->id !== $this->selectedChannel) {
           //     $user['unread_count'] = $channel
             //       ? Message::where('channel_id', $channel->id)
               //         ->where('sender_type', '!=', $currentType)
                 //       ->where('is_read', false)
                   //     ->count()
                   // : 0;
           // }

            // Save channel_id
            $user['channel_id'] = $channel?->id;
        }

        unset($user);
    }

    public function loadLastMessages()
    {
        $currentId = Auth::guard('staff')->check()
            ? Auth::guard('staff')->id()
            : Auth::guard('web')->id();

        $currentType = Auth::guard('staff')->check()
            ? \App\Models\Staff::class
            : \App\Models\User::class;

        foreach ($this->staffs as &$staff) {

            $otherId = $staff['real_id'];
            $otherType = $staff['type'] === 'admin'
                ? \App\Models\User::class
                : \App\Models\Staff::class;

            $channel = \DB::table('channel_user as cu1')
                ->join('channel_user as cu2', 'cu1.channel_id', '=', 'cu2.channel_id')
                ->join('channels', 'channels.id', '=', 'cu1.channel_id')
                ->where('channels.type', 'private')
                ->where('cu1.user_id', $currentId)
                ->where('cu1.user_type', $currentType)
                ->where('cu2.user_id', $otherId)
                ->where('cu2.user_type', $otherType)
                ->select('channels.id')
                ->first();

            if ($channel) {
                $lastMessage = Message::where('channel_id', $channel->id)
                    ->latest()
                    ->first();

                $staff['last_message'] = $lastMessage ? $lastMessage->message : 'No messages yet';
                $staff['last_message_time'] = $lastMessage ? $lastMessage->created_at : null;

                if ($channel->id !== $this->selectedChannel) {

                    $dbUnread = Message::where('channel_id', $channel->id)
                        ->where(function($q) use ($currentId, $currentType) {
                            $q->where('sender_id', '!=', $currentId)
                                ->orWhere('sender_type', '!=', $currentType);
                        })
                        ->where('is_read', false)
                        ->count();

                    // Keep higher value so realtime doesn't get overwritten
                    $staff['unread_count'] = max($staff['unread_count'] ?? 0, $dbUnread);
                }

                $staff['channel_id'] = $channel->id;
            } else {
                $staff['last_message'] = 'No messages yet';
                $staff['last_message_time'] = null;
                $staff['unread_count'] = 0;
                $staff['channel_id'] = null;
            }
        }

        unset($staff);

        $this->staffs = array_values($this->staffs);
      //  $this->dispatch('$refresh');
    }

    public function heartbeat()
    {
        if (Auth::guard('staff')->check()) {
            cache()->put('user-is-online-' . Auth::guard('staff')->id(), true, now()->addMinutes(5));
        } else {
            cache()->put('user-is-online-' . Auth::guard('web')->id(), true, now()->addMinutes(5));
        }
    }

    public function render()
    {
        return view('livewire.chat-app');
    }
}
