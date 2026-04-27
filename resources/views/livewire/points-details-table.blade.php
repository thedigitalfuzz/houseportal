<div class="p-4">

    {{-- TABS --}}
    <div class="flex flex-col md:flex-row gap-2 p-2 border rounded border-gray-800 bg-blue-50 mb-6">
        @foreach(['daily','monthly','yearly','all'] as $t)
            <button wire:click="setTab('{{ $t }}')"
                    class="px-4 py-2 rounded {{ $tab === $t ? 'bg-yellow-400 text-black' : 'bg-gray-400 text-white' }}">
                {{ ucfirst($t) }}
            </button>
        @endforeach
    </div>
<div class="grid grid-cols-1">
    <div class="flex gap-2 mb-3 overflow-x-auto">
        <button wire:click="setGame(null)"
                class="px-3 py-1 rounded {{ $selectedGame === null ? 'bg-blue-600 text-white' : 'bg-gray-300' }}">
            All Games
        </button>

        @foreach($games as $g)
            <button wire:click="setGame({{ $g->id }})"
                    class="px-3 py-1 rounded whitespace-nowrap {{ $selectedGame == $g->id ? 'bg-blue-600 text-white' : 'bg-gray-300' }}">
                {{ $g->name }}
            </button>
        @endforeach
    </div>
</div>

    {{-- GRID --}}
    <div class="grid grid-cols-1 gap-4">
        {{-- 1 TOTAL SUMMARY --}}
        <div class="border p-3 rounded bg-gray-50">

            <h2 class="font-bold mb-2">Total Summary ({{ $gameLabel }})</h2>

            <div class="max-h-[400px] overflow-y-auto border bg-white rounded shadow overflow-x-auto">
                <table class="w-full">
                    <thead class="sticky top-0 bg-gray-200 z-0">
                    <tr>
                        <th class="text-left p-4">Period</th>
                        <th class="text-right p-4">Cashin</th>
                        <th class="text-right p-4">Cashout</th>
                        <th class="text-right p-4">Net</th>
                        <th class="text-right p-4">Bonus Points</th>
                        <th class="text-right p-4 p-4">Used Points</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($totalSummary as $period => $d)
                        <tr class="border-t">
                            <td class="text-left p-4">{{ $period }}</td>
                            <td class="text-right p-4 text-green-600">{{ $d['cashin'] }}</td>
                            <td class="text-right p-4 text-red-600">{{ $d['cashout'] }}</td>
                            <td class="text-right p-4">{{ $d['net'] }}</td>
                            <td class="text-right p-4 font-bold">{{ $d['bonus'] }}</td>
                            <td class="text-right p-4 font-bold">{{ $d['used'] }}</td>
                        </tr>
                    @endforeach
                    @php
                        $totCashin = $totCashout = $totNet = $totBonus = $totUsed = 0;
                    @endphp

                    @foreach($totalSummary as $period => $d)
                        @php
                            $totCashin += $d['cashin'];
                            $totCashout += $d['cashout'];
                            $totNet += $d['net'];
                            $totBonus += $d['bonus'];
                            $totUsed += $d['used'];
                        @endphp
                    @endforeach

                    <tr class="bg-yellow-200 font-bold border-t-2">
                        <td class="p-4">TOTAL</td>
                        <td class="text-right p-4">{{ $totCashin }}</td>
                        <td class="text-right p-4">{{ $totCashout }}</td>
                        <td class="text-right p-4">{{ $totNet }}</td>
                        <td class="text-right p-4">{{ $totBonus }}</td>
                        <td class="text-right p-4">{{ $totUsed }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>



        {{-- 3 PLAYER SUMMARY --}}
        <div class="border p-3 rounded bg-gray-50 mb-6">
            <h2 class="font-bold mb-2">Player Ranking ({{ $gameLabel }})</h2>

            <div class="max-h-[400px] overflow-y-auto border bg-white rounded shadow overflow-x-auto">
                <table class="w-full">
                    <thead class="sticky top-0 bg-gray-200 z-0">
                    <tr>
                        <th class="text-left p-4">Rank</th>
                        <th class="text-left p-4">Player</th>
                        <th class="text-right p-4">Cashin</th>
                        <th class="text-right p-4">Cashout</th>
                        <th class="text-right p-4">Net</th>
                        <th class="text-right p-4">Bonus</th>
                        <th class="text-right p-4">Used</th>
                    </tr>
                    </thead>
                    <tbody>
                    @php $rank = 1; @endphp
                    @foreach($playerSummary as $p)
                        <tr class="border-t">
                            <td class="text-left p-4 font-bold">{{ $rank++ }}</td>
                            <td class="text-left p-4">{{ $p['player'] }}</td>
                            <td class="text-right p-4 text-green-600">{{ $p['cashin'] }}</td>
                            <td class="text-right p-4 text-red-600">{{ $p['cashout'] }}</td>
                            <td class="text-right p-4">{{ $p['net'] }}</td>
                            <td class="text-right p-4 font-bold">{{ $p['bonus'] }}</td>
                            <td class="text-right p-4 font-bold">{{ $p['used'] }}</td>
                        </tr>
                    @endforeach
                    @php
                        $totCashin = $totCashout = $totNet = $totBonus = $totUsed = 0;
                    @endphp

                    @foreach($playerSummary as $p)
                        @php
                            $totCashin += $p['cashin'];
                            $totCashout += $p['cashout'];
                            $totNet += $p['net'];
                            $totBonus += $p['bonus'];
                            $totUsed += $p['used'];
                        @endphp
                    @endforeach

                    <tr class="bg-yellow-200 font-bold border-t-2">
                        <td colspan="2" class="p-4">TOTAL</td>
                        <td class="text-right p-4">{{ $totCashin }}</td>
                        <td class="text-right p-4">{{ $totCashout }}</td>
                        <td class="text-right p-4">{{ $totNet }}</td>
                        <td class="text-right p-4">{{ $totBonus }}</td>
                        <td class="text-right p-4">{{ $totUsed }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
    <div class="grid grid-cols-1 gap-4">
        {{-- 4 WALLET SUMMARY --}}
        @php
            $periodKeys = array_keys($walletPeriods);
            $currentKey = $periodKeys[$walletPage] ?? null;
            $current = $walletPeriods[$currentKey] ?? null;
        @endphp

        <div class="border p-3 rounded bg-gray-50">
            <h2 class="font-bold mb-2">
                Wallet Summary for {{ $current['label'] ?? '' }} ({{ $gameLabel }})
            </h2>

            <div class="max-h-[400px] overflow-auto border bg-white rounded shadow relative">
                <table class="w-full">
                    <thead class="sticky top-0 bg-gray-200 z-0">
                    <tr>
                        <th class="text-left p-4 font-bold">Wallet</th>
                        <th class="text-left p-4 font-bold">Remarks</th>
                        <th class="text-right p-4 font-bold">Cashin</th>
                        <th class="text-right p-4 font-bold">Cashout</th>
                        <th class="text-right p-4 font-bold">Net</th>
                        <th class="text-right p-4 font-bold">Used</th>
                    </tr>
                    </thead>

                    <tbody>
                    @if($current)
                        @foreach($current['items'] as $w)
                            <tr class="border-t">
                                <td class="text-left p-4">{{ $w['wallet'] }}</td>
                                <td class="text-left p-4">{{ $w['remarks'] }}</td>
                                <td class="text-right p-4 text-green-600">{{ $w['cashin'] }}</td>
                                <td class="text-right p-4 text-red-600">{{ $w['cashout'] }}</td>
                                <td class="text-right p-4">{{ $w['net'] }}</td>
                                <td class="text-right p-4 font-bold">{{ $w['used'] }}</td>
                            </tr>
                        @endforeach
                        @php
                            $totCashin = $totCashout = $totNet = $totUsed = 0;
                        @endphp

                        @foreach($current['items'] as $w)
                            @php
                                $totCashin += $w['cashin'];
                                $totCashout += $w['cashout'];
                                $totNet += $w['net'];
                                $totUsed += $w['used'];
                            @endphp
                        @endforeach

                        <tr class="bg-yellow-200 font-bold border-t-2">
                            <td colspan="2" class="p-4">TOTAL</td>
                            <td class="text-right p-4">{{ $totCashin }}</td>
                            <td class="text-right p-4">{{ $totCashout }}</td>
                            <td class="text-right p-4">{{ $totNet }}</td>
                            <td class="text-right p-4">{{ $totUsed }}</td>
                        </tr>
                    @endif
                    </tbody>
                </table>
            </div>
            <div class="flex gap-2 mt-3 justify-between">
                @if($tab !== 'all')


                    <button wire:click="nextWalletReport"
                            class="px-3 py-1 bg-gray-300 rounded">
                        Previous
                    </button>
                    <button wire:click="prevWalletReport"
                            class="px-3 py-1 bg-gray-300 rounded">
                        Next
                    </button>
                @endif
            </div>
        </div>

    </div>
</div>
