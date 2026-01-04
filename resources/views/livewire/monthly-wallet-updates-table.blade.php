<div class="p-4 space-y-6">
    <h2 class="text-xl font-bold mb-4">Monthly Wallet Updates</h2>

    <div class="flex flex-wrap items-center justify-between md:justify-end gap-3 mb-4">
        <div class="flex flex-col md:flex-row gap-2 items-start md:items-center">
            <div class="flex gap-2 items-center">
                <select wire:model.defer="month" class="border rounded px-2 py-1">
                    <option value="">Select Month</option>
                    @foreach(range(1,12) as $m)
                        <option value="{{ $m }}">{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                    @endforeach
                </select>

                <select wire:model.defer="year" class="border rounded px-2 py-1">
                    <option value="">Select Year</option>
                    @foreach(range(now()->year - 5, now()->year + 1) as $y)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endforeach
                </select>
            </div>


            <button wire:click="search" class="bg-blue-600 text-white px-4 py-2 rounded">Search</button>
        </div>
    </div>

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
                        <th class="p-3 text-left">Agent</th>
                        <th class="p-3 text-left">Wallet Name</th>
                        <th class="p-3 text-left">Wallet Remarks</th>
                        <th class="p-3 text-right">Opening Balance</th>
                        <th class="p-3 text-right">Closing Balance</th>
                        <th class="p-3 text-right">Total Cash In</th>
                        <th class="p-3 text-right">Total Cash Out</th>
                        <th class="p-3 text-right">Net Transaction</th>
                    </tr>
                    </thead>

                    <tbody>
                    @php
                        $totalCashIn = 0;
                        $totalCashOut = 0;
                        $totalNet = 0;
                    @endphp

                    @forelse($monthChunk['wallets'] as $index => $wallet)
                        @php
                            $totalCashIn += $wallet['total_cashin'];
                            $totalCashOut += $wallet['total_cashout'];
                            $totalNet += $wallet['net_transaction'];
                        @endphp
                        <tr class="border-t">
                            <td class="p-3">{{ $index + 1 }}</td>
                            <td class="p-3">{{ $wallet['agent'] }}</td>
                            <td class="p-3">{{ $wallet['wallet_name'] }}</td>
                            <td class="p-3">{{ $wallet['wallet_remarks'] ?? '-' }}</td>
                            <td class="p-3 text-right">${{ number_format($wallet['opening_balance'], 2) }}</td>
                            <td class="p-3 text-right">${{ number_format($wallet['closing_balance'], 2) }}</td>
                            <td class="p-3 text-right text-green-600">${{ number_format($wallet['total_cashin'], 2) }}</td>
                            <td class="p-3 text-right text-red-600">${{ number_format($wallet['total_cashout'], 2) }}</td>
                            <td class="p-3 text-right {{ $wallet['net_transaction'] < 0 ? 'text-red-600' : 'text-green-600' }}">
                                {{ $wallet['net_transaction'] < 0 ? '-' : '' }}${{ number_format(abs($wallet['net_transaction']), 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-4 text-center">No wallets found for this month</td>
                        </tr>
                    @endforelse

                    @if(count($monthChunk['wallets']) > 0)
                        <tr class="font-bold border-t border-gray-300 bg-gray-50">
                            <td colspan="6" class="p-3 text-right">Total</td>
                            <td class="p-3 text-right text-green-600">${{ number_format($totalCashIn, 2) }}</td>
                            <td class="p-3 text-right text-red-600">${{ number_format($totalCashOut, 2) }}</td>
                            <td class="p-3 text-right {{ $totalNet < 0 ? 'text-red-600' : 'text-green-600' }}">
                                {{ $totalNet < 0 ? '-' : '' }}${{ number_format(abs($totalNet), 2) }}
                            </td>
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
