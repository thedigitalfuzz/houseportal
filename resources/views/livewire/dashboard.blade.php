<div class="p-4">
    <h1 class="text-4xl text-blue-950 font-bold mb-1 text-center md:text-left">Welcome to Housesupport Portal</h1>
    <h2 class="text-2xl text-gray-600 font-semibold mb-3 text-center md:text-left">This is the dashboard</h2>
    <p class="text-lg mb-6 text-center md:text-left">Use sidebar to navigate</p>
    <div class="font-bold text-xl mb-2"> {{ $monthLabel }} Summary:</div>

    <div class="flex gap-4 flex-col md:flex-row mb-4">
        <div class="flex flex-col md:flex-row gap-4">
            <div class="bg-white shadow rounded p-4">

                <div class="text-gray-500 text-sm font-bold">Total Transactions</div>
                <div class="text-2xl font-bold">
                    {{ number_format($totalTransactions) }}
                </div>
            </div>

            <div class="bg-white shadow rounded p-4">
                <div class="text-gray-500 text-sm font-bold">Total Cash In</div>
                <div class="text-2xl font-bold text-green-600">
                    ${{ number_format($totalCashin, 2) }}
                </div>
            </div>
        </div>
       <div class="flex flex-col md:flex-row gap-4">
           <div class="bg-white shadow rounded p-4">
               <div class="text-gray-500 text-sm font-bold">Total Cash Out</div>
               <div class="text-2xl font-bold text-red-600">
                   ${{ number_format($totalCashout, 2) }}
               </div>
           </div>

           <div class="bg-white shadow rounded p-4">
               <div class="text-gray-500 text-sm font-bold">Net Total</div>
               <div class="text-2xl font-bold {{ $totalNet < 0 ? 'text-red-600' : 'text-green-600' }}">
                   {{ $totalNet < 0 ? '-' : '' }}${{ number_format(abs($totalNet), 2) }}
               </div>
           </div>
       </div>



    </div>
    @if($isSupportAgent || $isWalletManager)
        <div class="font-bold text-xl mb-2"> YOUR {{ $monthLabel }} Summary:</div>
        <div class="flex gap-4 flex-col md:flex-row mb-4">

            <div class="bg-white shadow rounded p-4">
                <div class="text-gray-500 text-sm font-bold">Players Added</div>
                <div class="text-2xl font-bold">{{ $staffPlayersCount }}</div>
            </div>

            <div class="bg-white shadow rounded p-4">
                <div class="text-gray-500 text-sm font-bold">Transactions Added</div>
                <div class="text-2xl font-bold">{{ $staffTransactionsCount }}</div>
            </div>

            <div class="bg-white shadow rounded p-4">
                <div class="text-gray-500 text-sm font-bold">Cash In</div>
                <div class="text-2xl font-bold text-green-600">
                    ${{ number_format($staffTotalCashin, 2) }}
                </div>
            </div>

            <div class="bg-white shadow rounded p-4">
                <div class="text-gray-500 text-sm font-bold">Cash Out</div>
                <div class="text-2xl font-bold text-red-600">
                    ${{ number_format($staffTotalCashout, 2) }}
                </div>
            </div>
            <div class="bg-white shadow rounded p-4">
                <div class="text-gray-500 text-sm font-bold">Net Amount</div>
                <div class="text-2xl font-bold {{ ($staffTotalCashin - $staffTotalCashout) < 0 ? 'text-red-600' : 'text-green-600' }}">
                    {{ ($staffTotalCashin - $staffTotalCashout) < 0 ? '-' : '' }}
                    ${{ number_format(abs($staffTotalCashin - $staffTotalCashout), 2) }}
                </div>
            </div>
            <div class="bg-white shadow rounded p-4">
                <div class="text-gray-500 text-sm font-bold">Highest Cash In</div>

                @if($highestCashinTxn && $highestCashinTxn->cashin > 0)
                    <div class="text-sm font-semibold mt-1">
                        {{ optional($highestCashinTxn->player)->player_name ?? '-' }}
                    </div>
                    <div class="text-xs text-gray-500">
                        {{ optional($highestCashinTxn->game)->name ?? '-' }}
                    </div>
                    <div class="text-lg font-bold text-green-600">
                        ${{ number_format($highestCashinTxn->cashin, 2) }}
                    </div>
                @else
                    <div class="text-sm text-gray-400">No data</div>
                @endif
            </div>

            <div class="bg-white shadow rounded p-4">
                <div class="text-gray-500 text-sm font-bold">Highest Cash Out</div>

                @if($highestCashoutTxn && $highestCashoutTxn->cashout > 0)
                    <div class="text-sm font-semibold mt-1">
                        {{ optional($highestCashoutTxn->player)->player_name ?? '-' }}
                    </div>
                    <div class="text-xs text-gray-500">
                        {{ optional($highestCashoutTxn->game)->name ?? '-' }}
                    </div>
                    <div class="text-lg font-bold text-red-600">
                        ${{ number_format($highestCashoutTxn->cashout, 2) }}
                    </div>
                @else
                    <div class="text-sm text-gray-400">No data</div>
                @endif
            </div>
        </div>
    @endif


    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

        @if($isSupportAgent || $isWalletManager)
            <!-- Top Cashin -->
            <div class="bg-white shadow rounded p-4 overflow-x-auto">
                <div class="flex justify-between mb-2 items-center">
                    <h2 class="font-bold mb-3">Top Cash In Transactions Created by You</h2>
                    <a href="{{ route('transactions') }}" class="inline-block px-2 py-1 text-sm bg-blue-200 rounded-lg sm:px-3 sm:py-2 sm:text-base">
                        Transactions
                    </a>
                </div>

                <table class="min-w-full table-auto">
                    <thead>
                    <tr class="bg-gray-100">
                        <th class="p-2 text-left">Player Name</th>
                        <th class="p-2 text-left">Amount</th>
                        <th class="p-2 text-left">Remarks</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($topCashinTransactions as $txn)
                        <tr class="border-t">
                            <td class="p-2">{{ optional($txn->player)->player_name ?? '-' }}</td>
                            <td class="p-2">${{ number_format($txn->cashin, 2) }}</td>
                            <td class="p-2">
                                {{ $txn->wallet_remarks ?? '-' }}
                            </td>

                        </tr>
                    @endforeach
                    </tbody>
                </table>

            </div>

            <!-- Top Players All Time -->
            <div class="bg-white shadow rounded p-4 overflow-x-auto">

                <div class="flex justify-between mb-2 items-center">
                    <h2 class="font-bold mb-3">Your Top Players (All Time)</h2>
                    <a href="{{ route('players.index') }}" class="inline-block px-2 py-1 text-sm bg-blue-200 rounded-lg sm:px-3 sm:py-2 sm:text-base">
                        Players
                    </a>
                </div>

                <table class="min-w-full table-auto">
                    <thead>
                    <tr class="bg-gray-100">
                        <th class="p-2 text-left">Player Name</th>
                        <th class="p-2 text-left">Total Cashin</th>
                        <th class="p-2 text-left">Last Transaction Date</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($topPlayersAllTime as $p)
                        <tr class="border-t">
                            <td class="p-2">{{ $p->player_name }}</td>
                            <td class="p-2 text-green-500">${{ number_format($p->total_cashin, 2) }}</td>
                            <td class="p-2">
                                {{ $p->last_transaction_date
                                    ? \Carbon\Carbon::parse($p->last_transaction_date)->format('Y-m-d')
                                    : '-'
                                }}
                            </td>

                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <!-- KEEP YOUR ORIGINAL FIRST TWO BOXES -->
            <div class="bg-white shadow rounded p-4 overflow-x-auto">
                <div class="flex justify-between mb-2 items-center">
                    <h2 class="font-bold mb-3">Recent Wallets</h2>
                    <a href="{{ route('wallets') }}" class="inline-block px-2 py-1 text-sm bg-blue-200 rounded-lg sm:px-3 sm:py-2 sm:text-base">
                        Wallets
                    </a>
                </div>

                <table class="min-w-full table-auto">
                    <thead>
                    <tr class="bg-gray-100">
                        <th class="p-2 text-left">Wallet Name</th>
                        <th class="p-2 text-left">Wallet Remarks</th>
                        <th class="p-2 text-left">Balance</th>
                        <th class="p-2 text-left">Date</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($recentWallets as $wallet)
                        <tr class="border-t">
                            <td class="p-2">{{ $wallet->wallet_name }}</td>
                            <td class="p-2">{{ $wallet->wallet_remarks ?? '-' }}</td>
                            <td class="p-2">$ {{ number_format($wallet->current_balance, 2) }}</td>
                            <td class="p-2">{{ $wallet->date->format('Y-m-d') }}</td>

                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Recent Game Credits -->
            <div class="bg-white shadow rounded p-4 overflow-x-auto">
                <div class="flex justify-between items-center mb-2">
                    <h2 class="font-bold mb-3">Recent Game Credits</h2>
                    <a href="{{ route('game-credits') }}" class="inline-block px-2 py-1 text-sm bg-blue-200 rounded-lg sm:px-3 sm:py-2 sm:text-base">
                        Game Credits
                    </a>
                </div>

                <table class="min-w-full table-auto">
                    <thead>
                    <tr class="bg-gray-100">
                        <th class="p-2 text-left">Game</th>
                        <th class="p-2 text-left">Sub Balance</th>
                        <th class="p-2 text-left">Store Name</th>
                        <th class="p-2 text-left">Store Balance</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($recentGameCredits as $gc)
                        <tr class="border-t">
                            <td class="p-2">{{ optional($gc->game)->name ?? '-' }}</td>
                            <td class="p-2">$ {{ number_format($gc->subdistributor_balance, 2) }}</td>
                            <td class="p-2">{{ $gc->store_name }}</td>
                            <td class="p-2">$ {{ number_format($gc->store_balance, 2) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="bg-white shadow rounded p-4 overflow-x-auto">
                <div class="flex justify-between items-center mb-2">
                    <h2 class="font-bold mb-3">Top Game Performance - {{ now()->format('F Y') }} </h2>
                    <a href="{{ route('game-performance') }}"
                       class="inline-block px-2 py-1 text-sm bg-blue-200 rounded-lg sm:px-3 sm:py-2 sm:text-base">
                        Game Performance
                    </a>
                </div>

                <table class="min-w-full table-auto">
                    <thead>
                    <tr class="bg-gray-100">
                        <th class="p-2 text-left">Game</th>
                        <th class="p-2 text-right">Used Points</th>
                        <th class="p-2 text-right">Transactions</th>
                        <th class="p-2 text-right">Cash In</th>
                        <th class="p-2 text-right">Cash Out</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($topGamePerformance as $g)
                        <tr class="border-t">
                            <td class="p-2">{{ $g->game_name }}</td>

                            <td class="p-2 text-right">
                                {{ number_format($g->used_points) }}
                            </td>

                            <td class="p-2 text-right">
                                {{ number_format($g->total_transactions) }}
                            </td>

                            <td class="p-2 text-right text-green-600">
                                ${{ number_format($g->total_cashin, 2) }}
                            </td>

                            <td class="p-2 text-right text-red-600">
                                ${{ number_format($g->total_cashout, 2) }}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
        @if($isSupportAgent)




            @elseif($isWalletManager)
                <!-- KEEP YOUR ORIGINAL FIRST TWO BOXES -->
                <div class="bg-white shadow rounded p-4 overflow-x-auto">
                    <div class="flex justify-between mb-2 items-center">
                        <h2 class="font-bold mb-3">Recent Wallets</h2>
                        <a href="{{ route('wallets') }}" class="inline-block px-2 py-1 text-sm bg-blue-200 rounded-lg sm:px-3 sm:py-2 sm:text-base">
                            Wallets
                        </a>
                    </div>

                    <table class="min-w-full table-auto">
                        <thead>
                        <tr class="bg-gray-100">
                            <th class="p-2 text-left">Wallet Name</th>
                            <th class="p-2 text-left">Wallet Remarks</th>
                            <th class="p-2 text-left">Balance</th>
                            <th class="p-2 text-left">Date</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($recentWallets as $wallet)
                            <tr class="border-t">
                                <td class="p-2">{{ $wallet->wallet_name }}</td>
                                <td class="p-2">{{ $wallet->wallet_remarks ?? '-' }}</td>
                                <td class="p-2">$ {{ number_format($wallet->current_balance, 2) }}</td>
                                <td class="p-2">{{ $wallet->date->format('Y-m-d') }}</td>

                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="bg-white shadow rounded p-4 overflow-x-auto">
                    <div class="flex justify-between items-center mb-2">
                        <h2 class="font-bold mb-3">Top Game Performance - {{ now()->format('F Y') }}</h2>
                        <a href="{{ route('game-performance') }}"
                           class="inline-block px-2 py-1 text-sm bg-blue-200 rounded-lg sm:px-3 sm:py-2 sm:text-base">
                            Game Performance
                        </a>
                    </div>

                    <table class="min-w-full table-auto">
                        <thead>
                        <tr class="bg-gray-100">
                            <th class="p-2 text-left">Game</th>
                            <th class="p-2 text-right">Used Points</th>
                            <th class="p-2 text-right">Transactions</th>
                            <th class="p-2 text-right">Cash In</th>
                            <th class="p-2 text-right">Cash Out</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($topGamePerformance as $g)
                            <tr class="border-t">
                                <td class="p-2">{{ $g->game_name }}</td>

                                <td class="p-2 text-right">
                                    {{ number_format($g->used_points) }}
                                </td>

                                <td class="p-2 text-right">
                                    {{ number_format($g->total_transactions) }}
                                </td>

                                <td class="p-2 text-right text-green-600">
                                    ${{ number_format($g->total_cashin, 2) }}
                                </td>

                                <td class="p-2 text-right text-red-600">
                                    ${{ number_format($g->total_cashout, 2) }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="bg-white shadow rounded p-4 overflow-x-auto">
                    <div class="flex justify-between items-center mb-2">
                        <h2 class="font-bold mb-3">Recent Transactions</h2>
                        <a href="{{ route('transactions') }}" class="inline-block px-2 py-1 text-sm bg-blue-200 rounded-lg sm:px-3 sm:py-2 sm:text-base">
                            Transactions
                        </a>
                    </div>

                    <table class="min-w-full table-auto">
                        <thead>
                        <tr class="bg-gray-100">
                            <th class="p-2 text-left">Player</th>
                            <th class="p-2 text-left">Transaction</th>
                            <th class="p-2 text-left">Amount</th>
                            <th class="p-2 text-left">Remarks</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($recentTransactions as $txn)
                            <tr class="border-t">
                                <td class="p-2">{{ optional($txn->player)->name ?? optional($txn->player)->player_name ?? '-' }}</td>
                                <td class="p-2">
                                    {{ $txn->cashin > 0 ? 'Cash In' : 'Cash Out' }}
                                </td>

                                <td class="p-2">
                                    ${{ number_format($txn->cashin > 0 ? $txn->cashin : $txn->cashout, 2) }}
                                </td>

                                <td class="p-2">
                                    {{ $txn->wallet_remarks ?? '-' }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Top Players (Current Month) -->
                <div class="bg-white shadow rounded p-4 overflow-x-auto">
                    <div class="flex justify-between items-center mb-2">
                        <h2 class="font-bold mb-3">
                            Top Players – {{ now()->format('F Y') }}
                        </h2>
                        @if($isSupportAgent)
                            <a href="{{ route('players.index') }}"
                               class="inline-block px-2 py-1 text-sm bg-blue-200 rounded-lg sm:px-3 sm:py-2 sm:text-base">
                                Players
                            </a>
                        @else
                            <a href="{{ route('player-leaderboard') }}"
                               class="inline-block px-2 py-1 text-sm bg-blue-200 rounded-lg sm:px-3 sm:py-2 sm:text-base">
                                Leaderboard
                            </a>
                        @endif
                    </div>

                    <table class="min-w-full table-auto">
                        <thead>
                        <tr class="bg-gray-100">
                            <th class="p-2 text-left">Rank</th>
                            <th class="p-2 text-left">Player</th>
                            <th class="p-2 text-right">Cash In</th>
                            <th class="p-2 text-right">Cash Out</th>
                        </tr>
                        </thead>

                        <tbody>
                        @forelse($topPlayersCurrentMonth as $index => $player)
                            <tr class="border-t">
                                <td class="p-2 font-semibold">
                                    #{{ $index + 1 }}
                                </td>

                                <td class="p-2">
                                    {{ $player->player_name }}
                                </td>

                                <td class="p-2 text-right text-green-600">
                                    ${{ number_format($player->total_cashin, 2) }}
                                </td>

                                <td class="p-2 text-right text-red-600">
                                    ${{ number_format($player->total_cashout, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-4 text-center text-gray-500">
                                    No player data for this month
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

            @else
            <!-- Recent Transactions -->
            <div class="bg-white shadow rounded p-4 overflow-x-auto">
                <div class="flex justify-between items-center mb-2">
                    <h2 class="font-bold mb-3">Recent Transactions</h2>
                    <a href="{{ route('transactions') }}" class="inline-block px-2 py-1 text-sm bg-blue-200 rounded-lg sm:px-3 sm:py-2 sm:text-base">
                        Transactions
                    </a>
                </div>

                <table class="min-w-full table-auto">
                    <thead>
                    <tr class="bg-gray-100">
                        <th class="p-2 text-left">Player</th>
                        <th class="p-2 text-left">Transaction</th>
                        <th class="p-2 text-left">Amount</th>
                        <th class="p-2 text-left">Remarks</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($recentTransactions as $txn)
                        <tr class="border-t">
                            <td class="p-2">{{ optional($txn->player)->name ?? optional($txn->player)->player_name ?? '-' }}</td>
                            <td class="p-2">
                                {{ $txn->cashin > 0 ? 'Cash In' : 'Cash Out' }}
                            </td>

                            <td class="p-2">
                                ${{ number_format($txn->cashin > 0 ? $txn->cashin : $txn->cashout, 2) }}
                            </td>

                            <td class="p-2">
                                {{ $txn->wallet_remarks ?? '-' }}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Top Players (Current Month) -->
            <div class="bg-white shadow rounded p-4 overflow-x-auto">
                <div class="flex justify-between items-center mb-2">
                    <h2 class="font-bold mb-3">
                        Top Players – {{ now()->format('F Y') }}
                    </h2>
                    @if($isSupportAgent)
                        <a href="{{ route('players.index') }}"
                           class="inline-block px-2 py-1 text-sm bg-blue-200 rounded-lg sm:px-3 sm:py-2 sm:text-base">
                            Players
                        </a>
                    @else
                        <a href="{{ route('player-leaderboard') }}"
                           class="inline-block px-2 py-1 text-sm bg-blue-200 rounded-lg sm:px-3 sm:py-2 sm:text-base">
                            Leaderboard
                        </a>
                    @endif
                </div>

                <table class="min-w-full table-auto">
                    <thead>
                    <tr class="bg-gray-100">
                        <th class="p-2 text-left">Rank</th>
                        <th class="p-2 text-left">Player</th>
                        <th class="p-2 text-right">Cash In</th>
                        <th class="p-2 text-right">Cash Out</th>
                    </tr>
                    </thead>

                    <tbody>
                    @forelse($topPlayersCurrentMonth as $index => $player)
                        <tr class="border-t">
                            <td class="p-2 font-semibold">
                                #{{ $index + 1 }}
                            </td>

                            <td class="p-2">
                                {{ $player->player_name }}
                            </td>

                            <td class="p-2 text-right text-green-600">
                                ${{ number_format($player->total_cashin, 2) }}
                            </td>

                            <td class="p-2 text-right text-red-600">
                                ${{ number_format($player->total_cashout, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-4 text-center text-gray-500">
                                No player data for this month
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>



@endif

   </div>
    @if($isSupportAgent || $isWalletManager)
    @else
   <div class="grid grid-cols-1  gap-4">
       <div wire:ignore class="bg-white shadow rounded p-4 mt-6 mb-6" style="height: 600px;" >
           <h2 class="font-bold mb-4">
               Total Cash In – Last 10 Days
           </h2>

           <canvas id="cashinChart" style="height: 100%; width: 100%;"></canvas>
       </div>
   </div>
    @endif
   <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6">
       @if($isSupportAgent || $isWalletManager)

           <div class="bg-white shadow rounded p-4" wire:ignore style="height: 450px;">
               <h2 class="font-bold mb-2">Your last 5 Days Transactions</h2>
               <canvas id="txnChart"></canvas>
           </div>

           <div class="bg-white shadow rounded p-4" wire:ignore style="height: 450px;">
               <h2 class="font-bold mb-2">Your Cash In Trend (All Time)</h2>
               <canvas id="allTimeCashinChart"></canvas>
           </div>

           <div class="bg-white shadow rounded p-4" wire:ignore style="height: 450px;">
               <h2 class="font-bold mb-2">Your Net Trend (All Time)</h2>
               <canvas id="allTimeNetChart"></canvas>
           </div>


           @else
               <div class="bg-white shadow rounded p-4 ">

                   <div class="grid grid-cols-1 md:grid-cols-2 gap-2 items-start">
                       <div>
                           <h2 class="font-bold mb-2">Game Cash In Distribution</h2>
                           <canvas id="gamePie"></canvas>
                       </div>
                       <div class="grid grid-cols-1">
                           <h2 class="font-bold mb-2">Game Cash In Table</h2>
                           <table class="min-w-full w-full">
                               <thead>
                               <tr class="bg-gray-100">
                                   <th class="p-2 text-left">Game</th>
                                   <th class="p-2 text-left">CashIn</th>
                                   <th class="p-2 text-left">%</th>
                               </tr>
                               </thead>
                               <tbody>
                               @foreach($gameStatsTable as $game)
                                   <tr class="border-t">
                                       <td class="p-2">{{ $game->game_name }}</td>
                                       <td class="p-2 text-green-600">
                                           ${{ number_format($game->total, 2) }}
                                       </td>
                                       <td class="p-2 text-sm text-gray-500">
                                           {{ round(($game->total / array_sum($gamePieData)) * 100, 1) }}%
                                       </td>
                                   </tr>
                               @endforeach
                               </tbody>
                           </table>
                       </div>

                   </div>

               </div>
           <div class="bg-white shadow rounded p-4" wire:ignore style="height: 450px;">
               <h2 class="font-bold mb-2">System Cash In Trend (All Time)</h2>
               <canvas id="allTimeCashinChart"></canvas>
           </div>

           <div class="bg-white shadow rounded p-4" wire:ignore style="height: 450px;">
               <h2 class="font-bold mb-2">System Net Trend (All Time)</h2>
               <canvas id="allTimeNetChart"></canvas>
           </div>
       @endif


   </div>


   <script>
       window.cashinChartData = {
           labels: @json($dailyCashinLabels),
           data: @json($dailyCashinData)
       };
       console.log('Cashin Chart Data:' , window.cashinChartData);
   </script>
   <script>
       function renderCashinChart() {
           const ctx = document.getElementById('cashinChart');
           if(!ctx) return;

           if(window.cashinChartInstance) {
               window.cashinChartInstance.destroy();
           }

           window.cashinChartInstance = new Chart(ctx.getContext('2d'), {
               type: 'bar',
               data: {
                   labels: @json($dailyCashinLabels),
                   datasets: [{
                       label: 'Total Cash In ($)',
                       data: @json($dailyCashinData),
                       backgroundColor: '#2b6cb0',
                       borderRadius: 6,
                   }]
               },
               options: {
                   responsive: true,
                   maintainAspectRatio: false,
                   plugins: {
                       legend: { display: false },
                       tooltip: {
                           callbacks: {
                               label: function(context) {
                                   return '$' + context.raw.toLocaleString();
                               }
                           }
                       }
                   },
                   scales: {
                       y: {
                           beginAtZero: true,
                           ticks: {
                               callback: function(value) {
                                   return '$' + value.toLocaleString(); // <-- adds $ to y-axis labels
                               }
                           }
                       }
                   }
               }
           });
       }

       // Run once when page loads
       document.addEventListener('DOMContentLoaded', () => renderCashinChart());

       // Re-run after Livewire updates (if data is dynamic)
       Livewire.hook('message.processed', () => renderCashinChart());
   </script>
   <script>
       function renderExtraCharts() {
           const pieCtx = document.getElementById('gamePie');
           if(pieCtx) {
               new Chart(pieCtx, {
                   type: 'pie',
                   data: {
                       labels: @json($gamePieLabels),
                       datasets: [{
                           data: @json($gamePieData)
                       }]
                   }
               });
           }
       }

       document.addEventListener('DOMContentLoaded', renderExtraCharts);
       Livewire.hook('message.processed', renderExtraCharts);
   </script>
   <script>
       // global variable to store chart instance
       let txnChartInstance = null;

       function renderTxnChart(labels, data) {
           const ctx = document.getElementById('txnChart');
           if (!ctx) return;

           // Destroy old chart if exists
           if (txnChartInstance) {
               txnChartInstance.destroy();
           }

           txnChartInstance = new Chart(ctx.getContext('2d'), {
               type: 'bar',
               data: {
                   labels: labels,
                   datasets: [{
                       label: '', // hide legend
                       data: data,
                       backgroundColor: '#8b5cf6', // purple
                       borderRadius: 6
                   }]
               },
               options: {
                   responsive: true,
                   maintainAspectRatio: false,
                   plugins: {
                       legend: { display: false },
                       tooltip: {
                           callbacks: {
                               label: function(context) {
                                   return '$' + context.raw.toLocaleString(); // show $ sign
                               }
                           }
                       }
                   },
                   scales: {
                       y: {
                           beginAtZero: true,
                           ticks: {
                               callback: function(value) {
                                   return '$' + value.toLocaleString();
                               }
                           }
                       }
                   }
               }
           });
       }

       // initial render
       document.addEventListener('DOMContentLoaded', function() {
           renderTxnChart(@json($last5DaysTxnLabels), @json($last5DaysTxnData));
       });
   </script>
    <script>
        let allTimeCashinChartInstance = null;
        let allTimeNetChartInstance = null;

        function renderAllTimeCharts() {
            const cashinCtx = document.getElementById('allTimeCashinChart');
            const netCtx = document.getElementById('allTimeNetChart');

            // CASHIN CHART
            if (cashinCtx) {
                if (allTimeCashinChartInstance) {
                    allTimeCashinChartInstance.destroy();
                }

                allTimeCashinChartInstance = new Chart(cashinCtx.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: @json($allTimeCashinLabels),
                        datasets: [{
                            label: 'Cash In',
                            data: @json($allTimeCashinData),
                            borderWidth: 2,
                            tension: 0.3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return '$' + context.raw.toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // NET CHART
            if (netCtx) {
                if (allTimeNetChartInstance) {
                    allTimeNetChartInstance.destroy();
                }

                allTimeNetChartInstance = new Chart(netCtx.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: @json($allTimeNetLabels),
                        datasets: [{
                            label: 'Net',
                            data: @json($allTimeNetData),
                            borderWidth: 2,
                            tension: 0.3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return '$' + context.raw.toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                });
            }
        }

        document.addEventListener('DOMContentLoaded', renderAllTimeCharts);
        Livewire.hook('message.processed', renderAllTimeCharts);
    </script>




</div>
