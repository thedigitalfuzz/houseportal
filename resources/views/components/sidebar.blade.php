<div>


@php
    $currentUser = auth()->guard('web')->user() ?? auth()->guard('staff')->user();
    $role = $currentUser?->role;
@endphp
<aside
    id="sidebar"
    class="w-64 bg-gray-800 fixed top-0 left-0 h-screen text-white z-40
           transform -translate-x-full transition-all duration-300
           lg:translate-x-0 flex flex-col sidebar-expanded"
>
    <div class="lg:hidden flex justify-end p-3">
        <button onclick="closeSidebarMobile()" class="text-white text-xl">
            ✕
        </button>
    </div>
    <div id="sidebarHeader" class="p-6 border-b border-gray-700 flex-shrink-0 text-center">
        <a href="{{ route('dashboard') }}">
            <img id="sidebarLogo" src="{{ asset('hs-logo.png') }}" class="mx-auto w-20 transition-all duration-300">
            <span id="sidebarTitle" class="block mt-2 font-bold text-sm transition-all duration-300">
            HouseSupport Portal
        </span>
        </a>
    </div>


    @php
        $currentUser = auth()->guard('web')->user() ?? auth()->guard('staff')->user();
    @endphp

    <nav class="mt-6 space-y-1 overflow-y-auto flex-1 px-2">
        <a href="{{ route('dashboard') }}"
           class="flex items-center gap-3 py-2 px-4 hover:bg-gray-700 {{ request()->routeIs('dashboard') ? 'bg-gray-700' : '' }}">

          <i data-lucide="layout-dashboard"></i>
            <span class="sidebar-text">Dashboard</span>
        </a>

        @php
            $playersOpen = request()->routeIs('players.index', 'player-rankings', 'player-leaderboard','daily-player-leaderboard');
        @endphp

        <details class="group" {{ $playersOpen ? 'open' : '' }}>
            <summary
                class="flex items-center justify-between py-2 px-4 cursor-pointer list-none hover:bg-gray-700 rounded-lg {{ $playersOpen ? 'bg-gray-700' : '' }}"
            >
                <div class="flex items-center gap-3">
                    <i data-lucide="users" class="w-5 h-5"></i>
                    <span class="sidebar-text">Players</span>
                </div>

                <svg class="w-4 h-4 transition-transform duration-300 group-open:rotate-180"
                     fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </summary>

            <div class="ml-4 mt-1 space-y-1">
                {{-- Player Details always visible for support staff and above --}}
                <a href="{{ route('players.index') }}"
                   class="flex items-center gap-3 py-2 px-3 text-sm rounded-md hover:bg-gray-700 {{ request()->routeIs('players.index') ? 'bg-gray-700' : '' }}">

                    <i data-lucide="user" class="w-4 h-4  shrink-0 text-gray-300"></i>
                    <span class="sidebar-text">Player Details</span>
                </a>

                {{-- Rankings and Leaderboard only for wallet manager and admin --}}
                @if(in_array($role, ['wallet_manager', 'admin']))
                    <a href="{{ route('player-rankings') }}"
                       class="flex items-center gap-3 py-2 px-3 text-sm rounded-md hover:bg-gray-700 {{ request()->routeIs('player-rankings') ? 'bg-gray-700' : '' }}">

                        <i data-lucide="trophy" class="w-4 h-4"></i>
                        <span class="sidebar-text">Player Rankings</span>
                    </a>

                    <a href="{{ route('player-leaderboard') }}"
                       class="flex items-center gap-3 py-2 px-3 text-sm rounded-md hover:bg-gray-700 {{ request()->routeIs('player-leaderboard') || request()->routeIs('daily-player-leaderboard')  ? 'bg-gray-700' : '' }}">

                        <i data-lucide="medal" class="w-4 h-4  shrink-0 text-gray-300"></i>
                        <span class="sidebar-text">Leaderboard</span>
                    </a>
                @endif
            </div>
        </details>



        @php
            $gamesOpen = request()->routeIs('games', 'game-credits', 'game-points', 'game-performance','game-credits-credentials');
        @endphp

        <details class="group" {{ $gamesOpen ? 'open' : '' }}>
            <summary
                class="flex items-center justify-between py-2 px-4 cursor-pointer list-none hover:bg-gray-700 rounded-lg {{ $gamesOpen ? 'bg-gray-700' : '' }}"
            >
                <div class="flex items-center gap-3">
                    <i data-lucide="gamepad-2" class="w-5 h-5"></i>
                    <span class="sidebar-text">Games</span>
                </div>

                <svg class="w-4 h-4 transition-transform duration-300 group-open:rotate-180"
                     fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </summary>

            <div class="ml-4 mt-1 space-y-1">
                {{-- Game Details always visible --}}

                <a href="{{ route('games') }}"
                   class="flex items-center gap-3 py-2 px-3 text-sm rounded-md hover:bg-gray-700 {{ request()->routeIs('games') ? 'bg-gray-700' : '' }}">

                    <i data-lucide="list" class="w-4 h-4  shrink-0 text-gray-300"></i>
                    <span class="sidebar-text">Game Details</span>
                </a>
                {{-- Game Points visible for support staff and above --}}


                {{-- Game Credits & Performance → wallet manager and admin only --}}
                @if(in_array($role, ['wallet_manager', 'admin']))

                    <a href="{{ route('game-points') }}"
                       class="flex items-center gap-3 py-2 px-3 text-sm rounded-md hover:bg-gray-700 {{ request()->routeIs('game-points') ? 'bg-gray-700' : '' }}">

                        <i data-lucide="star" class="w-4 h-4  shrink-0 text-gray-300"></i>
                        <span class="sidebar-text">Game Points</span>
                    </a>

                    @if($role === 'admin')
                        <a href="{{ route('game-credits') }}"
                           class="flex items-center gap-3 py-2 px-3 text-sm rounded-md hover:bg-gray-700 {{ request()->routeIs('game-credits') ? 'bg-gray-700' : '' }}">

                            <i data-lucide="wallet-cards" class="w-4 h-4  shrink-0 text-gray-300"></i>
                            <span class="sidebar-text">Game Credits</span>
                        </a>

                        <a href="{{ route('game-credits-credentials') }}"
                           class="flex items-center gap-3 py-2 px-3 text-sm rounded-md hover:bg-gray-700 {{ request()->routeIs('game-credits-credentials') ? 'bg-gray-700' : '' }}">

                            <i data-lucide="key" class="w-4 h-4  shrink-0 text-gray-300"></i>
                            <span class="sidebar-text">Game Credentials</span>
                        </a>
                    @endif

                    <a href="{{ route('game-performance') }}"
                       class="flex items-center gap-3 py-2 px-3 text-sm rounded-md hover:bg-gray-700 {{ request()->routeIs('game-performance') ? 'bg-gray-700' : '' }}">

                        <i data-lucide="activity" class="w-4 h-4  shrink-0 text-gray-300"></i>
                        <span class="sidebar-text">Game Performance</span>
                    </a>
                @endif
            </div>
        </details>


        @if(in_array($role, ['wallet_manager', 'admin']))
            @php
                $walletOpen = request()->routeIs('wallets', 'wallet-details', 'monthly-wallet-updates', 'wallet-performance', 'monthly-wallet-performance', 'overall-wallet-performance');
            @endphp

            <details class="group" {{ $walletOpen ? 'open' : '' }}>
                <summary
                    class="flex items-center justify-between py-2 px-4 cursor-pointer list-none hover:bg-gray-700 rounded-lg {{ $walletOpen ? 'bg-gray-700' : '' }}"
                >
                    <div class="flex items-center gap-3">
                        <i data-lucide="wallet" class="w-5 h-5"></i>
                        <span class="sidebar-text">Wallets</span>
                    </div>

                    <svg class="w-4 h-4 transition-transform duration-300 group-open:rotate-180"
                         fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </summary>

                <div class="ml-4 mt-1 space-y-1">
                    <a href="{{ route('wallet-performance') }}"
                       class="flex items-center gap-3 py-2 px-3 text-sm rounded-md hover:bg-gray-700 {{ request()->routeIs('wallet-performance', 'monthly-wallet-performance','overall-wallet-performance') ? 'bg-gray-700' : '' }}">

                        <i data-lucide="chart-no-axes-column-increasing" class="w-4 h-4  shrink-0 text-gray-300"></i>
                        <span class="sidebar-text">Wallet Performance</span>
                    </a>
                    <a href="{{ route('wallets') }}"
                       class="flex items-center gap-3 py-2 px-3 text-sm rounded-md hover:bg-gray-700 {{ request()->routeIs('wallets') ? 'bg-gray-700' : '' }}">

                        <i data-lucide="file-text" class="w-4 h-4  shrink-0 text-gray-300"></i>
                        <span class="sidebar-text">Wallet Records</span>
                    </a>
                    <a href="{{ route('wallet-details') }}"
                       class="flex items-center gap-3 py-2 px-3 text-sm rounded-md hover:bg-gray-700 {{ request()->routeIs('wallet-details') ? 'bg-gray-700' : '' }}">

                        <i data-lucide="info" class="w-4 h-4  shrink-0 text-gray-300"></i>
                        <span class="sidebar-text">Wallet Details</span>
                    </a>
                    <a href="{{ route('monthly-wallet-updates') }}"
                       class="flex items-center gap-3 py-2 px-3 text-sm rounded-md hover:bg-gray-700 {{ request()->routeIs('monthly-wallet-updates') ? 'bg-gray-700' : '' }}">

                        <i data-lucide="calendar" class="w-4 h-4  shrink-0 text-gray-300"></i>
                        <span class="sidebar-text">Monthly Wallet Updates</span>
                    </a>

                </div>
            </details>
        @endif

        <a href="{{ route('transactions') }}"
           class="flex items-center gap-3 py-2 px-4 rounded-lg hover:bg-gray-700 {{ request()->routeIs('transactions') ? 'bg-gray-700' : '' }}">
            <i data-lucide="arrow-left-right" class="w-5 h-5"></i>
            <span class="sidebar-text">Transactions</span>
        </a>

        <a href="{{ route('chat') }}"
           class="flex items-center gap-3 py-2 px-4 rounded-lg hover:bg-gray-700 {{ request()->routeIs('chat') ? 'bg-gray-700' : '' }}">
            <i data-lucide="message-circle-more" class="w-5 h-5"></i>
            <span class="sidebar-text">Chat</span>
        </a>
        @if(in_array($role, ['wallet_manager', 'support_agent']))
            <a href="{{ route('staff-profile') }}"
               class="flex items-center gap-3 py-2 px-4 rounded-lg hover:bg-gray-700 {{ request()->routeIs('staff-profile') ? 'bg-gray-700' : '' }}">
                <i data-lucide="id-card" class="w-5 h-5"></i>
                <span class="sidebar-text">Staff Profile</span>
            </a>
        @endif
        @if(in_array($role, ['wallet_manager', 'admin']))
            <a href="{{ route('staff-performance') }}"
               class="flex items-center gap-3 py-2 px-4 rounded-lg hover:bg-gray-700 {{ request()->routeIs('staff-performance') ? 'bg-gray-700' : '' }}">
                <i data-lucide="id-card" class="w-5 h-5"></i>
                <span class="sidebar-text">Staff Performance</span>
            </a>
        @endif
        @if($currentUser?->role === 'admin')

            <a href="{{ route('chat.management') }}"
               class="flex items-center gap-3 py-2 px-4 rounded-lg hover:bg-gray-700 {{ request()->routeIs('chat.management') ? 'bg-gray-700' : '' }}">
                <i data-lucide="columns-3-cog" class="w-5 h-5"></i>
                <span class="sidebar-text">Chat Management</span>
            </a>

            @php
                $subOpen = request()->routeIs('subdistributors', 'sub-recharge', 'monthly-sub-recharge-records');
            @endphp

            <details class="group" {{ $subOpen ? 'open' : '' }}>
                <summary
                    class="flex items-center justify-between py-2 px-4 cursor-pointer list-none hover:bg-gray-700 rounded-lg {{ $subOpen ? 'bg-gray-700' : '' }}"
                >
                    <div class="flex items-center gap-3">
                        <i data-lucide="layers" class="w-5 h-5"></i>
                        <span class="sidebar-text">Subdistributors</span>
                    </div>

                    <svg class="w-4 h-4 transition-transform duration-300 group-open:rotate-180"
                         fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </summary>

                <div class="ml-4 mt-1 space-y-1">
                    <a href="{{ route('subdistributors') }}"
                       class="flex items-center gap-3 py-2 px-3 text-sm rounded-md hover:bg-gray-700 {{ request()->routeIs('subdistributors') ? 'bg-gray-700' : '' }}">

                        <i data-lucide="file-text" class="w-4 h-4  shrink-0 text-gray-300"></i>
                        <span class="sidebar-text">Subdistributors List</span>
                    </a>
                    <a href="{{ route('sub-recharge') }}"
                       class="flex items-center gap-3 py-2 px-3 text-sm rounded-md hover:bg-gray-700 {{ request()->routeIs('sub-recharge') ? 'bg-gray-700' : '' }}">

                        <i data-lucide="info" class="w-4 h-4  shrink-0 text-gray-300"></i>
                        <span class="sidebar-text">Recharge List</span>
                    </a>
                    <a href="{{ route('monthly-sub-recharge-records') }}"
                       class="flex items-center gap-3 py-2 px-3 text-sm rounded-md hover:bg-gray-700 {{ request()->routeIs('monthly-sub-recharge-records') ? 'bg-gray-700' : '' }}">

                        <i data-lucide="calendar" class="w-4 h-4  shrink-0 text-gray-300"></i>
                        <span class="sidebar-text">Monthly Recharge Updates</span>
                    </a>
                </div>
            </details>

            <a href="{{ route('staffs.index') }}"
               class="flex items-center gap-3 py-2 px-4 rounded-lg hover:bg-gray-700 {{ request()->routeIs('staffs.index') ? 'bg-gray-700' : '' }}">
                <i data-lucide="users-2" class="w-5 h-5"></i>
                <span class="sidebar-text">Staff Management</span>
            </a>

            <a href="{{ route('admin.editprofile') }}"
               class="flex items-center gap-3 py-2 px-4 rounded-lg hover:bg-gray-700 {{ request()->routeIs('admin.editprofile') ? 'bg-gray-700' : '' }}">
                <i data-lucide="user-cog" class="w-5 h-5"></i>
                <span class="sidebar-text">Admin Profile</span>
            </a>

            <a href="{{ route('player-agents') }}"
               class="flex items-center gap-3 py-2 px-4 rounded-lg hover:bg-gray-700 {{ request()->routeIs('player-agents') ? 'bg-gray-700' : '' }}">
                <i data-lucide="user-check" class="w-5 h-5"></i>
                <span class="sidebar-text">Player Agents</span>
            </a>


        @endif

        @if(in_array($role, ['wallet_manager', 'admin']))

            <a href="{{ route('reports') }}"
               class="flex items-center gap-3 py-2 px-4 rounded-lg hover:bg-gray-700 {{ request()->routeIs('reports') ? 'bg-gray-700' : '' }}">
                <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
                <span class="sidebar-text">Reports</span>
            </a>
        @endif
    </nav>
</aside>
    <div id="sidebarOverlay"
         onclick="closeSidebarMobile()"
         class="fixed inset-0 bg-black bg-opacity-40 z-30 hidden lg:hidden">
    </div>

</div>
