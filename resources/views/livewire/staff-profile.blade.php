<div class="p-4 space-y-6">

    {{-- Success message --}}
    @if(session()->has('success'))
        <div class="p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
    @endif
    <h2 class="font-bold text-xl mb-4">My Profile</h2>
    <div class="bg-gray-200 p-4 space-y-6">
        {{-- Staff Photo Display --}}
        <div class="flex flex-col items-start gap-2">
            <span class="font-bold">{{ $name }}</span>
            <span>Role:
                <span class="text-gray-800">
        {{ Auth::user()->role ?? Auth::user()->staff_role ?? 'Staff' }}
    </span>
            </span>
            @php
                $photoPath = asset('/images/hslogo.png');
                if(!empty($existingPhoto) && file_exists(storage_path('app/public/' . $existingPhoto))) {
                    $photoPath = asset('storage/' . $existingPhoto);
                }
            @endphp

            <img src="{{ $photoPath }}" class="w-28 h-28 rounded-full object-cover border-gray-800"/>
        </div>
        {{-- Profile Section --}}
        <div class="flex gap-6 flex-col md:flex-row">

            {{-- Staff Profile Form --}}
            <div class="bg-white shadow rounded p-4 max-w-md">
                <div class="space-y-3">

                    <div>
                        <label class="block font-medium">Name</label>
                        <input type="text" wire:model="name" class="w-full border rounded p-2"/>
                        <x-input-error :messages="$errors->get('name')" class="mt-1"/>
                    </div>

                    <div>
                        <label class="block font-medium">Current Password</label>
                        <input type="password" wire:model="current_password" class="w-full border rounded p-2"/>
                        <x-input-error :messages="$errors->get('current_password')" class="mt-1"/>
                    </div>

                    <div>
                        <label class="block font-medium">New Password</label>
                        <input type="password" wire:model="new_password" class="w-full border rounded p-2"/>
                        <x-input-error :messages="$errors->get('new_password')" class="mt-1"/>
                    </div>

                    <div>
                        <label class="block font-medium">Photo</label>
                        <input type="file" wire:model="photo" class="w-full border rounded p-2"/>

                        @if($photo)
                            <img src="{{ $photo->temporaryUrl() }}" class="mt-2 w-20 h-20 rounded-full object-cover"/>
                        @elseif($existingPhoto)
                            <img src="{{ asset('storage/' . $existingPhoto) }}" class="mt-2 w-20 h-20 rounded-full object-cover"/>
                        @endif
                        <x-input-error :messages="$errors->get('photo')" class="mt-1"/>
                    </div>

                    <div class="flex justify-end gap-2">
                        <button wire:click="saveProfile" class="px-4 py-2 bg-green-600 text-white rounded">Save</button>
                    </div>
                </div>
            </div>

            {{-- Monthly Time Summary & Highest Cash In/Out --}}
            <div class="flex-1 space-y-4">

                <h2 class="font-bold text-xl mb-2">Your {{ $monthLabel }} Summary</h2>

                {{-- Grid layout for all cards --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

                    {{-- Players Added --}}
                    <x-summary-card title="Players Added" :value="$staffMonthlyPlayersCount" :showDollar="false" color="blue"/>

                    {{-- Transactions Added --}}
                    <x-summary-card title="Transactions Added" :value="$staffMonthlyTransactionsCount" :showDollar="false" color="blue"/>

                    {{-- Cash In --}}
                    <x-summary-card title="Cash In" :value="number_format($staffMonthlyTotalCashin,2)" color="green"/>

                    {{-- Cash Out --}}
                    <x-summary-card title="Cash Out" :value="number_format($staffMonthlyTotalCashout,2)" color="red"/>

                    {{-- Net Amount --}}
                    <x-summary-card title="Net Amount"
                                    :value="number_format(abs($staffMonthlyTotalCashin-$staffMonthlyTotalCashout),2)"
                                    :isNegative="($staffMonthlyTotalCashin-$staffMonthlyTotalCashout)<0"/>

                    {{-- Highest Cash In --}}
                    <div class="bg-white shadow rounded p-4 flex flex-col justify-between">
                        <div class="text-gray-500 text-sm font-bold">Highest Cash In</div>
                        @if($highestMonthlyCashinTxn && $highestMonthlyCashinTxn->cashin > 0)
                            <div class="mt-1 text-sm font-semibold">{{ optional($highestMonthlyCashinTxn->player)->player_name ?? '-' }}</div>
                            <div class="text-xs text-gray-500">{{ optional($highestMonthlyCashinTxn->game)->name ?? '-' }}</div>
                            <div class="mt-2 text-lg font-bold text-green-600">${{ number_format($highestMonthlyCashinTxn->cashin,2) }}</div>
                        @else
                            <div class="mt-1 text-sm text-gray-400">No data</div>
                        @endif
                    </div>

                    {{-- Highest Cash Out --}}
                    <div class="bg-white shadow rounded p-4 flex flex-col justify-between">
                        <div class="text-gray-500 text-sm font-bold">Highest Cash Out</div>
                        @if($highestMonthlyCashoutTxn && $highestMonthlyCashoutTxn->cashout > 0)
                            <div class="mt-1 text-sm font-semibold">{{ optional($highestMonthlyCashoutTxn->player)->player_name ?? '-' }}</div>
                            <div class="text-xs text-gray-500">{{ optional($highestMonthlyCashoutTxn->game)->name ?? '-' }}</div>
                            <div class="mt-2 text-lg font-bold text-red-600">${{ number_format($highestMonthlyCashoutTxn->cashout,2) }}</div>
                        @else
                            <div class="mt-1 text-sm text-gray-400">No data</div>
                        @endif
                    </div>

                </div>

            </div>

        </div>

        {{-- All Time Summary & Highest Cash In/Out --}}
        <div class="flex-1 space-y-4">

            <h2 class="font-bold text-xl mb-2">Entire Summary</h2>

            {{-- Grid layout for all cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

                {{-- Players Added --}}
                <x-summary-card title="Players Added" :value="$staffPlayersCount" :showDollar="false" color="blue"/>

                {{-- Transactions Added --}}
                <x-summary-card title="Transactions Added" :value="$staffTransactionsCount" :showDollar="false" color="blue"/>

                {{-- Cash In --}}
                <x-summary-card title="Cash In" :value="number_format($staffTotalCashin,2)" color="green"/>

                {{-- Cash Out --}}
                <x-summary-card title="Cash Out" :value="number_format($staffTotalCashout,2)" color="red"/>

                {{-- Net Amount --}}
                <x-summary-card title="Net Amount"
                                :value="number_format(abs($staffTotalCashin-$staffTotalCashout),2)"
                                :isNegative="($staffTotalCashin-$staffTotalCashout)<0"/>

                {{-- Highest Cash In --}}
                <div class="bg-white shadow rounded p-4 flex flex-col justify-between">
                    <div class="text-gray-500 text-sm font-bold">Highest Cash In</div>
                    @if($highestCashinTxn && $highestCashinTxn->cashin > 0)
                        <div class="mt-1 text-sm font-semibold">{{ optional($highestCashinTxn->player)->player_name ?? '-' }}</div>
                        <div class="text-xs text-gray-500">{{ optional($highestCashinTxn->game)->name ?? '-' }}</div>
                        <div class="mt-2 text-lg font-bold text-green-600">${{ number_format($highestCashinTxn->cashin,2) }}</div>
                    @else
                        <div class="mt-1 text-sm text-gray-400">No data</div>
                    @endif
                </div>

                {{-- Highest Cash Out --}}
                <div class="bg-white shadow rounded p-4 flex flex-col justify-between">
                    <div class="text-gray-500 text-sm font-bold">Highest Cash Out</div>
                    @if($highestCashoutTxn && $highestCashoutTxn->cashout > 0)
                        <div class="mt-1 text-sm font-semibold">{{ optional($highestCashoutTxn->player)->player_name ?? '-' }}</div>
                        <div class="text-xs text-gray-500">{{ optional($highestCashoutTxn->game)->name ?? '-' }}</div>
                        <div class="mt-2 text-lg font-bold text-red-600">${{ number_format($highestCashoutTxn->cashout,2) }}</div>
                    @else
                        <div class="mt-1 text-sm text-gray-400">No data</div>
                    @endif
                </div>

            </div>

        </div>

        {{-- Players Table --}}
        <div class="grid grid-cols-1">
            <div class="bg-white shadow rounded p-4 overflow-x-auto">
                <h2 class="font-bold mb-3">My Players</h2>
                <table class="min-w-full table-auto">
                    <thead class="bg-gray-100">
                    <tr>
                        <th class="p-2 text-left">ID</th>
                        <th class="p-2 text-left">Username</th>
                        <th class="p-2 text-left">Player Name</th>
                        <th class="p-2 text-left">Social Media Link</th>
                        <th class="p-2 text-left">Assigned Agent</th>
                        <th class="p-2 text-left">Last Transaction Date</th>
                        <th class="p-2 text-left">Created Date</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($players as $player)
                        <tr class="border-t">
                            <td class="p-2">{{ $player->id }}</td>
                            <td class="p-2">{{ $player->username ?? '-' }}</td>
                            <td class="p-2">{{ $player->player_name }}</td>
                            <td class="p-2">
                                @if($player->facebook_profile)
                                    <a href="{{ $player->facebook_profile }}" target="_blank" class="text-blue-600 underline">View</a>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="p-3">{{ $player->assignedAgent?->player_agent_name ?? '-' }}</td>

                            <td class="p-2">
                                @php
                                    $lastTxn = $player->transactions()->latest('transaction_date')->first();
                                @endphp
                                @if($lastTxn)
                                    {{ $lastTxn->transaction_date->format('Y-m-d') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="p-2"> {{-- Created Date --}}
                                {{ $player->created_at->format('Y-m-d') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-2 text-center text-gray-500">No players found</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        {{-- Transactions Table --}}
        <div class="grid grid-cols-1">
            <div class="bg-white shadow rounded p-4 overflow-x-auto">
                <h2 class="font-bold mb-3">My Transactions</h2>
                <table class="min-w-full table-auto">
                    <thead class="bg-gray-100">
                    <tr>
                        <th class="p-2 text-left">ID</th>
                        <th class="p-2 text-left">Username</th>
                        <th class="p-2 text-left">Player Name</th>
                        <th class="p-2 text-left">Player Profile</th>
                        <th class="p-2 text-left">Game</th>
                        <th class="p-2 text-left">Transaction Type</th>
                        <th class="p-2 text-left">Amount</th>
                        <th class="p-2 text-left">Wallet Remarks</th>
                        <th class="p-2 text-left">Player Agent</th>
                        <th class="p-2 text-left">Date</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($transactions as $txn)
                        <tr class="border-t">
                            <td class="p-2">{{ $txn->id }}</td>
                            <td class="p-2">{{ optional($txn->player)->username ?? '-' }}</td>
                            <td class="p-2">{{ optional($txn->player)->player_name ?? '-' }}</td>
                            <td class="p-2">
                                @if(optional($txn->player)->facebook_profile)
                                    <a href="{{ $txn->player->facebook_profile }}" target="_blank" class="text-blue-600 underline">
                                        View
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="p-2">{{ optional($txn->game)->name ?? '-' }}</td>
                            <td class="p-2">{{ $txn->cashin > 0 ? 'Cash In' : 'Cash Out' }}</td>
                            <td class="p-2">${{ number_format($txn->cashin > 0 ? $txn->cashin : $txn->cashout, 2) }}</td>
                            <td class="p-2">{{ $txn->wallet_remarks ?? '-' }}</td>
                            <td class="p-2">
                                {{ optional($txn->player->assignedAgent)->player_agent_name ?? '-' }}
                            </td>
                            <td class="p-2">{{ $txn->transaction_date->format('Y-m-d') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="p-2 text-center text-gray-500">No transactions found</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>



</div>
