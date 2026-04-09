<div class="relative">

    <button wire:click="toggleDropdown" class="relative">
        <!-- ICON -->
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-message-circle-more-icon lucide-message-circle-more"><path d="M2.992 16.342a2 2 0 0 1 .094 1.167l-1.065 3.29a1 1 0 0 0 1.236 1.168l3.413-.998a2 2 0 0 1 1.099.092 10 10 0 1 0-4.777-4.719"/><path d="M8 12h.01"/><path d="M12 12h.01"/><path d="M16 12h.01"/></svg>

    @if($this->totalUnread > 0)
            <span class="absolute -top-1 -right-1 bg-red-600 text-white text-xs px-1 rounded-full">
                {{ $this->totalUnread > 99 ? '99+' : $this->totalUnread }}
            </span>
        @endif
    </button>

    @if($isOpen)
        <div class="absolute right-0 mt-2 w-80 bg-white border shadow-lg rounded z-50 max-h-96 overflow-y-auto">

            <ul class="divide-y">
                @forelse($conversations as $chat)
                    <li wire:click="goToChat({{ $chat['channel_id'] }})"
                        class="p-3 cursor-pointer hover:bg-gray-100 flex justify-between">

                        <div>
                            <div class="font-semibold text-sm">
                                {{ $chat['name'] }}
                            </div>

                            <div class="text-xs text-gray-500 truncate">
                                {{ $chat['last_message'] }}
                            </div>
                        </div>

                        @if($chat['unread'] > 0)
                            <span class="bg-red-500 text-white text-xs px-2 py-1 rounded-full">
                                {{ $chat['unread'] }}
                            </span>
                        @endif

                    </li>
                @empty
                    <li class="p-3 text-center text-gray-500">
                        No unread messages
                    </li>
                @endforelse
            </ul>
        </div>
    @endif

</div>
