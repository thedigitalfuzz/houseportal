<header class="bg-white border-b p-4 flex items-center justify-between">

    <!-- Left Section -->
    <div class="flex items-center gap-4">

        <!-- Mobile Sidebar Toggle -->
        <button onclick="toggleSidebar()" class="lg:hidden p-2 rounded hover:bg-gray-100">
            <svg fill="#173c61" width="36px" height="36px" viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg" class="icon"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M408 442h480c4.4 0 8-3.6 8-8v-56c0-4.4-3.6-8-8-8H408c-4.4 0-8 3.6-8 8v56c0 4.4 3.6 8 8 8zm-8 204c0 4.4 3.6 8 8 8h480c4.4 0 8-3.6 8-8v-56c0-4.4-3.6-8-8-8H408c-4.4 0-8 3.6-8 8v56zm504-486H120c-4.4 0-8 3.6-8 8v56c0 4.4 3.6 8 8 8h784c4.4 0 8-3.6 8-8v-56c0-4.4-3.6-8-8-8zm0 632H120c-4.4 0-8 3.6-8 8v56c0 4.4 3.6 8 8 8h784c4.4 0 8-3.6 8-8v-56c0-4.4-3.6-8-8-8zM115.4 518.9L271.7 642c5.8 4.6 14.4.5 14.4-6.9V388.9c0-7.4-8.5-11.5-14.4-6.9L115.4 505.1a8.74 8.74 0 0 0 0 13.8z"></path> </g></svg>
        </button>

    </div>

    <!-- Right Section -->
    <div>
        <div class="flex items-center gap-4">
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

                            $photoUrl = $photoPath
                                ? asset('storage/' . $photoPath) . '?v=' . now()->timestamp
                                : asset('assets/images/admin-avatar.png');
                        @endphp

                        <button class="flex items-center gap-2 p-2 rounded hover:bg-gray-50">
                            <img src="{{ $photoUrl }}" class="w-8 h-8 rounded-full object-cover" alt="me">
                            <span class="inline">{{ $displayName }}</span>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        @if(auth()->guard('web')->check() && optional(auth()->guard('web')->user())->role === 'admin')
                            <a href="{{ route('admin.editprofile') }}" class="w-full text-left px-4 py-2">Edit Profile</a>
                        @endif

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2">Logout</button>
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
