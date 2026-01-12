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

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

        <!-- Recent Wallets -->
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

                <a href="{{ route('player-leaderboard') }}"
                   class="inline-block px-2 py-1 text-sm bg-blue-200 rounded-lg sm:px-3 sm:py-2 sm:text-base">
                    Leaderboard
                </a>
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


    </div>
    <div class="grid grid-cols-1  gap-4">
        <div wire:ignore class="bg-white shadow rounded p-4 mt-6" style="height: 600px;" >
            <h2 class="font-bold mb-4">
                Total Cash In – Last 10 Days
            </h2>

            <canvas id="cashinChart" style="height: 100%; width: 100%;"></canvas>
        </div>
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








</div>
