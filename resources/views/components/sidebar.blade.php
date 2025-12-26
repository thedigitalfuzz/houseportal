<aside
    id="sidebar"
    class="w-64 bg-gray-800 text-white fixed inset-y-0 left-0 z-40 transform -translate-x-full transition-transform duration-300 lg:translate-x-0 lg:static lg:block flex-shrink-0"
>
    <a href="{{ route('dashboard') }}" class="block">
        <div class="p-6 font-bold text-xl border-b border-gray-700 cursor-pointer">
            <img src="{{ asset('hs-logo.png') }}" alt="Image" class="mx-auto" style="width: 80px;">
            HouseSupport Portal
        </div>
    </a>


    @php
        $currentUser = auth()->guard('web')->user() ?? auth()->guard('staff')->user();
    @endphp

    <nav class="mt-6 space-y-1">
        <a href="{{ route('dashboard') }}"
           class="block py-2 px-6 hover:bg-gray-700 {{ request()->routeIs('dashboard') ? 'bg-gray-700' : '' }}">
            Dashboard
        </a>

        <a href="{{ route('players.index') }}"
           class="block py-2 px-6 hover:bg-gray-700 {{ request()->routeIs('players.index') ? 'bg-gray-700' : '' }}">
            Players
        </a>

        <a href="{{ route('games') }}"
           class="block py-2 px-6 hover:bg-gray-700 {{ request()->routeIs('games') ? 'bg-gray-700' : '' }}">
            Games
        </a>

        <a href="{{ route('transactions') }}"
           class="block py-2 px-6 hover:bg-gray-700 {{ request()->routeIs('transactions') ? 'bg-gray-700' : '' }}">
            Transactions
        </a>

        <a href="{{ route('wallets') }}"
           class="block py-2 px-6 hover:bg-gray-700 {{ request()->routeIs('wallets') ? 'bg-gray-700' : '' }}">
            Wallets
        </a>

        <a href="{{ route('game-credits') }}"
           class="block py-2 px-6 hover:bg-gray-700 {{ request()->routeIs('game-credits') ? 'bg-gray-700' : '' }}">
            Games Credit
        </a>

        @if($currentUser?->role === 'admin')
            <a href="{{ route('staffs.index') }}"
               class="block py-2 px-6 hover:bg-gray-700 {{ request()->routeIs('staffs.index') ? 'bg-gray-700' : '' }}">
                Staff Management
            </a>

            <a href="{{ route('admin.editprofile') }}"
               class="block py-2 px-6 hover:bg-gray-700 {{ request()->routeIs('admin.editprofile') ? 'bg-gray-700' : '' }}">
                Edit Profile
            </a>
        @endif
    </nav>
</aside>
