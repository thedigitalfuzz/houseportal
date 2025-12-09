<header class="bg-white border-b p-4 flex items-center justify-between">
    <div class="flex items-center gap-4">
{{--        <button class="md:hidden p-2 rounded hover:bg-gray-100">☰</button>--}}
{{--        <form action="" method="GET" class="hidden sm:block">--}}
{{--            <input name="q" placeholder="Search..." class="border rounded px-3 py-2 w-72" />--}}
{{--        </form>--}}
    </div>

    <div>
        <div class="flex items-center gap-4">
            <div>
                <x-dropdown align="right">
                    <x-slot name="trigger">
                        @php
                            // Always prioritize the main admin (web guard)
                            $webUser = auth()->guard('web')->user();
                            $staffUser = auth()->guard('staff')->user();

                            if ($webUser) {
                                // Main admin or normal user via web guard
                                $currentUser = $webUser;
                                $displayName = $currentUser->name ?? 'Guest';
                                $photoPath = $currentUser->photo ?? null;

                            } elseif ($staffUser) {
                                // Staff user (reload full model to ensure correct photo)
                                $currentUser = \App\Models\Staff::find($staffUser->id);
                                $displayName = $currentUser->staff_name ?? 'Guest';
                                $photoPath = $currentUser->photo ?? null;

                            } else {
                                $currentUser = null;
                                $displayName = 'Guest';
                                $photoPath = null;
                            }

                            // Final URL with cache-buster so updated admin photo always reloads
                            $photoUrl = $photoPath
                                ? asset('storage/' . $photoPath) . '?v=' . now()->timestamp
                                : asset('assets/images/admin-avatar.png');
                        @endphp


                        <button class="flex items-center gap-2 p-2 rounded hover:bg-gray-50">
                            <img src="{{ $photoUrl }}" class="w-8 h-8 rounded-full object-cover" alt="me">
                            <span class="hidden sm:inline">{{ $displayName }}</span>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        {{-- Only main admin (web guard with role=admin) sees Edit Profile --}}
                        @if(auth()->guard('web')->check() && optional(auth()->guard('web')->user())->role === 'admin')
                            <a href="{{ route('admin.editprofile') }}" class="w-full text-left px-4 py-2" >Edit Profile</a>
                        @endif

                        {{-- Logout visible to everyone --}}
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2">Logout</button>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>
        </div>

        {{-- Include AdminProfile Livewire component ONLY for main admin --}}
        @if(auth()->guard('web')->check() && optional(auth()->guard('web')->user())->role === 'admin')
            <livewire:admin-profile />
        @endif
    </div>


</header>
