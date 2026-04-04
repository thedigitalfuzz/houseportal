<div>
    <div wire:poll.30s="heartbeat"></div>
    <div wire:poll.5s="cleanupTypingUsers"></div>
    <div class="flex h-full">
        <div class="w-3/4 p-2 flex flex-col"> @if($deletedMessageAlert) <div id="deletedMessageAlert"
                                                                             class="bg-red-500 text-white p-2 rounded mb-2 flex justify-between items-center"> <span>{{
                    $deletedMessageAlert }}</span> <!-- Close button --> <button wire:click="clearDeletedMessageAlert"
                                                                                 class="ml-4 text-white font-bold text-lg leading-none"> X </button> </div> @endif <div
                class="flex-1 overflow-y-auto border mb-2">
                <!-- Selected Channel Title -->
                <div class="border-b-4 p-2 mb-2 flex justify-between items-center bg-yellow-100">
                    <h3 class="font-bold text-lg text-blue-600">#{{ $selectedChannelName }}</h3>
                </div> @php $lastDate = null; @endphp @foreach($messages as $msg) @php $msgDate =
                \Carbon\Carbon::parse($msg->created_at)->format('Y-m-d'); @endphp @if($lastDate != $msgDate) <div
                    class="text-center my-2"> <span class="bg-gray-300 text-xs px-2 py-1 rounded"> {{
                        \Carbon\Carbon::parse($msg->created_at)->format('F d, Y') }} </span> </div> @php $lastDate =
                $msgDate; @endphp @endif <div class="message relative p-2 " wire:key="msg-{{ $msg->id }}">
                    <div class="flex justify-between">
                        <div class="flex gap-1"> <strong> @if($msg->sender_type === \App\Models\Staff::class) {{
                                $msg->sender->staff_name ?? 'Unknown Staff' }} @elseif($msg->sender_type ===
                                \App\Models\User::class) Administrator @else Unknown @endif </strong>: {{ $msg->message
                            }} @if(isset($deletedMessages[$msg->id])) <div class="mt-1 text-red-600 italic"></div>
                            @endif </div> <!-- React button --> <button wire:click="toggleReactionBar({{ $msg->id }})"
                                                                        @if($msg->message === 'This message has been deleted by the sender') style="display:none;"
                                                                        @endif class="ml-2 bg-gray-200 px-1 rounded">👍 </button>
                    </div> @if(isset($activeReactionMessage) && $activeReactionMessage == $msg->id && $msg->message !==
                    'This message has been deleted by the sender') <div class="reaction-bar flex gap-1 mt-1"> @php
                            $userId = Auth::guard('staff')->check() ? Auth::guard('staff')->id() : Auth::guard('web')->id();
                            $userReactions = json_decode($msg->reactions, true) ?? []; @endphp @foreach(['👍','❤️','😂',
                        '👎'] as $emoji) @php $highlight = isset($userReactions[$emoji]) && in_array($userId,
                        $userReactions[$emoji]) ? 'bg-blue-200 rounded px-1' : ''; @endphp <button
                            wire:click="react({{ $msg->id }}, '{{ $emoji }}')" class="{{ $highlight }}"> {{ $emoji }}
                        </button> @endforeach @if($msg->sender_id === $userId) <button
                            wire:click="deleteMessage({{ $msg->id }})" class="text-red-600 ml-2">🗑</button> @endif
                    </div> @endif
                    <!-- Display stored reactions below message --> @if($msg->reactions && $msg->message !== 'This
                    message has been deleted by the sender') <div class="mt-1 text-sm text-gray-500"> @php $reactions =
                        json_decode($msg->reactions, true); @endphp @foreach($reactions as $emoji => $users) <span>{{
                            $emoji }} {{ count($users) }}</span> @endforeach </div> @endif
                </div> @endforeach
            </div>
            <div class="typing-indicator"> @foreach($typingUsers as $name => $ts) <span>{{ $name }} is typing...</span>
                @endforeach </div>
            <div class="flex"> <input type="text" wire:model.live="newMessage" placeholder="Type a message..."
                                      class="border rounded flex-1 px-2 py-1" /> <button wire:click="sendMessage"
                                                                                         class="bg-blue-600 text-white px-4 py-1 ml-2 rounded">Send</button> </div>
        </div>
        <div class="w-1/4 border-r p-2">
            <div>
                <h3 class="font-bold mb-2">Channels</h3>
                <ul> @foreach($channels as $channel) @php $unreadCount = $channel->hasNewMessage ?? 0; @endphp <li
                        class="cursor-pointer flex justify-between items-center p-1 hover:bg-gray-200 relative {{ $selectedChannel == $channel->id ? 'bg-gray-300 font-bold' : '' }}"
                        wire:click="selectChannel({{ $channel->id }})"> #{{ $channel->name }} @if($unreadCount > 0)
                            <span class="bg-red-600 text-white text-xs px-2 py-0.5 rounded-full ml-2"> ✉ {{ $unreadCount }}
                        </span> @endif </li> @endforeach </ul>
            </div>
            <div class="mt-4">
                <h3 class="text-sm font-bold mb-2">Private Chats</h3> @foreach($staffs as $user) @php $uid =
                $user['id']; $name = $user['name']; $photo = $user['photo'] ?? '/images/default-avatar.png'; $isActive =
                $user['isOnline'] ?? false; $unreadCount = $user['unread_count'] ?? 0; @endphp <li
                    wire:click="startPrivateChat('{{ $user['id'] }}')"
                    class="flex items-center gap-2 cursor-pointer relative p-2 hover:bg-gray-100 rounded {{ $selectedChannelName == $name ? 'bg-blue-200' : 'hover:bg-gray-100' }}">
                    <div class="relative"> <img src="{{ asset('storage/' . $photo) }}"
                                                class="w-10 h-10 rounded-full border-4 {{ $isActive ? 'border-green-500' : 'border-gray-300' }}">
                        @if($user['isOnline']) <span
                            class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-white rounded-full"></span>
                        @endif </div>
                    <div class="flex flex-col"> <span>{{ $name }}</span>
                        <div class="text-xs text-gray-500 truncate"> {{ $user['last_message'] ?? 'No messages yet' }}
                        </div>
                            @if(!empty($user['last_message_time']))
                                <div class="text-xs text-gray-400"> {{
                                        \Carbon\Carbon::parse($user['last_message_time'])->diffForHumans() }}
                                </div>
                            @endif

                                    @if(($user['unread_count'] ?? 0) > 0) <span
                                             class="bg-red-500 text-white text-xs px-2 py-1 rounded-full"> {{ $user['unread_count'] }}
                                 </span>
                             @endif
                         </li>
                @endforeach
            </div>
        </div>
        <script>
            Livewire.on('hideReactionBar', data => { const bar = document.querySelector(#reaction-bar-${data.messageId}); if(bar) bar.style.display = 'none'; }); Livewire.on('hideDeletedMessageAlert', () => { setTimeout(() => { const alert = document.getElementById('deletedMessageAlert'); if(alert) alert.style.display = 'none'; }, 5000); });
        </script>
        <script>
            window.addEventListener('removeTyping', event => { const name = event.detail.name; const typingElements = document.querySelectorAll('.typing-indicator span'); typingElements.forEach(el => { if(el.textContent.includes(name)) { el.remove(); // remove typing after 5 sec } }); });
        </script>
    </div>
</div>
