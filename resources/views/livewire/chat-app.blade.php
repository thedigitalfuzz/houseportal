<div class="w-full max-w-full overflow-hidden bg-gray-50 text-gray-800">
    <div wire:poll.30s="heartbeat"></div>
    <div wire:poll.5s="cleanupTypingUsers"></div>
    <div class="grid grid-cols-1">
        <div class="flex flex-col-reverse md:flex-row w-full overflow-hidden">
            <!-- LEFT CHAT -->
            <div class="md:w-3/4 w-full min-w-0 flex flex-col bg-gray-50 overflow-hidden" style="height: calc(100vh - 6rem - 72px);">
                @if($deletedMessageAlert) <div id="deletedMessageAlert"
                                               class="bg-red-100 text-red-700 px-4 py-2 flex justify-between items-center text-sm shrink-0 rounded shadow">
                    <span>{{ $deletedMessageAlert }}</span> <button wire:click="clearDeletedMessageAlert"
                                                                    class="text-lg font-bold">×</button> </div> @endif
                <!-- HEADER -->
                <div class="flex gap-2 items-center bg-white border-b border-gray-200 px-4 py-2 shadow-sm">
                    <div class="w-10 h-10 rounded-full overflow-hidden border border-gray-300 shrink-0">
                        @php
                            $headerPhoto = asset('/images/hslogo.png'); // fallback
                            foreach($staffs as $user) {
                                if($selectedChannelName == $user['name']) {
                                    // Check if photo exists and the file is present in storage
                                    if(!empty($user['photo']) && file_exists(storage_path('app/public/' . $user['photo']))) {
                                        $headerPhoto = asset('storage/' . $user['photo']);
                                    }
                                    break;
                                }
                            }
                        @endphp

                        <img src="{{ $headerPhoto }}" class="w-full h-full object-cover">
                    </div>

                    <div class="flex-1 flex items-center justify-between">
                        <h3 class="font-semibold text-lg text-gray-900 truncate"> {{ $selectedChannelName }} </h3>
                        @foreach($staffs as $user) @if($selectedChannelName == $user['name'] && ($user['isOnline'] ??
                    false)) <span class="w-3 h-3 bg-green-500 rounded-full shrink-0"></span> @endif @endforeach
                    </div>
                </div> <!-- MESSAGES -->
                <div id="chatContainer" class="flex-1 overflow-y-auto overflow-x-hidden px-6 py-4 space-y-4"> @php $lastDate
                = null; @endphp @foreach($messages as $msg) @php $msgDate =
                \Carbon\Carbon::parse($msg->created_at)->format('Y-m-d'); @endphp @if($lastDate != $msgDate) <div
                        class="text-center my-4"> <span class="bg-gray-200 text-xs px-3 py-1 rounded-full"> {{
                        \Carbon\Carbon::parse($msg->created_at)->format('F d, Y') }} </span> </div> @php $lastDate =
                $msgDate; @endphp @endif
                    <!-- MESSAGE BUBBLE -->
                    <div class="group flex items-start gap-3 min-w-0" wire:key="msg-{{ $msg->id }}">
                        <!-- AVATAR -->
                        <div class="w-10 h-10 rounded-full overflow-hidden border border-gray-300 shrink-0">
                            @php
                                $photoPath = asset('/images/hslogo.png'); // fallback

                                if($msg->sender) {
                                    // Check if photo exists and the file actually exists in storage
                                    if(!empty($msg->sender->photo) && file_exists(storage_path('app/public/' . $msg->sender->photo))) {
                                        $photoPath = asset('storage/' . $msg->sender->photo);
                                    }
                                }
                            @endphp

                            <img src="{{ $photoPath }}" class="w-full h-full object-cover"></div> <!-- BUBBLE CONTENT -->
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-semibold text-gray-900 truncate"> @if($msg->sender_type ===
                            \App\Models\Staff::class) {{ $msg->sender->staff_name ?? 'Unknown Staff' }}
                                @elseif($msg->sender_type === \App\Models\User::class) Administrator @else Unknown @endif
                            </div>
                            <div class="mt-1 text-sm text-gray-800 break-words p-3 rounded-xl bg-gray-100 shadow-sm"> {{
                            $msg->message }} </div> @if($msg->reactions && $msg->message !== 'This message has been
                        deleted by the sender') <div class="mt-2 flex gap-2 flex-wrap"> @php $reactions =
                            json_decode($msg->reactions, true); @endphp @foreach($reactions as $emoji => $users) <span
                                    class="bg-white border border-gray-200 px-2 py-0.5 rounded-full text-sm shadow-sm"> {{
                                $emoji }} {{ count($users) }} </span> @endforeach </div> @endif
                        </div> <!-- REACTION BUTTON --> <button wire:click="toggleReactionBar({{ $msg->id }})"
                                                                @if($msg->message === 'This message has been deleted by the sender') style="display:none;"
                                                                @endif class="opacity-0 group-hover:opacity-100 transition bg-gray-100 hover:bg-gray-200 px-2
                        py-1 rounded-full text-lg shrink-0"> 👍 </button>
                    </div> @if(isset($activeReactionMessage) && $activeReactionMessage == $msg->id && $msg->message !==
                'This message has been deleted by the sender') <div id="reaction-bar-{{ $msg->id }}"
                                                                    class="ml-14 mt-1 flex items-center gap-2 bg-gray-100 border border-gray-200 px-3 py-1 rounded-full w-fit shadow">
                        @php $userId = Auth::guard('staff')->check() ? Auth::guard('staff')->id() :
                    Auth::guard('web')->id(); $userReactions = json_decode($msg->reactions, true) ?? []; @endphp
                        @foreach(['👍','❤️','😂','👎','🔥','🎉'] as $emoji) @php $highlight = isset($userReactions[$emoji])
                    && in_array($userId, $userReactions[$emoji]) ? 'bg-blue-600 text-white' : 'hover:bg-gray-200';
                        @endphp <button wire:click="react({{ $msg->id }}, '{{ $emoji }}')"
                                        class="text-xl px-2 py-1 rounded-full {{ $highlight }}"> {{ $emoji }} </button> @endforeach
                        @if($msg->sender_id === $userId) <button wire:click="deleteMessage({{ $msg->id }})"
                                                                 class="ml-2 text-red-500 text-sm"> 🗑 </button> @endif </div> @endif @endforeach
                </div> <!-- TYPING -->
                <div class="px-6 py-1 text-sm text-gray-500 shrink-0"> @foreach($typingUsers as $name => $ts) <span
                        class="italic">{{ $name }} is typing...</span> @endforeach </div> <!-- INPUT -->
                <div
                    class="p-4 border-t border-gray-200 flex items-center gap-3 bg-white shrink-0 shadow-inner rounded-t-lg">
                    <input type="text" wire:model.live="newMessage" wire:keydown.enter="sendMessage"  placeholder="Message {{ $selectedChannelName }}"
                           class="flex-1 min-w-0 bg-gray-100 border border-gray-200 rounded-full px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <!--<button class="text-xl text-gray-500 hover:text-gray-700 shrink-0">😊</button> -->
                    <button
                        wire:click="sendMessage"
                        class="bg-blue-600 hover:bg-blue-500 px-5 py-2 rounded-full text-sm font-medium shrink-0 text-white">
                        Send </button> </div>
            </div>
            <!-- RIGHT SIDEBAR -->
            <div class="w-full md:w-1/4 min-w-0 flex-shrink bg-white border-l border-gray-200 flex flex-col overflow-hidden md:h-[calc(100vh-6rem-72px)]">
                <div class=" overflow-y-auto overflow-x-hidden p-2 md:p-4 w-full overflow-hidden">

                    <!-- CHANNELS -->
                    <h3 class="text-xs uppercase text-gray-500 mb-2 hidden md:block">Channels</h3>
                    <ul class="flex gap-3 md:flex-col overflow-x-auto md:overflow-x-visible w-full max-w-full min-w-0">
                        @foreach($channels as $channel)
                            @php $unreadCount = $channel->hasNewMessage ?? 0;
                            @endphp
                            @php $hasUnread = $channel->hasNewMessage ?? false; @endphp

                            <li wire:click="selectChannel({{ $channel->id }})"
                                class="flex  items-center gap-2 px-3 py-2 rounded cursor-pointer  shrink-0
    {{ $selectedChannel == $channel->id ? 'bg-blue-200' : 'hover:bg-gray-100' }}">

   <span class="{{ isset($newMessages[$channel->id]) ? 'font-bold' : '' }}">
        #{{ $channel->name }}
    </span>

                                @if(isset($newMessages[$channel->id]))
                                    <span class="bg-red-500 text-white rounded-full px-1 py-0.5 ">New</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>


                    <!-- PRIVATE CHATS -->
                    <h3 class="text-xs uppercase text-gray-500 mt-6 mb-2 hidden md:block">Direct Messages</h3>
                    <ul class="flex md:flex-col gap-3 overflow-x-auto md:overflow-x-visible w-full max-w-full">
                        @foreach($staffs as $user)
                            @php $name = $user['name']; $photo = $user['photo'] ?? '/images/hslogo.png'; $isActive =
                $user['isOnline'] ?? false;
// Fallback logic for photo
        $photoPath = asset('/images/hslogo.png'); // default fallback
        if(!empty($user['photo']) && file_exists(storage_path('app/public/' . $user['photo']))) {
            $photoPath = asset('storage/' . $user['photo']);
        }

                            @endphp

                            <li wire:click="startPrivateChat('{{ $user['id'] }}')"
                                class="flex flex-row items-center gap-3 px-3 py-2 rounded cursor-pointer shrink-0 {{ $selectedChannelName == $name ? 'bg-blue-200' : 'hover:bg-gray-100' }}">
                                <div class="relative">
                                    <img src="{{ $photoPath }}"
                                         class="w-10 h-10 rounded-full border-4 {{ $isActive ? 'border-green-500' : 'border-gray-200' }}">
                                    @if($isActive)
                                        <span
                                            class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-green-500 border border-white rounded-full"></span>
                                    @endif
                                    @if(($user['unread_count'] ?? 0) > 0)
                                        <span
                                            class="bg-red-500 text-white absolute top-0 right-0 md:hidden text-xs px-2 py-1 rounded-full"> {{ $user['unread_count'] }}
                            </span>
                                    @endif
                                </div>
                                <div class="hidden md:flex md:flex-col md:flex-1">
                                    <div class="text-sm font-medium">{{ $name }}</div>
                                    <div class="text-xs text-gray-500 truncate"> {{ $user['last_message'] ?? 'No messages yet' }}
                                    </div>
                                    @if(!empty($user['last_message_time']))
                                        <div class="text-xs text-gray-400"> {{
                                        \Carbon\Carbon::parse($user['last_message_time'])->diffForHumans() }}
                                        </div>
                                    @endif
                                </div>


                                @if(($user['unread_count'] ?? 0) > 0)
                                    <span
                                        class="bg-red-500 hidden md:block text-white text-xs px-2 py-1 rounded-full"> {{ $user['unread_count'] }}
                            </span>
                                @endif
                            </li>


                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>



    <script>
        Livewire.on('hideReactionBar', data => { const bar = document.querySelector(#reaction-bar-${data.messageId}); if(bar) bar.style.display = 'none'; }); Livewire.on('hideDeletedMessageAlert', () => { setTimeout(() => { const alert = document.getElementById('deletedMessageAlert'); if(alert) alert.style.display = 'none'; }, 5000); });
    </script>
    <script>
        window.addEventListener('removeTyping', event => { const name = event.detail.name; const typingElements = document.querySelectorAll('.typing-indicator span'); typingElements.forEach(el => { if(el.textContent.includes(name)) { el.remove(); // remove typing after 5 sec } }); });
    </script>
    <script>
        window.addEventListener('scrollChatToBottom', () => {
            const chatContainer = document.getElementById('chatContainer');
            if (chatContainer) {
                chatContainer.scrollTop = chatContainer.scrollHeight;
            }
        });

        // Optional: scroll to bottom on initial page load
        document.addEventListener('DOMContentLoaded', () => {
            const chatContainer = document.getElementById('chatContainer');
            if (chatContainer) {
                chatContainer.scrollTop = chatContainer.scrollHeight;
            }
        });
    </script>
</div>
