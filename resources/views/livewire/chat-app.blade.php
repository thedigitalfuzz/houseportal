<div class="h-screen w-full max-w-full overflow-hidden bg-[#0f172a] text-gray-200">

    <div wire:poll.30s="heartbeat"></div>
    <div wire:poll.5s="cleanupTypingUsers"></div>

    <div class="flex flex-col-reverse md:flex-row h-screen w-full overflow-hidden">

        <!-- LEFT CHAT -->
        <div class="w-3/4 min-w-0 flex flex-col h-full bg-[#0b1220] overflow-hidden">

            @if($deletedMessageAlert)
                <div id="deletedMessageAlert" class="bg-red-500 text-white px-4 py-2 flex justify-between items-center text-sm shrink-0">
                    <span>{{ $deletedMessageAlert }}</span>
                    <button wire:click="clearDeletedMessageAlert" class="text-lg font-bold">×</button>
                </div>
            @endif

            <div class="flex gap-2 items-center">
                <div class="w-9 h-9 rounded-full overflow-hidden border border-gray-700 shrink-0 ml-2">
                    @foreach($messages as $msg)
                    @if($msg->sender_type === \App\Models\Staff::class)
                        <img src="{{ asset('storage/' . ($msg->sender->photo ?? 'images/default-avatar.png')) }}"
                             class="w-full h-full object-cover">
                    @else
                        <img src="/images/default-avatar.png" class="w-full h-full object-cover">
                    @endif
                    @endforeach
                </div>
                <div class="h-14 shrink-0 flex items-center px-6 border-b border-gray-800 bg-[#0f172a]">
                    <div class="flex items-center gap-2 min-w-0">
                        <h3 class="font-semibold text-lg text-blue-400 truncate">
                            {{ $selectedChannelName }}
                        </h3>

                        @foreach($staffs as $user)
                            @if($selectedChannelName == $user['name'] && ($user['isOnline'] ?? false))
                                <span class="w-2.5 h-2.5 bg-green-500 rounded-full shrink-0"></span>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
            <!-- HEADER -->


            <!-- MESSAGES -->
            <div id="chatContainer" class="flex-1 overflow-y-auto overflow-x-hidden px-6 py-4 space-y-4">

                @php $lastDate = null; @endphp

                @foreach($messages as $msg)

                    @php
                        $msgDate = \Carbon\Carbon::parse($msg->created_at)->format('Y-m-d');
                    @endphp

                    @if($lastDate != $msgDate)
                        <div class="text-center my-4">
                            <span class="bg-gray-700 text-xs px-3 py-1 rounded-full">
                                {{ \Carbon\Carbon::parse($msg->created_at)->format('F d, Y') }}
                            </span>
                        </div>
                        @php $lastDate = $msgDate; @endphp
                    @endif

                    <div class="group flex items-start gap-3 min-w-0" wire:key="msg-{{ $msg->id }}">

                        <!-- AVATAR -->
                        <div class="w-9 h-9 rounded-full overflow-hidden border border-gray-700 shrink-0">
                            @if($msg->sender_type === \App\Models\Staff::class)
                                <img src="{{ asset('storage/' . ($msg->sender->photo ?? 'images/default-avatar.png')) }}"
                                     class="w-full h-full object-cover">
                            @else
                                <img src="/images/default-avatar.png" class="w-full h-full object-cover">
                            @endif
                        </div>

                        <div class="flex-1 min-w-0">

                            <div class="text-sm font-semibold text-blue-400 truncate">
                                @if($msg->sender_type === \App\Models\Staff::class)
                                    {{ $msg->sender->staff_name ?? 'Unknown Staff' }}
                                @elseif($msg->sender_type === \App\Models\User::class)
                                    Administrator
                                @else
                                    Unknown
                                @endif
                            </div>

                            <div class="text-sm text-gray-200 break-words">
                                {{ $msg->message }}
                            </div>

                            @if($msg->reactions && $msg->message !== 'This message has been deleted by the sender')
                                <div class="mt-2 flex gap-2 flex-wrap">
                                    @php $reactions = json_decode($msg->reactions, true); @endphp
                                    @foreach($reactions as $emoji => $users)
                                        <span class="bg-[#1f2937] border border-gray-700 px-2 py-0.5 rounded-full text-sm">
                                            {{ $emoji }} {{ count($users) }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                        </div>

                        <!-- REACTION BUTTON -->
                        <button wire:click="toggleReactionBar({{ $msg->id }})"
                                @if($msg->message === 'This message has been deleted by the sender') style="display:none;" @endif
                                class="opacity-0 group-hover:opacity-100 transition bg-[#1f2937] hover:bg-[#374151] px-2 py-1 rounded-full text-lg shrink-0">
                            👍
                        </button>
                    </div>

                    @if(isset($activeReactionMessage) && $activeReactionMessage == $msg->id && $msg->message !== 'This message has been deleted by the sender')
                        <div id="reaction-bar-{{ $msg->id }}" class="ml-14 mt-1 flex items-center gap-2 bg-[#1f2937] border border-gray-700 px-3 py-1 rounded-full w-fit shadow-lg">

                            @php
                                $userId = Auth::guard('staff')->check()
                                         ? Auth::guard('staff')->id()
                                         : Auth::guard('web')->id();

                                $userReactions = json_decode($msg->reactions, true) ?? [];
                            @endphp

                            @foreach(['👍','❤️','😂','👎','🔥','🎉'] as $emoji)
                                @php
                                    $highlight = isset($userReactions[$emoji]) && in_array($userId, $userReactions[$emoji])
                                        ? 'bg-blue-600 text-white'
                                        : 'hover:bg-gray-600';
                                @endphp

                                <button wire:click="react({{ $msg->id }}, '{{ $emoji }}')"
                                        class="text-xl px-2 py-1 rounded-full {{ $highlight }}">
                                    {{ $emoji }}
                                </button>
                            @endforeach

                            @if($msg->sender_id === $userId)
                                <button wire:click="deleteMessage({{ $msg->id }})"
                                        class="ml-2 text-red-500 text-sm">
                                    🗑
                                </button>
                            @endif
                        </div>
                    @endif

                @endforeach

            </div>

            <!-- TYPING -->
            <div class="px-6 py-1 text-sm text-gray-400 shrink-0">
                @foreach($typingUsers as $name => $ts)
                    <span class="italic">{{ $name }} is typing...</span>
                @endforeach
            </div>

            <!-- INPUT -->
            <div class="p-4 border-t border-gray-800 flex items-center gap-3 bg-[#0f172a] shrink-0">

                <input type="text"
                       wire:model.live="newMessage"
                       placeholder="Message #{{ $selectedChannelName }}"
                       class="flex-1 min-w-0 bg-[#1f2937] border border-gray-700 rounded-full px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">

                <button class="text-xl text-gray-400 hover:text-white shrink-0">😊</button>

                <button wire:click="sendMessage"
                        class="bg-blue-600 hover:bg-blue-500 px-5 py-2 rounded-full text-sm font-medium shrink-0">
                    Send
                </button>
            </div>

        </div>

        <!-- RIGHT SIDEBAR -->
        <div class="w-1/4 min-w-0 h-full bg-[#111827] border-l border-gray-800 flex flex-col overflow-hidden">

            <div class="flex-1 overflow-y-auto overflow-x-hidden p-4">

                <!-- CHANNELS -->
                <h3 class="text-xs uppercase text-gray-400 mb-2">Channels</h3>

                <ul class="space-y-1">
                    @foreach($channels as $channel)
                        @php $unreadCount = $channel->hasNewMessage ?? 0; @endphp

                        <li wire:click="selectChannel({{ $channel->id }})"
                            class="flex justify-between items-center px-3 py-2 rounded cursor-pointer
                            {{ $selectedChannel == $channel->id ? 'bg-[#2563eb]' : 'hover:bg-[#1f2937]' }}">

                            <span># {{ $channel->name }}</span>

                            @if($unreadCount > 0)
                                <span class="bg-red-500 text-xs px-2 py-0.5 rounded-full">
                                    {{ $unreadCount }}
                                </span>
                            @endif
                        </li>
                    @endforeach
                </ul>

                <!-- PRIVATE CHATS -->
                <h3 class="text-xs uppercase text-gray-400 mt-6 mb-2">Direct Messages</h3>

                @foreach($staffs as $user)
                    @php
                        $name = $user['name'];
                        $photo = $user['photo'] ?? '/images/default-avatar.png';
                        $isActive = $user['isOnline'] ?? false;
                    @endphp

                    <li wire:click="startPrivateChat('{{ $user['id'] }}')"
                        class="flex items-center gap-3 px-3 py-2 rounded cursor-pointer
                        {{ $selectedChannelName == $name ? 'bg-[#2563eb]' : 'hover:bg-[#1f2937]' }}">

                        <div class="relative">
                            <img src="{{ asset('storage/' . $photo) }}"
                                 class="w-9 h-9 rounded-full border-2 {{ $isActive ? 'border-green-500' : 'border-gray-600' }}">
                            @if($isActive)
                                <span class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-green-500 border border-[#111827] rounded-full"></span>
                            @endif
                        </div>

                        <div class="flex-1">
                            <div class="text-sm font-medium">{{ $name }}</div>
                            <div class="text-xs text-gray-400 truncate">
                                {{ $user['last_message'] ?? 'No messages yet' }}
                            </div>
                        </div>

                        @if(($user['unread_count'] ?? 0) > 0)
                            <span class="bg-red-500 text-xs px-2 py-1 rounded-full">
                                {{ $user['unread_count'] }}
                            </span>
                        @endif
                    </li>
                @endforeach

            </div>
        </div>

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
            <script>
                window.addEventListener('removeTyping', event => {
                    const name = event.detail.name;
                    const typingElements = document.querySelectorAll('.typing-indicator span');
                    typingElements.forEach(el => {
                        if(el.textContent.includes(name)) {
                            el.remove(); // remove typing after 5 sec
                        }
                    });
                });
            </script>
    <script>
        function scrollToBottom() {
            const container = document.getElementById('chatContainer');
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        }

        document.addEventListener("livewire:update", () => {
            scrollToBottom();
        });

        window.addEventListener("load", () => {
            scrollToBottom();
        });
    </script>
</div>

