<div class="p-4">
    <div class="flex flex-col md:flex-row gap-2 items-start md:items-center justify-between">
        <h2 class="text-xl font-bold mb-4">Player Rankings</h2>
        <div class="flex flex-col gap-2 justify-start">
            <div class="mb-4">

            </div>
            <div class="mb-4 flex gap-2">
                <input
                    type="text"
                    wire:model="searchInput"
                    placeholder="Search player name..."
                    class="flex-1 px-3 py-2 border rounded focus:outline-none focus:ring w-full"
                >

                <button
                    wire:click="applySearch"
                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
                >
                    Search
                </button>

            </div>

        </div>


    </div>

    <div class="grid grid-cols-1">
        <a
            wire:click="toggleSort"
            class="cursor-pointer {{ $sortByDate ? 'text-green-600' : 'text-blue-600' }}"
        >
            {{ $sortByDate ? 'Sort By Rank' : 'Sort By Date' }}
        </a>
    <div class="bg-white rounded shadow overflow-x-auto">

        <table class="min-w-full table-auto">
            <thead class="bg-gray-100">
            <tr>
                <th class="p-3 text-left">Rank</th>
                <th class="p-3 text-left">Player Name</th>
                <th class="p-3 text-right">Total Cash In</th>
                <th class="p-3 text-right">Total Cash Out</th>
                <th class="p-3 text-right">Net Total</th>
                <th class="p-3 text-left">Last Played</th>
            </tr>
            </thead>

            <tbody>
            @forelse($rankings as $index => $row)
                <tr class="border-t">
                    <td class="p-3 font-semibold">
                        #{{ $row ->rank }}
                    </td>

                    <td class="p-3">
                        {{ $row->player_name }}
                    </td>

                    <td class="p-3 text-right">
                        ${{ number_format($row->total_cashin, 2) }}
                    </td>

                    <td class="p-3 text-right">
                        ${{ number_format($row->total_cashout, 2) }}
                    </td>
                    @php
                        $net = $row->total_cashin - $row->total_cashout;
                    @endphp

                    <td class="p-3 text-right font-semibold
    {{ $net >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $net < 0 ? '-' : '' }}${{ number_format(abs($net), 2) }}
                    </td>
                    <td class="p-3">
                        {{ $row->last_transaction_date ? \Carbon\Carbon::parse($row->last_transaction_date)->format('Y-m-d') : '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="p-4 text-center">
                        No ranking data available.
                    </td>
                </tr>
            @endforelse
            @if($rankings->count())
                <tr class="border-t bg-gray-100 font-bold">
                    <td class="p-3 text-right" colspan="2">
                        TOTAL
                    </td>

                    <td class="p-3 text-right text-green-700">
                        ${{ number_format($totals['cashin'], 2) }}
                    </td>

                    <td class="p-3 text-right text-red-700">
                        ${{ number_format($totals['cashout'], 2) }}
                    </td>

                    <td class="p-3 text-right
        {{ $totals['net'] >= 0 ? 'text-green-800' : 'text-red-800' }}">
                        ${{ number_format($totals['net'], 2) }}
                    </td>
                </tr>
            @endif

            </tbody>
        </table>
    </div>
    </div>
</div>
