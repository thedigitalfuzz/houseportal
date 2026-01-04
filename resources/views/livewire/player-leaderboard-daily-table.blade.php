<div class="p-4 space-y-8">
    <h2 class="text-2xl font-bold mb-4">Player Leaderboard (Daily)</h2>
    <div class="flex gap-2 mb-4 flex-col md:flex-row">
        <input type="text" wire:model="searchPlayer" placeholder="Search Player" class="border p-2 rounded flex-1">
        <input type="date" wire:model="searchDate" class="border p-2 rounded">
        <select wire:model="searchGame" class="border p-2 rounded">
            <option value="">All Games</option>
            @foreach($games as $game)
                <option value="{{ $game->id }}">{{ $game->name }}</option>
            @endforeach
        </select>
        <button wire:click="$refresh" class="bg-blue-600 text-white px-4 py-2 rounded">Search</button>
    </div>
    @foreach($chunks as $chunk)
        <div class="grid grid-cols-1 mb-6">
        <div class="bg-white shadow rounded overflow-x-auto">

            <div class="px-4 py-3 font-bold border-b">
                {{ $chunk['label'] }}
            </div>

            <table class="min-w-full table-auto">
                <thead class="bg-gray-100">
                <tr>
                    <th class="p-3 text-left">Rank</th>
                    <th class="p-3 text-left">Player Name</th>
                    <th class="p-3 text-left">Username</th>
                    <th class="p-3 text-left">Games</th>
                    <th class="p-3 text-right">Transactions</th>
                    <th class="p-3 text-right">Cashin</th>
                    <th class="p-3 text-right">Cashout</th>
                    <th class="p-3 text-right">Net</th>
                </tr>
                </thead>

                <tbody>
                @foreach($chunk['rows'] as $rank => $row)

                    {{-- PLAYER TOTAL ROW --}}
                    <tr class="bg-gray-50 font-bold border-t">
                        <td class="p-3">
                            #{{ $fixedRanks[$chunk['date']][$row['player_name']] ?? '-' }}
                        </td>

                        <td class="p-3">{{ $row['player_name'] }}</td>
                        <td></td>
                        <td></td>
                        <td class="p-3 text-right">{{ $row['total_transactions'] }}</td>
                        <td class="p-3 text-right text-green-600">${{ number_format($row['total_cashin'], 2) }}</td>
                        <td class="p-3 text-right text-red-600">${{ number_format($row['total_cashout'], 2) }}</td>
                        @php
                            $net = $row['net'];
                        @endphp

                        <td class="p-3 text-right font-bold
    {{ $net < 0 ? 'text-red-600' : ($net > 0 ? 'text-green-600' : 'text-gray-600') }}">
                            {{ $net < 0 ? '-' : '' }}${{ number_format(abs($net), 2) }}
                        </td>
                    </tr>

                    {{-- USERNAME ROWS --}}
                    @foreach($row['usernames'] as $u)
                        <tr class="border-t">
                            <td></td>
                            <td class="p-3 pl-8 text-gray-600">↳</td>
                            <td class="p-3 text-left">{{ $u['username'] }}</td>
                            <td class="p-3 text-left">{{ $u['game'] }}</td>
                            <td class="p-3 text-right">{{ $u['transactions'] }}</td>
                            <td class="p-3 text-right">${{ number_format($u['cashin'], 2) }}</td>
                            <td class="p-3 text-right">${{ number_format($u['cashout'], 2) }}</td>
                            @php
                                $net = $u['net'];
                            @endphp

                            <td class="p-3 text-right font-semibold">
                                {{ $net < 0 ? '-' : '' }}${{ number_format(abs($net), 2) }}
                            </td>
                        </tr>
                    @endforeach

                @endforeach

                {{-- DATE TOTAL --}}
                <tr class="bg-gray-200 font-bold border-t">
                    <td colspan="4" class="p-3 text-right"> TOTAL</td>
                    <td class="p-3 text-right">{{ $chunk['totals']['transactions'] }}</td>
                    <td class="p-3 text-right text-green-600">${{ number_format($chunk['totals']['cashin'], 2) }}</td>
                    <td class="p-3 text-right text-red-600">${{ number_format($chunk['totals']['cashout'], 2) }}</td>
                    @php
                        $net = $chunk['totals']['net'];
                    @endphp

                    <td class="p-3 text-right font-bold
    {{ $net < 0 ? 'text-red-600' : ($net > 0 ? 'text-green-600' : 'text-gray-600') }}">
                        {{ $net < 0 ? '-' : '' }}${{ number_format(abs($net), 2) }}
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        </div>
    @endforeach
</div>
