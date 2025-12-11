<header class="bg-white border-b p-4 flex items-center justify-between">

    <!-- Left Section -->
    <div class="flex items-center gap-4">

        <!-- Mobile Sidebar Toggle -->
        <button onclick="toggleSidebar()" class="lg:hidden p-2 rounded hover:bg-gray-100">
            ☰
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
