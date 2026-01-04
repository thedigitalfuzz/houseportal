<div> {{-- <-- Single root element --}}

    <div class="p-4 space-y-6">
        <h2 class="text-xl font-bold mb-4">Player Leaderboard</h2>
        <!-- Filters -->
        <div class="flex flex-wrap items-center justify-between md:justify-end gap-3">
            <div class="flex flex-col md:flex-row gap-2 items-start md:items-center">
                <div class="flex gap-2">
                    <select wire:model.defer="month" class="border rounded px-2 py-1">
                        <option value="">Select Month</option>
                        @foreach(range(1,12) as $m) <option value="{{ $m }}">
                            {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                        </option>
                        @endforeach
                    </select>


                    <select wire:model.defer="year" class="border rounded px-2 py-1">
                        <option value="">Select Year</option>
                        @foreach(range(now()->year - 5, now()->year + 1) as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach </select>
                </div>

                <button
                    wire:click="search"
                    class="bg-blue-600 text-white px-4 py-2 rounded">
                    Search
                </button>
            </div>

            <div class="flex gap-2" ">
                <a href="{{ route('daily-player-leaderboard') }}"
                   class="px-4 py-2 bg-indigo-600 text-white rounded">
                    Daily Leaderboard
                </a>
            </div>
        </div>

        <!-- Leaderboard Tables -->
        @forelse($leaderboards as $board)
            <div class="grid grid-cols-1">
                <div class="bg-white shadow rounded overflow-x-auto">
                    <div class="px-4 py-3 font-bold text-lg border-b">
                        {{ \Carbon\Carbon::create($board['year'], $board['month'])->format('F Y') }}
                    </div>

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
                        @forelse($board['rows'] as $index => $row)
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
                                    No data available
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="text-center text-gray-500">
                No leaderboard data found.
            </div>
        @endforelse

    </div>

</div> {{-- <-- End single root element --}}
