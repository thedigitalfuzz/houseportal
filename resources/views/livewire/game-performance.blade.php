<div>
    {{-- Filters --}}
    <h2 class="text-2xl font-bold mb-4">Game Performance</h2>
    <div class="flex flex-wrap gap-2 mb-4">
        <select wire:model.defer="game_id" class="border rounded px-2 py-1">
            <option value="">All Games</option>
            @foreach($games as $g)
                <option value="{{ $g->id }}">{{ $g->name }}</option>
            @endforeach
        </select>

        <input type="date" wire:model.defer="searchDate"
               class="border rounded px-2 py-1">

        <button wire:click="$refresh"
                class="px-4 py-1 bg-blue-600 text-white rounded">
            Search
        </button>
    </div>

    {{-- Tabs --}}
    <div class="flex gap-2 mb-4">
        <button wire:click="$set('activeTab','daily')" class="px-3 py-1 rounded {{ $activeTab==='daily' ? 'bg-blue-600 text-white' : 'bg-gray-200' }}">Daily</button>
        <button wire:click="$set('activeTab','monthly')" class="px-3 py-1 rounded {{ $activeTab==='monthly' ? 'bg-blue-600 text-white' : 'bg-gray-200' }}">Monthly</button>
        <button wire:click="$set('activeTab','all')" class="px-3 py-1 rounded {{ $activeTab==='all' ? 'bg-blue-600 text-white' : 'bg-gray-200' }}">All Time</button>
    </div>

    {{-- Tables --}}
    @forelse($chunks as $chunk)
        <div class="grid grid-cols-1 mb-6">
        <div class="mb-6">
            <h3 class="font-bold mb-2">{{ $chunk['label'] }}</h3>

            <div class="overflow-x-auto bg-white shadow rounded">
                <table class="min-w-full table-auto">
                    <thead class="bg-gray-200">
                    <tr>
                        <th class="p-3 text-left">Game</th>
                        <th class="p-3 text-right">Transactions</th>
                        <th class="p-3 text-right">Used Points</th>
                        <th class="p-3 text-right">Cash In</th>
                        <th class="p-3 text-right">Cash Out</th>
                        <th class="p-3 text-right">Net</th>
                        <th class="p-3 text-left">Top Player</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($chunk['rows'] as $r)
                        <tr class="border-t">
                            <td class="p-3">{{ $r->game_name }}</td>
                            <td class="p-3 text-right">{{ $r->total_transactions }}</td>
                            <td class="p-3 text-right">{{ $r->used_points }}</td>
                            <td class="p-3 text-right">${{ number_format($r->total_cashin,2) }}</td>
                            <td class="p-3 text-right">${{ number_format($r->total_cashout,2) }}</td>
                            <td class="p-3 text-right {{ $r->total_net < 0 ? 'text-red-600' : 'text-green-600' }}">
                                {{ $r->total_net < 0 ? '-$'.number_format(abs($r->total_net),2) : '$'.number_format($r->total_net,2) }}
                            </td>
                            <td class="p-3">{{ $r->top_player ?? '-' }}</td>
                        </tr>

                    @endforeach
                    @php
                    $sumTransactions = collect($chunk['rows'])->sum('total_transactions');
                    $sumUsedpoints = collect($chunk['rows'])->sum('used_points');
                        $sumCashin = collect($chunk['rows'])->sum('total_cashin');
                        $sumCashout = collect($chunk['rows'])->sum('total_cashout');
                        $sumNet = collect($chunk['rows'])->sum('total_net');
                    @endphp

                    <tr class="font-bold bg-gray-200 border-t">
                        <td  class="p-3 text-left">TOTAL</td>

                        <td class="p-3 text-right">
                            {{ number_format($sumTransactions) }}
                        </td>
                        <td class="p-3 text-right">
                            {{ number_format($sumUsedpoints) }}
                        </td>
                        <td class="p-3 text-right text-green-600">
                            ${{ number_format($sumCashin, 2) }}
                        </td>

                        <td class="p-3 text-right text-red-600">
                            ${{ number_format($sumCashout, 2) }}
                        </td>

                        <td class="p-3 text-right {{ $sumNet < 0 ? 'text-red-600' : 'text-green-600' }}">
                            {{ $sumNet < 0
                                ? '-$'.number_format(abs($sumNet), 2)
                                : '$'.number_format($sumNet, 2)
                            }}
                        </td>

                        <td></td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        </div>
    @empty
        <div class="text-center p-4">No data found.</div>
    @endforelse
</div>
