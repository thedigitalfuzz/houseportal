<div>

    <div class="flex flex-col md:flex-row justify-between mb-4 gap-2">

        <div class="flex gap-2">

            <a href="{{route('wallet-performance')}}" class="px-4 py-2 bg-blue-600 text-white rounded">
                Daily Performance
            </a>

            <a href="{{route('overall-wallet-performance')}}" class="px-4 py-2 bg-green-600 text-white rounded">
                Overall Performance
            </a>

        </div>

        <div class="flex flex-wrap gap-2 items-center">
            <h4 class="text-gray-400">Search:</h4>
            {{-- YEAR --}}
            <select wire:model.live="year" class="border rounded px-2 py-1">
                <option value="">Year</option>
                @foreach($years as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>

            {{-- MONTH --}}
            <select wire:model.live="month" class="border rounded px-2 py-1">
                <option value="">Month</option>
                @for($i=1;$i<=12;$i++)
                    <option value="{{ $i }}">{{ $i }}</option>
                @endfor
            </select>

            <select wire:model.live="wallet_agent" class="border rounded px-2 py-1">
                <option value="">All Agents</option>
                @foreach($walletAgents as $a)
                    <option value="{{ $a }}">{{ $a }}</option>
                @endforeach
            </select>

            <select wire:model.live="wallet_name" class="border rounded px-2 py-1">
                <option value="">All Wallets</option>
                @foreach($walletNames as $w)
                    <option value="{{ $w }}">{{ $w }}</option>
                @endforeach
            </select>

            <select wire:model.live="wallet_remarks" class="border rounded px-2 py-1">
                <option value="">All Remarks</option>
                @foreach($walletRemarksOptions as $r)
                    <option value="{{ $r }}">{{ $r }}</option>
                @endforeach
            </select>

        </div>
    </div>

    @foreach($months as $month)
<div class="grid grid-cols-1">
    <div class="mb-6 border p-4 bg-white">

        <h3 class="font-bold mb-3">
            Month: {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('Y-F') }}
        </h3>

        <div class="bg-white rounded shadow overflow-x-auto">
            <table class="min-w-full table-auto">

                <thead class="bg-gray-100">
                <tr>
                    <th class="p-3 text-left">Rank</th>
                    <th class="p-3 text-left">Wallet Agent</th>
                    <th class="p-3 text-left">Wallet Name</th>
                    <th class="p-3 text-left">Wallet Remarks</th>
                    <th class="p-3 text-right">Transactions</th>
                    <th class="p-3 text-right">Cash In</th>
                    <th class="p-3 text-right">Cash Out</th>
                    <th class="p-3 text-right">Net</th>
                    <th class="p-3 text-right">Top Player</th>
                    <th class="p-3 text-right">Top Staff</th>
                </tr>
                </thead>

                <tbody>
                @foreach($data[$month] as $row)
                    <tr class="border-t">
                        <td class="p-3">{{ $row['rank'] }}</td>
                        <td class="p-3">{{ $row['agent'] }}</td>
                        <td class="p-3">{{ $row['wallet_name'] }}</td>
                        <td class="p-3">{{ $row['wallet_remarks'] }}</td>
                        <td class="p-3 text-right">{{ $row['count'] }}</td>
                        <td class="p-3 text-green-600 text-right">${{ number_format($row['cashin'],2) }}</td>
                        <td class="p-3 text-red-600 text-right">${{ number_format($row['cashout'],2) }}</td>
                        <td class="p-3 text-right">{{ number_format($row['net'],2) }}</td>
                        <td class="p-3 text-right">{{ $row['top_player'] }}</td>
                        <td class="p-3 text-right">{{ $row['top_staff'] }}</td>
                    </tr>
                @endforeach

                @php
                    $totalCashin = collect($data[$month])->sum('cashin');
                    $totalCashout = collect($data[$month])->sum('cashout');
                    $totalNet = $totalCashin - $totalCashout;
                    $topRow = $data[$month][0] ?? null;
                @endphp

                <tr class="bg-gray-100 font-semibold">
                    <td class="p-3 "></td>
                    <td class="p-3 " colspan="3">TOTAL</td>
                    <td class="p-3 text-right">{{ collect($data[$month])->sum('count') }}</td>
                    <td class="p-3 text-green-600 text-right">${{ number_format($totalCashin,2) }}</td>
                    <td class="p-3 text-red-600 text-right">${{ number_format($totalCashout,2) }}</td>
                    <td class="p-3 text-right">${{ number_format($totalNet,2) }}</td>
                    <td class="p-3 text-right"></td>
                    <td class="p-3 text-right"></td>
                </tr>

                </tbody>
            </table>
        </div>
    </div>

</div>

    @endforeach

    <div class="mt-3">
        {{ $months->links() }}
    </div>

</div>
