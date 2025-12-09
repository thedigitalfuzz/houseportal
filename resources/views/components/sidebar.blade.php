<aside class="w-64 bg-gray-800 min-h-screen text-white flex-shrink-0">
    <div class="p-6 font-bold text-xl border-b border-gray-700">
        HouseSupport Portal
    </div>

    @php
        // Get the authenticated user from either 'web' or 'staff' guard
        $currentUser = auth()->guard('web')->user() ?? auth()->guard('staff')->user();
    @endphp

    <nav class="mt-6">
        <a href="{{ route('dashboard') }}" class="block py-2 px-6 hover:bg-gray-700 {{ request()->routeIs('dashboard') ? 'bg-gray-700' : '' }}">
            Dashboard
        </a>

        <!-- Players Link -->
        <a href="{{ route('players.index') }}" class="block py-2 px-6 hover:bg-gray-700 {{ request()->routeIs('players.index') ? 'bg-gray-700' : '' }}">
            Players
        </a>

        <a href="{{ route('games') }}" class="block py-2 px-6 hover:bg-gray-700 {{ request()->routeIs('games') ? 'bg-gray-700' : '' }}">
            Games
        </a>

        <a href="{{ route('transactions') }}" class="block py-2 px-6 hover:bg-gray-700 {{ request()->routeIs('transactions') ? 'bg-gray-700' : '' }}">
            Transactions
        </a>

        @if($currentUser?->role === 'admin')
            <a href="{{ route('staffs.index') }}" class="block py-2 px-6 hover:bg-gray-700 {{ request()->routeIs('staffs.index') ? 'bg-gray-700' : '' }}">
                Staff Management
            </a>
            <a href="{{ route('admin.editprofile') }}" class="block py-2 px-6 hover:bg-gray-700 {{ request()->routeIs('admin.editprofile') ? 'bg-gray-700' : '' }}">
                Edit Profile
            </a>
        @endif
    </nav>
</aside>
