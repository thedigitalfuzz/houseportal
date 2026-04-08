<div class="p-4 space-y-6">

    {{-- ============================= --}}
    {{-- 🔷 SECTION 1: GLOBAL REPORT --}}
    {{-- ============================= --}}
    <div class="bg-gray-200 p-4 rounded">

        <h2 class="text-xl font-bold mb-3">Overall Reports</h2>

        <div class="text-sm text-gray-600">
            <div class="grid grid-cols-1">
                <div class="bg-white shadow rounded p-4 overflow-x-auto max-w-full">

                    <div class="flex gap-2 mb-4 bg-gray-100 p-1 rounded-lg overflow-x-auto no-scrollbar max-w-full">

                        <button wire:click="setMainTab('daily')"
                                class="flex-shrink-0 px-4 py-1.5 rounded-md text-sm font-medium transition
        {{ $activeMainTab === 'daily'
            ? 'bg-blue-600 text-white shadow'
            : 'text-gray-600 hover:bg-white hover:shadow-sm' }}">
                            Daily
                        </button>



                        <button wire:click="setMainTab('monthly')"
                                class="flex-shrink-0 px-4 py-1.5 rounded-md text-sm font-medium transition
        {{ $activeMainTab === 'monthly'
            ? 'bg-blue-600 text-white shadow'
            : 'text-gray-600 hover:bg-white hover:shadow-sm' }}">
                            Monthly
                        </button>

                        <button wire:click="setMainTab('all')"
                                class="px-4 py-1.5 rounded-md text-sm font-medium transition
        {{ $activeMainTab === 'all'
            ? 'bg-blue-600 text-white shadow'
            : 'text-gray-600 hover:bg-white hover:shadow-sm' }}">
                            All Time
                        </button>

                    </div>

                    @php
                        $rows = $this->getStaffPerformanceRows();
                    @endphp

                    <table class="min-w-[700px] w-full table-auto">
                        <thead class="bg-gray-100 sticky top-0 z-10">
                        <tr>
                            <th class="p-2 text-left">Staff</th>
                            <th class="p-2 text-right">Players Added</th>
                            <th class="p-2 text-right">Transactions</th>
                            <th class="p-2 text-right">Cashin</th>
                            <th class="p-2 text-right">Cashout</th>
                            <th class="p-2 text-right">Net</th>
                        </tr>
                        </thead>

                        <tbody>
                        @foreach($rows as $s)
                            <tr class="border-t">
                                <td class="p-2">{{ $s->staff_name }}</td>
                                <td class="p-2 text-right">{{ $s->players_added }}</td>
                                <td class="p-2 text-right">{{ $s->transactions }}</td>
                                <td class="p-2 text-right">${{ number_format($s->cashin,2) }}</td>
                                <td class="p-2 text-right">${{ number_format($s->cashout,2) }}</td>
                                <td class="p-2 text-right {{ $s->net < 0 ? 'text-red-600' : 'text-green-600' }}">
                                    {{ $s->net < 0
                                        ? '-$' . number_format(abs($s->net), 2)
                                        : '$' . number_format($s->net, 2) }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                </div>
            </div>

        </div>

    </div>

    {{-- ============================= --}}
    {{-- 🔷 SECTION 2: STAFF TABS --}}
    {{-- ============================= --}}
    <div class="grid grid-cols-1">
            <div class="bg-gray-200 p-4 rounded">

        <h2 class="text-xl font-bold mb-3">Staff Performance</h2>

        <div class="flex gap-2 overflow-x-auto pb-2">
            @foreach($staffs as $staff)
                <button
                    wire:click="setStaffTab({{ $staff->id }})"
                    class="px-4 py-2 rounded whitespace-nowrap
                    {{ $activeStaffTab == $staff->id ? 'bg-blue-600 text-white' : 'bg-white border' }}">
                    {{ $staff->staff_name }}
                </button>
            @endforeach
        </div>

        {{-- ============================= --}}
        {{-- 🔷 STAFF CONTENT --}}
        {{-- ============================= --}}
        @if($activeStaffTab)

            @php
                $daily = $this->getStaffSummary($activeStaffTab,'daily');
                $monthly = $this->getStaffSummary($activeStaffTab,'monthly');
                $all = $this->getStaffSummary($activeStaffTab,'all');
            @endphp

            {{-- ============================= --}}
            {{-- 🔷 SUMMARIES --}}
            {{-- ============================= --}}

            {{-- TODAY --}}
            <div class="mt-4">
                <h3 class="font-bold text-lg mb-2">Today's Summary ({{ now()->format('Y-m-d') }})</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                    <x-summary-card title="Transactions" :value="$daily['transactions']" :showDollar="false" color="blue"/>
                    <x-summary-card title="Players" :value="$daily['players']" :showDollar="false" color="blue"/>
                    <x-summary-card title="Cash In" :value="number_format($daily['cashin'],2)" color="green"/>
                    <x-summary-card title="Cash Out" :value="number_format($daily['cashout'],2)" color="red"/>
                    <x-summary-card
                        title="Net"
                        :value="$daily['net'] < 0
        ? '-$' . number_format(abs($daily['net']), 2)
        : '$' . number_format($daily['net'], 2)"
                        :isNegative="$daily['net'] < 0"
                        :showDollar="false"
                    />
                </div>
            </div>

            {{-- MONTHLY --}}
            <div class="mt-6">
                <h3 class="font-bold text-lg mb-2">Monthly Summary ({{ now()->format('F Y') }})</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                    <x-summary-card title="Transactions" :value="$monthly['transactions']" :showDollar="false" color="blue"/>
                    <x-summary-card title="Players" :value="$monthly['players']" :showDollar="false" color="blue"/>
                    <x-summary-card title="Cash In" :value="number_format($monthly['cashin'],2)" color="green"/>
                    <x-summary-card title="Cash Out" :value="number_format($monthly['cashout'],2)" color="red"/>
                    <x-summary-card
                        title="Net"
                        :value="$monthly['net'] < 0
        ? '-$' . number_format(abs($monthly['net']), 2)
        : '$' . number_format($monthly['net'], 2)"
                        :isNegative="$monthly['net'] < 0"
                        :showDollar="false"
                    />
                </div>
            </div>

            {{-- ALL TIME --}}
            <div class="mt-6">
                <h3 class="font-bold text-lg mb-2">All Time Summary</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                    <x-summary-card title="Transactions" :value="$all['transactions']" :showDollar="false" color="blue"/>
                    <x-summary-card title="Players" :value="$all['players']" :showDollar="false" color="blue"/>
                    <x-summary-card title="Cash In" :value="number_format($all['cashin'],2)" color="green"/>
                    <x-summary-card title="Cash Out" :value="number_format($all['cashout'],2)" color="red"/>
                    <x-summary-card
                        title="Net"
                        :value="$all['net'] < 0
        ? '-$' . number_format(abs($all['net']), 2)
        : '$' . number_format($all['net'], 2)"
                        :isNegative="$all['net'] < 0"
                        :showDollar="false"
                    />
                </div>
            </div>

            {{-- ============================= --}}
            {{-- 🔷 STAFF TABLE --}}
            {{-- ============================= --}}
            <div class="mt-6 bg-white shadow rounded p-4 overflow-auto">

                <h3 class="font-bold mb-3">Staff Transactions Table</h3>

                <div class="flex gap-2 mb-4 bg-gray-100 p-1 rounded-lg overflow-x-auto no-scrollbar max-w-full">

                    <button wire:click="setStaffTableTab('daily')"
                            class="flex-shrink-0 px-4 py-1.5 rounded-md text-sm font-medium transition
        {{ $activeStaffTableTab === 'daily'
            ? 'bg-blue-600 text-white shadow'
            : 'text-gray-600 hover:bg-white hover:shadow-sm' }}">
                        Daily
                    </button>

                    <button wire:click="setStaffTableTab('monthly')"
                            class="flex-shrink-0 px-4 py-1.5 rounded-md text-sm font-medium transition
        {{ $activeStaffTableTab === 'monthly'
            ? 'bg-blue-600 text-white shadow'
            : 'text-gray-600 hover:bg-white hover:shadow-sm' }}">
                        Monthly
                    </button>

                    <button wire:click="setStaffTableTab('yearly')"
                            class="flex-shrink-0 px-4 py-1.5 rounded-md text-sm font-medium transition
        {{ $activeStaffTableTab === 'yearly'
            ? 'bg-blue-600 text-white shadow'
            : 'text-gray-600 hover:bg-white hover:shadow-sm' }}">
                        Yearly
                    </button>

                </div>

                @php
                    $rows = $this->getStaffTable($activeStaffTab);
                    $tTxn=0;$tPlayers=0;$tIn=0;$tOut=0;$tNet=0;
                @endphp

                <div class="overflow-y-auto border rounded"
                     style="max-height: 460px; scrollbar-width: thin;">
                    <table class="min-w-full table-auto">
                        <thead class="bg-gray-100 sticky top-0">
                        <tr>
                            <th class="p-2 text-left">Period</th>
                            <th class="p-2 text-left">Transactions</th>
                            <th class="p-2 text-left">Players</th>
                            <th class="p-2 text-left">Cash In</th>
                            <th class="p-2 text-left">Cash Out</th>
                            <th class="p-2 text-left">Net</th>
                        </tr>
                        </thead>

                        <tbody>
                        @foreach($rows as $r)
                            <tr class="border-t">
                                <td class="p-2">{{ $r['label'] }}</td>
                                <td class="p-2">{{ $r['transactions'] }}</td>
                                <td class="p-2">{{ $r['players'] }}</td>
                                <td class="p-2">${{ number_format($r['cashin'],2) }}</td>
                                <td class="p-2">${{ number_format($r['cashout'],2) }}</td>
                                <td class="p-2 font-semibold">
                                    {{ $r['net'] < 0
                                        ? '-$' . number_format(abs($r['net']), 2)
                                        : '$' . number_format($r['net'], 2) }}
                                </td>
                            </tr>

                            @php
                                $tTxn += $r['transactions'];
                                $tPlayers += $r['players'];
                                $tIn += $r['cashin'];
                                $tOut += $r['cashout'];
                                $tNet += $r['net'];
                            @endphp
                        @endforeach

                        {{-- TOTAL ROW --}}
                        <tr class="bg-gray-200 font-bold">
                            <td class="p-2">TOTAL</td>
                            <td class="p-2">{{ $tTxn }}</td>
                            <td class="p-2">{{ $tPlayers }}</td>
                            <td class="p-2">${{ number_format($tIn,2) }}</td>
                            <td class="p-2">${{ number_format($tOut,2) }}</td>
                            <td class="p-2 ">
                                {{ $tNet < 0
                                    ? '-$' . number_format(abs($tNet), 2)
                                    : '$' . number_format($tNet, 2) }}
                            </td>
                        </tr>

                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ============================= --}}
            {{-- 🔷 MY PLAYERS --}}
            {{-- ============================= --}}
            <div class="mt-6 bg-white shadow rounded p-4">
                <h3 class="font-bold mb-3">Staff Players</h3>

                <div class="max-h-[400px] overflow-y-auto">
                    <table class="min-w-full table-auto">
                        <thead class="bg-gray-100 sticky top-0">
                        <tr>
                            <th class="p-2 text-left">ID</th>
                            <th class="p-2 text-left">Username</th>
                            <th class="p-2 text-left">Player Name</th>
                            <th class="p-2 text-left">Agent</th>
                            <th class="p-2 text-left">Created</th>
                        </tr>
                        </thead>

                        <tbody>
                        @foreach($this->getStaffPlayers($activeStaffTab) as $player)
                            <tr class="border-t">
                                <td class="p-2">{{ $player->id }}</td>
                                <td class="p-2">{{ $player->username }}</td>
                                <td class="p-2">{{ $player->player_name }}</td>
                                <td class="p-2">{{ $player->assignedAgent?->player_agent_name ?? '-' }}</td>
                                <td class="p-2">{{ $player->created_at->format('Y-m-d') }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ============================= --}}
            {{-- 🔷 MY TRANSACTIONS --}}
            {{-- ============================= --}}
            <div class="mt-6 bg-white shadow rounded p-4">
                <h3 class="font-bold mb-3">Staff Transactions</h3>

                <div class="max-h-[400px] overflow-y-auto">
                    <table class="min-w-full table-auto">
                        <thead class="bg-gray-100 sticky top-0">
                        <tr>
                            <th class="p-2 text-left">SN</th>
                            <th class="p- text-left">Player</th>
                            <th class="p-2 text-left">Game</th>
                            <th class="p-2 text-left">Type</th>
                            <th class="p-2 text-left">Amount</th>
                            <th class="p-2 text-left">Date</th>
                        </tr>
                        </thead>

                        <tbody>
                        @foreach($this->getStaffTransactions($activeStaffTab) as $txn)
                            <tr class="border-t">
                                <td class="p-2">{{ $loop->iteration }}</td>
                                <td class="p-2">{{ $txn->player->player_name ?? '-' }}</td>
                                <td class="p-2">{{ $txn->game->name ?? '-' }}</td>
                                <td class="p-2">{{ $txn->cashin > 0 ? 'Cash In' : 'Cash Out' }}</td>
                                <td class="p-2">
                                    ${{ number_format($txn->cashin > 0 ? $txn->cashin : $txn->cashout,2) }}
                                </td>
                                <td class="p-2">{{ $txn->transaction_date->format('Y-m-d') }}</td>
                            </tr>

                        @endforeach

                        </tbody>

                    </table>

                </div>
                <div class="grid grid-cols-1">
                    <div class="bg-gray-200 flex flex-col md:flex-row justify-center">
                        <div class="flex gap-2">
                            <div class="p-2 font-bold">TOTAL CASHIN:</div>

                            <div class="p-2 font-bold text-green-600">${{number_format($all['cashin'],2) }}</div>
                        </div>
                        <div class="flex gap-2">
                            <div class="p-2 font-bold">TOTAL CASHOUT:</div>

                            <div class="p-2 font-bold text-red-600">${{number_format(abs($all['cashout']),2) }}</div>
                        </div>
                        <div class="flex gap-2">
                            <div class="p-2 font-bold">TOTAL NET:</div>


                            <div class="p-2 font-bold  {{ $all['net'] < 0 ? 'text-red-600' : 'text-green-600' }}">
                                {{ $all['net'] < 0
                                    ? '-$' . number_format(abs($all['net']), 2)
                                    : '$' . number_format($all['net'], 2) }}
                            </div>
                        </div>

                    </div>
                </div>

            </div>

        @endif

    </div>
    </div>
</div>
