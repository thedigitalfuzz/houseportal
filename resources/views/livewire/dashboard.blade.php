<div class="p-4">
    <h1 class="text-4xl text-blue-950 font-bold mb-1 text-center md:text-left">Welcome to Housesupport Portal</h1>
    <h2 class="text-2xl text-gray-600 font-semibold mb-3 text-center md:text-left">This is the dashboard</h2>
    <p class="text-lg mb-6 text-center md:text-left">Use sidebar to navigate</p>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

        <!-- Recent Wallets -->
        <div class="bg-white shadow rounded p-4 overflow-x-auto">
            <h2 class="font-bold mb-3">Recent Wallets</h2>
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
            <h2 class="font-bold mb-3">Recent Game Credits</h2>
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

        <!-- Recent Transactions -->
        <div class="bg-white shadow rounded p-4 overflow-x-auto">
            <h2 class="font-bold mb-3">Recent Transactions</h2>
            <table class="min-w-full table-auto">
                <thead>
                <tr class="bg-gray-100">
                    <th class="p-2 text-left">Player</th>
                    <th class="p-2 text-left">Cash In</th>
                    <th class="p-2 text-left">Cash Out</th>
                    <th class="p-2 text-left">Total</th>
                </tr>
                </thead>
                <tbody>
                @foreach($recentTransactions as $txn)
                    <tr class="border-t">
                        <td class="p-2">{{ optional($txn->player)->name ?? optional($txn->player)->player_name ?? '-' }}</td>
                        <td class="p-2">$ {{ number_format($txn->cashin, 2) }}</td>
                        <td class="p-2">$ {{ number_format($txn->cashout, 2) }}</td>
                        <td class="p-2">
                            $ {{ number_format(($txn->cashin ?? 0) - ($txn->cashout ?? 0), 2) }}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

    </div>
</div>
