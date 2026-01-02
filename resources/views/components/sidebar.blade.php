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

        @php
            $playersOpen = request()->routeIs(
                'players.index',
                'player-rankings',
                'player-leaderboard'
            );
        @endphp

        <details class="group" {{ $playersOpen ? 'open' : '' }}>
            <summary
                class="flex items-center justify-between py-2 px-6 cursor-pointer list-none hover:bg-gray-700 {{ $playersOpen ? 'bg-gray-700' : '' }}"
            >
                <span>Players</span>

                <svg
                    class="wallet-chevron w-4 h-4 transition-transform duration-300 ease-in-out"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </summary>

            <div class="ml-4 mt-1 space-y-1">
                <a
                    href="{{ route('players.index') }}"
                    class="block py-2 px-6 text-sm hover:bg-gray-700 {{ request()->routeIs('players.index') ? 'bg-gray-700' : '' }}"
                >
                    Player Details
                </a>

                <a
                    href="{{ route('player-rankings') }}"
                    class="block py-2 px-6 text-sm hover:bg-gray-700 {{ request()->routeIs('player-rankings') ? 'bg-gray-700' : '' }}"
                >
                    Player Rankings
                </a>

                <a
                    href="{{ route('player-leaderboard') }}"
                    class="block py-2 px-6 text-sm hover:bg-gray-700 {{ request()->routeIs('player-leaderboard') ? 'bg-gray-700' : '' }}"
                >
                    Leaderboard
                </a>
            </div>
        </details>


        @php
            $gamesOpen = request()->routeIs(
                'games',
                'game-credits',
                'game-points'
            );
        @endphp

        <details class="group" {{ $gamesOpen ? 'open' : '' }}>
            <summary
                class="flex items-center justify-between py-2 px-6 cursor-pointer list-none hover:bg-gray-700 {{ $gamesOpen ? 'bg-gray-700' : '' }}"
            >
                <span>Games</span>

                <svg
                    class="wallet-chevron w-4 h-4 transition-transform duration-300 ease-in-out"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </summary>

            <div class="ml-4 mt-1 space-y-1">
                <a
                    href="{{ route('games') }}"
                    class="block py-2 px-6 text-sm hover:bg-gray-700 {{ request()->routeIs('games') ? 'bg-gray-700' : '' }}"
                >
                    Game Details
                </a>

                <a
                    href="{{ route('game-credits') }}"
                    class="block py-2 px-6 text-sm hover:bg-gray-700 {{ request()->routeIs('game-credits') ? 'bg-gray-700' : '' }}"
                >
                    Game Credits
                </a>

                <a
                    href="{{ route('game-points') }}"
                    class="block py-2 px-6 text-sm hover:bg-gray-700 {{ request()->routeIs('game-points') ? 'bg-gray-700' : '' }}"
                >
                    Game Points
                </a>
            </div>
        </details>



        @php
            $walletOpen = request()->routeIs(
                'wallets',
                'wallet-details',
                'monthly-wallet-updates'
            );
        @endphp

        <details class="group" {{ $walletOpen ? 'open' : '' }}>
            <summary
                class="flex items-center justify-between py-2 px-6 cursor-pointer list-none hover:bg-gray-700 {{ $walletOpen ? 'bg-gray-700' : '' }}"
            >
                <span>Wallets</span>

                <!-- Chevron -->
                <svg
                    class="wallet-chevron w-4 h-4 transition-transform duration-300 ease-in-out"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>

            </summary>

            <div class="ml-4 mt-1 space-y-1">
                <a
                    href="{{ route('wallets') }}"
                    class="block py-2 px-6 text-sm hover:bg-gray-700 {{ request()->routeIs('wallets') ? 'bg-gray-700' : '' }}"
                >
                    Wallet Records
                </a>

                <a
                    href="{{ route('wallet-details') }}"
                    class="block py-2 px-6 text-sm hover:bg-gray-700 {{ request()->routeIs('wallet-details') ? 'bg-gray-700' : '' }}"
                >
                    Wallet Details
                </a>

                <a
                    href="{{ route('monthly-wallet-updates') }}"
                    class="block py-2 px-6 text-sm hover:bg-gray-700 {{ request()->routeIs('monthly-wallet-updates') ? 'bg-gray-700' : '' }}"
                >
                    Monthly Wallet Updates
                </a>
            </div>
        </details>

        <a href="{{ route('transactions') }}"
           class="block py-2 px-6 hover:bg-gray-700 {{ request()->routeIs('transactions') ? 'bg-gray-700' : '' }}">
            Transactions
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
