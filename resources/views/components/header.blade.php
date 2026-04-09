<header class="bg-white border-b p-4 flex items-center justify-between md:fixed md:top-0 md:left-0 md:right-0">

    <!-- Left Section -->
    <div class=" items-center gap-4 justify-center">

        <!-- Mobile Sidebar Toggle -->

        <button onclick="toggleSidebar()" class="p-2 rounded hover:bg-gray-100">
          <!--  <svg fill="#173c61" width="28px" height="28px" viewBox="0 0 1024 1024">
                <path d="M408 442h480c4.4 0 8-3.6 8-8v-56c0-4.4-3.6-8-8-8H408c-4.4 0-8 3.6-8 8v56c0 4.4 3.6 8 8 8zm-8 204c0 4.4 3.6 8 8 8h480c4.4 0 8-3.6 8-8v-56c0-4.4-3.6-8-8-8H408c-4.4 0-8 3.6-8 8v56zm504-486H120c-4.4 0-8 3.6-8 8v56c0 4.4 3.6 8 8 8h784c4.4 0 8-3.6 8-8v-56c0-4.4-3.6-8-8-8z"></path>
            </svg> -->

        </button>
        <button onclick="toggleSidebar()"
                id="sidebarToggleBtn"
                class="md:fixed  top-4 transition-all duration-300
           lg:left-64
           z-30 lg:z-50
           p-2 rounded bg-white hover:bg-gray-100">

            <svg fill="#173c61" width="36px" height="36px" viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg" class="icon"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M408 442h480c4.4 0 8-3.6 8-8v-56c0-4.4-3.6-8-8-8H408c-4.4 0-8 3.6-8 8v56c0 4.4 3.6 8 8 8zm-8 204c0 4.4 3.6 8 8 8h480c4.4 0 8-3.6 8-8v-56c0-4.4-3.6-8-8-8H408c-4.4 0-8 3.6-8 8v56zm504-486H120c-4.4 0-8 3.6-8 8v56c0 4.4 3.6 8 8 8h784c4.4 0 8-3.6 8-8v-56c0-4.4-3.6-8-8-8zm0 632H120c-4.4 0-8 3.6-8 8v56c0 4.4 3.6 8 8 8h784c4.4 0 8-3.6 8-8v-56c0-4.4-3.6-8-8-8zM115.4 518.9L271.7 642c5.8 4.6 14.4.5 14.4-6.9V388.9c0-7.4-8.5-11.5-14.4-6.9L115.4 505.1a8.74 8.74 0 0 0 0 13.8z"></path> </g></svg>

        </button>

    </div>

    <!-- Right Section -->
    <div>
        <div class="flex items-center gap-4 ">
            <div class="relative" wire:poll.5s>
                <livewire:notifications-bell />
            </div>
            <div class="relative" wire:poll.5s>
                <livewire:chat-bell />
            </div>
            <div>
                <x-dropdown align="right">
                    <x-slot name="trigger">
                        @php
                            $webUser = auth()->guard('web')->user();
                            $staffUser = auth()->guard('staff')->user();

                            if ($webUser) {
                                $currentUser = $webUser;
                                $displayName = $currentUser->name ?? 'Guest';
                                $photoPath = $currentUser->photo ?? null;

                            } elseif ($staffUser) {
                                $currentUser = \App\Models\Staff::find($staffUser->id);
                                $displayName = $currentUser->staff_name ?? 'Guest';
                                $photoPath = $currentUser->photo ?? null;

                            } else {
                                $currentUser = null;
                                $displayName = 'Guest';
                                $photoPath = null;
                            }

                            $photoUrl = asset('/images/hslogo.png'); // fallback

if (!empty($photoPath) && file_exists(storage_path('app/public/' . $photoPath))) {
    $photoUrl = asset('storage/' . $photoPath) . '?v=' . now()->timestamp;
}
                        @endphp

                        <button class="flex items-center gap-2 p-2 rounded hover:bg-gray-50">
                            <img src="{{ $photoUrl }}" class="w-8 h-8 rounded-full object-cover" alt="me">
                            <span class="inline">{{ $displayName }}</span>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        @php
                            $user = auth()->guard('web')->user();
                        @endphp

                        @if($user)
                            <div class="hover:bg-gray-100 transition-all py-2">
                            <a href="{{ $user->role === 'admin' ? route('admin.editprofile') : route('staff-profile') }}"
                                class="w-full text-left px-4 py-2">
                                {{ $user->role === 'admin' ? 'Admin Profile' : 'My Profile' }}
                            </a>
                            </div>
                        @endif
                        @if(!$user)
                            <div class="hover:bg-gray-100 transition-all py-2">
                                <a href="{{route('staff-profile')}}" class="w-full text-left px-4 py-2">
                                    My Profile
                                </a>
                            </div>

                        @endif

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 hover:bg-gray-100 transition-all">Logout</button>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>
        </div>

        @if(auth()->guard('web')->check() && optional(auth()->guard('web')->user())->role === 'admin')
            <livewire:admin-profile />
        @endif
    </div>

</header>
