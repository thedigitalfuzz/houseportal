<div class="flex h-full">

    <div class="w-3/4 p-2 flex flex-col">
        @if($deletedMessageAlert)
            <div id="deletedMessageAlert" class="bg-red-500 text-white p-2 rounded mb-2 flex justify-between items-center">

                <span>{{ $deletedMessageAlert }}</span>

                <!-- Close button -->
                <button onclick="document.getElementById('deletedMessageAlert').style.display='none'"
                        class="ml-4 text-white font-bold text-lg leading-none">
                    Close
                </button>

            </div>
        @endif
        <div class="flex-1 overflow-y-auto border p-2 mb-2">
            @foreach($messages as $msg)
                <div class="message relative p-1" wire:key="msg-{{ $msg->id }}">
                    <div class="flex justify-between">
                        <div class="flex gap-1">
                            <strong>
                                @if($msg->sender_type === \App\Models\Staff::class)
                                    {{ $msg->sender->staff_name ?? 'Unknown Staff' }}
                                @elseif($msg->sender_type === \App\Models\User::class)
                                    Administrator
                                @else
                                    Unknown
                                @endif
                            </strong>: {{ $msg->message }}
                           @if(isset($deletedMessages[$msg->id]))
                                <div class="mt-1 text-red-600 italic"></div>
                            @endif
                        </div>



                        <!-- React button -->
                        <button wire:click="toggleReactionBar({{ $msg->id }})"
                                @if($msg->message === 'This message has been deleted by the sender') style="display:none;" @endif
                                class="ml-2 bg-gray-200 px-1 rounded">👍
                        </button>
                    </div>


                    @if(isset($activeReactionMessage) && $activeReactionMessage == $msg->id && $msg->message !== 'This message has been deleted by the sender')
                        <div class="reaction-bar flex gap-1 mt-1">
                            @php
                                $userId = Auth::guard('staff')->check() ? Auth::guard('staff')->id() : 0;
                                $userReactions = json_decode($msg->reactions, true) ?? [];
                            @endphp
                            @foreach(['👍','❤️','😂', '👎'] as $emoji)
                                @php
                                    $highlight = isset($userReactions[$emoji]) && in_array($userId, $userReactions[$emoji]) ? 'bg-blue-200 rounded px-1' : '';
                                @endphp
                                <button wire:click="react({{ $msg->id }}, '{{ $emoji }}')" class="{{ $highlight }}">
                                    {{ $emoji }}
                                </button>
                            @endforeach
                            @if($msg->sender_id === $userId)
                                <button wire:click="deleteMessage({{ $msg->id }})" class="text-red-600 ml-2">🗑</button>
                            @endif
                        </div>
                    @endif

                    <!-- Display stored reactions below message -->
                    @if($msg->reactions && $msg->message !== 'This message has been deleted by the sender')
                        <div class="mt-1 text-sm text-gray-500">
                            @php
                                $reactions = json_decode($msg->reactions, true);
                            @endphp
                            @foreach($reactions as $emoji => $users)
                                <span>{{ $emoji }} {{ count($users) }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach

        </div>
        <div class="flex">
            <input type="text" wire:model.defer="newMessage" placeholder="Type a message..."
                   class="border rounded flex-1 px-2 py-1"/>
            <button wire:click="sendMessage" class="bg-blue-600 text-white px-4 py-1 ml-2 rounded">Send</button>
        </div>
    </div>
    <div class="w-1/4 border-r p-2">
        <h3 class="font-bold mb-2">Channels</h3>
        <!-- Channel Creation (Admin Only) -->
        @php
            $isAdmin = auth()->check(); // admin only
        $isStaff = auth()->guard('staff')->check(); // staff
        @endphp

        @if($isAdmin)
            <div class="flex items-center gap-2 mb-2">
                <input type="text" wire:model.defer="newChannelName" placeholder="New channel name"
                       class="border rounded px-2 py-1 w-full"/>
                <button wire:click="createChannel" class="bg-green-600 text-white px-2 py-1 mt-1 rounded w-full">Create</button>
            </div>
        @endif

        <ul>
            @foreach($channels as $channel)
                <li class="cursor-pointer flex justify-between p-1 hover:bg-gray-200 {{ $selectedChannel == $channel->id ? 'bg-gray-300 font-bold' : '' }}"
                    wire:click="selectChannel({{ $channel->id }})">
                    #{{ $channel->name }}

                    <!-- Delete icon only for admin -->
                    @if($isAdmin)
                        <button wire:click="deleteChannel({{ $channel->id }})"
                                onclick="confirm('Do you want to delete this channel and all messages?') || event.stopImmediatePropagation()"
                                class="text-red-600 ml-2">🗑</button>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
    <script>
        Livewire.on('hideReactionBar', data => {
            const bar = document.querySelector(`#reaction-bar-${data.messageId}`);
            if(bar) bar.style.display = 'none';
        });

        Livewire.on('hideDeletedMessageAlert', () => {
            setTimeout(() => {
                const alert = document.getElementById('deletedMessageAlert');
                if(alert) alert.style.display = 'none';
            }, 5000);
        });
    </script>
</div>
