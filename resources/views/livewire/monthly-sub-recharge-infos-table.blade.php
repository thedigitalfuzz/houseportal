<div class="p-4 space-y-6">
    <h2 class="text-xl font-bold mb-4">Monthly Subdistributor Recharge Info</h2>

    <!-- SEARCH -->
    <div class="flex flex-wrap items-center justify-between md:justify-end gap-3 mb-4">
        <div class="flex flex-col md:flex-row gap-2 items-start md:items-center">
            <div class="flex gap-2 items-center">
                <select wire:model.defer="month" class="border rounded px-2 py-1">
                    <option value="">Select Month</option>
                    @foreach(range(1,12) as $m)
                        <option value="{{ $m }}">
                            {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                        </option>
                    @endforeach
                </select>

                <select wire:model.defer="year" class="border rounded px-2 py-1">
                    <option value="">Select Year</option>
                    @foreach(range(now()->year - 5, now()->year + 1) as $y)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endforeach
                </select>
            </div>

            <button wire:click="search" class="bg-blue-600 text-white px-4 py-2 rounded">
                Search
            </button>
        </div>
    </div>

    <!-- MONTHLY TABLES -->
    @forelse($monthlyData as $monthChunk)
        <div class="grid grid-cols-1 mb-6">
            <div class="bg-white shadow rounded overflow-x-auto">
                <div class="px-4 py-3 font-bold text-lg border-b">
                    {{ \Carbon\Carbon::createFromFormat('Y-m', $monthChunk['month'])->format('F Y') }}
                </div>

                <table class="min-w-full table-auto">
                    <thead class="bg-gray-100">
                    <tr>
                        <th class="p-3 text-left">S/N</th>
                        <th class="p-3 text-left">Game</th>
                        <th class="p-3 text-left">Subdistributor</th>
                        <th class="p-3 text-right">Recharge Amount</th>
                        <th class="p-3 text-right">Last Recharge Date</th>
                    </tr>
                    </thead>

                    <tbody>
                    @php $total = 0; @endphp

                    @forelse($monthChunk['rows'] as $index => $row)
                        @php $total += $row['total_recharge']; @endphp
                        <tr class="border-t">
                            <td class="p-3">{{ $index + 1 }}</td>
                            <td class="p-3">{{ $row['game_name'] }}</td>
                            <td class="p-3">{{ $row['sub_name'] }}</td>
                            <td class="p-3 text-right">
                                $ {{ number_format($row['total_recharge'], 2) }}
                            </td>
                            <td class="p-3 text-right">
                                {{ \Carbon\Carbon::parse($row['last_recharge_date'])->format('Y-m-d') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-4 text-center">
                                No recharge data for this month
                            </td>
                        </tr>
                    @endforelse

                    @if(count($monthChunk['rows']) > 0)
                        <tr class="font-bold border-t bg-gray-50">
                            <td colspan="3" class="p-3 text-right">Total</td>
                            <td class="p-3 text-right">
                                $ {{ number_format($total, 2) }}
                            </td>
                            <td></td>
                        </tr>
                    @endif
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div class="text-center text-gray-500">No monthly data found.</div>
    @endforelse

</div>
