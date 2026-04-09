<div class="relative">

    <button wire:click="toggleDropdown" class="relative">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-bell-icon lucide-bell"><path d="M10.268 21a2 2 0 0 0 3.464 0"/><path d="M3.262 15.326A1 1 0 0 0 4 17h16a1 1 0 0 0 .74-1.673C19.41 13.956 18 12.499 18 8A6 6 0 0 0 6 8c0 4.499-1.411 5.956-2.738 7.326"/></svg>
        @php $unreadCount = $notifications->where('is_read', 0)->count(); @endphp

        @if($unreadCount > 0)
            <span class="absolute -top-1 -right-1 bg-red-600 text-white rounded-full px-1 text-xs">
        {{ $unreadCount }}
    </span>
        @endif
    </button>

    @if($isOpen)
        <div class="absolute right-0 mt-2 w-80 bg-white border shadow-lg rounded z-50 max-h-96 overflow-y-auto">
            <!-- Mobile close button -->
            <div class="flex justify-end p-2 md:hidden">
                <button wire:click="$set('isOpen', false)" class="text-gray-500 font-bold">&times;</button>
            </div>

            <ul class="divide-y">
                @forelse($notifications as $n)
                    <li class="p-3 flex justify-between items-start gap-2
    {{$n->is_read == 0 ? 'bg-red-50' : ''}}">

                        <!-- Clickable content -->
                        <div wire:click="markAsRead({{ $n->id }})"
                             class="flex-1 cursor-pointer pr-2">

                            <!-- Heading -->
                            <div class="text-blue-600 font-semibold text-sm">
                                {{ $n->type }}
                            </div>

                            <!-- Message -->
                            <div class="text-gray-700 text-sm">
                                {{ $n->message }}
                            </div>

                            <!-- Date -->
                            <div class="text-xs text-gray-400 mt-1">
                                {{ $n->created_at->format('Y-m-d') }}
                            </div>
                        </div>

                        <!-- Delete button -->
                        <button wire:click.stop="deleteNotification({{ $n->id }})"
                                class="text-gray-400 hover:text-red-600 font-bold">
                            &times;
                        </button>
                    </li>
                @empty
                    <li class="p-2 text-gray-500 text-center">
                        No new notifications
                    </li>
                @endforelse
            </ul>
        </div>
    @endif
    <script>
        document.addEventListener('livewire:load', () => {

            window.addEventListener('redirect-to', event => {
                window.location.href = event.detail.url;
            });

            window.addEventListener('notification-deleted', event => {
                alert(event.detail.message);
            });

        });
    </script>
</div>
