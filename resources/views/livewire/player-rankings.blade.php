<div class="p-4">
    <div class="flex flex-col md:flex-row gap-2 md:items-center justify-between">
        <h2 class="text-xl font-bold mb-4">Player Rankings</h2>
        <div class="flex items-center gap-2">
            <a href="{{ route('player-leaderboard') }}"
               class="px-4 py-2 bg-indigo-600 text-white rounded">
                Player Leaderboard
            </a>
            <a href="{{ route('players.index') }}"
               class="px-4 py-2 bg-gray-700 text-white rounded">
                Back
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1">
    <div class="bg-white rounded shadow overflow-x-auto">
        <table class="min-w-full table-auto">
            <thead class="bg-gray-100">
            <tr>
                <th class="p-3 text-left">Rank</th>
                <th class="p-3 text-left">Player Name</th>
                <th class="p-3 text-right">Total Cash In</th>
                <th class="p-3 text-right">Total Cash Out</th>
            </tr>
            </thead>

            <tbody>
            @forelse($rankings as $index => $row)
                <tr class="border-t">
                    <td class="p-3 font-semibold">
                        #{{ $index + 1 }}
                    </td>

                    <td class="p-3">
                        {{ $row->player_name }}
                    </td>

                    <td class="p-3 text-right text-green-600">
                        ${{ number_format($row->total_cashin, 2) }}
                    </td>

                    <td class="p-3 text-right text-red-600">
                        ${{ number_format($row->total_cashout, 2) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="p-4 text-center">
                        No ranking data available.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    </div>
</div>
