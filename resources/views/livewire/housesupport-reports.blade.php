<div>
    {{-- Filters --}}
    <div class="flex gap-2 mb-4 items-center">
        <input type="date" wire:model="searchDate" class="border p-2 rounded" placeholder="Search date">
        <button class="bg-blue-600 text-white px-4 py-2 rounded" wire:click="$refresh">Search</button>


    @if($searchDate)
            <button wire:click="exportPdf" class="bg-green-600 text-white px-4 py-2 rounded">Download PDF</button>
        @endif
    </div>


    {{-- Tabs --}}
    <div class="flex gap-2 p-2 border rounded border-gray-800 bg-blue-50 mb-6">
        @foreach(['daily','weekly','monthly','all'] as $tab)
            <button
                wire:click="setTab('{{ $tab }}')"
                class="px-4 py-2 rounded
                {{ $activeTab === $tab ? 'bg-yellow-400 text-black' : 'bg-gray-400 text-white'  }}">
                {{ ucfirst($tab) }}
            </button>
        @endforeach
    </div>

    {{-- Chunks --}}
    @forelse($chunks as $chunk)
        <div class="p-6 mb-6 space-y-6 border rounded border-gray-300 bg-gray-50 shadow">

            {{-- Chunk Title --}}
            <h2 class="text-lg font-bold mb-4">{{ $chunk['label'] }}</h2>

            {{-- Summary --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white p-4 shadow rounded">Transactions: <b>{{ $chunk['summary']['totalTransactions'] }}</b></div>
                <div class="bg-white p-4 shadow rounded">Cash In: <b>{{ number_format($chunk['summary']['totalCashin'],2) }}</b></div>
                <div class="bg-white p-4 shadow rounded">Cash Out: <b>{{ number_format($chunk['summary']['totalCashout'],2) }}</b></div>
                <div class="bg-white p-4 shadow rounded">Net: <b>{{ number_format($chunk['summary']['netAmount'],2) }}</b></div>
            </div>

            {{-- Players --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="font-bold mb-2">Top 5 Cashin Players</h3>
                    <ul class="bg-white shadow rounded divide-y">
                        @foreach($chunk['summary']['topCashinPlayers'] as $p)
                            <li class="p-2 flex justify-between">
                                <span>{{ $p->player_name }}</span>
                                <span>{{ number_format($p->total,2) }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div>
                    <h3 class="font-bold mb-2">Top 5 Cashout Players</h3>
                    <ul class="bg-white shadow rounded divide-y">
                        @foreach($chunk['summary']['topCashoutPlayers'] as $p)
                            <li class="p-2 flex justify-between">
                                <span>{{ $p->player_name }}</span>
                                <span>{{ number_format($p->total,2) }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            {{-- Games & Wallets --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white p-4 shadow rounded">
                    <b>Game with Most Cashin:</b><br>
                    {{ $chunk['summary']['topCashinGame']->name ?? '-' }} <br>
                    <span class="text-sm text-gray-600">
                        Amount: {{ number_format($chunk['summary']['topCashinGame']->amount ?? 0, 2) }}
                    </span>
                </div>

                <div class="bg-white p-4 shadow rounded">
                    <b>Game with Most Cashout:</b><br>
                    {{ $chunk['summary']['topCashoutGame']->name ?? '-' }} <br>
                    <span class="text-sm text-gray-600">
                        Amount: {{ number_format($chunk['summary']['topCashoutGame']->amount ?? 0, 2) }}
                    </span>
                </div>

                <div class="bg-white p-4 shadow rounded">
                    <b>Wallet with Most Cashin:</b><br>
                    {{ $chunk['summary']['topCashinWallet']
                        ? $chunk['summary']['topCashinWallet']->agent.' | '.$chunk['summary']['topCashinWallet']->wallet_name.' | '.$chunk['summary']['topCashinWallet']->wallet_remarks
                        : '-' }}
                    <br>
                    <span class="text-sm text-gray-600">
                        Amount: {{ number_format($chunk['summary']['topCashinWallet']->amount ?? 0, 2) }}
                    </span>
                </div>

                <div class="bg-white p-4 shadow rounded">
                    <b>Wallet with Most Cashout:</b><br>
                    {{ $chunk['summary']['topCashoutWallet']
                        ? $chunk['summary']['topCashoutWallet']->agent.' | '.$chunk['summary']['topCashoutWallet']->wallet_name.' | '.$chunk['summary']['topCashoutWallet']->wallet_remarks
                        : '-' }}
                    <br>
                    <span class="text-sm text-gray-600">
                        Amount: {{ number_format($chunk['summary']['topCashoutWallet']->amount ?? 0, 2) }}
                    </span>
                </div>
            </div>

            {{-- Wallet Summary --}}
            <div>
                <h3 class="font-bold mb-2">Wallet Summary</h3>
                <table class="w-full bg-white shadow rounded">
                    <thead class="bg-gray-100">
                    <tr>
                        <th class="p-2 text-left">Wallet</th>
                        <th class="p-2 text-right">Cashin</th>
                        <th class="p-2 text-right">Cashout</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($chunk['summary']['walletSummary'] as $w)
                        <tr class="border-t">
                            <td class="p-2">{{ $w->agent }} | {{ $w->wallet_name }} | {{ $w->wallet_remarks }}</td>
                            <td class="p-2 text-right">{{ number_format($w->cashin,2) }}</td>
                            <td class="p-2 text-right">{{ number_format($w->cashout,2) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Staff --}}
            <div>
                <h3 class="font-bold mb-2">Top 3 Staff Performance</h3>
                <table class="w-full bg-white shadow rounded">
                    <thead class="bg-gray-100">
                    <tr>
                        <th class="p-2 text-left">Staff</th>
                        <th class="p-2 text-right">Transactions</th>
                        <th class="p-2 text-right">Cashin</th>
                        <th class="p-2 text-right">Cashout</th>
                        <th class="p-2 text-right">Net</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($chunk['summary']['topStaffs'] as $s)
                        <tr class="border-t">
                            <td class="p-2">{{ $s->staff_name }}</td>
                            <td class="p-2 text-right">{{ $s->transactions }}</td>
                            <td class="p-2 text-right">{{ number_format($s->cashin,2) }}</td>
                            <td class="p-2 text-right">{{ number_format($s->cashout,2) }}</td>
                            <td class="p-2 text-right">{{ number_format($s->net,2) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    @empty
        <div class="text-center p-4">No data found.</div>
    @endforelse
</div>
