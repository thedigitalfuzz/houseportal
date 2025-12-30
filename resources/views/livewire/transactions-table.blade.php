<div>
    <div class="flex items-start md:items-center flex-col md:flex-row gap-2 mb-4 md:justify-between">
            <div class="flex justify-start md:justify-between">
                <livewire:transactions-create />
                <button onclick="Livewire.dispatch('open-create-transaction')" class="px-4 py-2 bg-green-600 text-white rounded">
                    New Transaction
                </button>
            </div>

        <div class="flex flex-col md:flex-row gap-2">
            <div class="flex gap-2 flex-col md:flex-row">

                    <input type="text" wire:model="searchInput" placeholder="Search by username or player name" class="border rounded px-2 py-1" />

                <div class="flex gap-2 flex-col md:flex-row">
                    @if($currentUser->role === 'admin')
                        <select wire:model="staff_id" class="border rounded px-2 py-1">
                            <option value="">All Staffs</option>
                            @foreach($allStaffs as $staff)
                                <option value="{{ $staff->id }}">{{ $staff->staff_name }}</option>
                            @endforeach
                        </select>
                    @endif

                    <select wire:model="game_id" class="border rounded px-2 py-1">
                        <option value="">All games</option>
                        @foreach($games as $g)
                            <option value="{{ $g->id }}">{{ $g->name }}</option>
                        @endforeach
                    </select>


                </div>

            </div>
            <input type="date" name="date-from" wire:model="date_from" class="border rounded px-2 py-1 w-full" />
            <input type="date" name="date-to" wire:model="date_to" class="border rounded px-2 py-1 w-full" />


        </div>
        <div class="flex flex-col gap-2 items-start">

                <select wire:model.live="wallet_agent" class="border rounded px-2 py-1 w-full">
                    <option value="">Agents</option>
                    @foreach($walletAgents as $a)
                        <option value="{{ $a }}">{{ $a }}</option>
                    @endforeach
                </select>

                <select wire:model.live="wallet_name" class="border rounded px-2 py-1 w-full">
                    <option value="">Wallet</option>
                    @foreach($walletNames as $w)
                        <option value="{{ $w }}">{{ $w }}</option>
                    @endforeach
                </select>

                <select wire:model.live="wallet_remarks" class="border rounded px-2 py-1 w-full">
                    <option value="">Remarks</option>
                    @foreach($walletRemarksOptions as $r)
                        <option value="{{ $r }}">{{ $r }}</option>
                    @endforeach
                </select>


        </div>
        <div>

            <button wire:click="applySearch" class="px-4 py-1 bg-blue-600 text-white rounded">Search</button>
        </div>
    </div>

<div class="border-gray-300 border-2 p-4">
    @forelse($transactionsDates as $date)
        <div class="grid grid-cols-1 mb-4">
            <h3 class="font-bold mb-2">Date: {{ $date }}</h3>
            <div class="bg-white rounded shadow overflow-x-auto">
                <table class="min-w-full table-auto">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="p-3 text-left">ID</th>
                        <th class="p-3 text-left">Username</th>
                        <th class="p-3 text-left">Player Name</th>
                        <th class="p-3 text-left">Player Profile</th>
                        <th class="p-3 text-left">Assigned Staff</th>
                        <th class="p-3 text-left">Staff Profile</th>
                        <th class="p-3 text-left">Game</th>
                        <th class="p-3 text-left">Transaction Type</th>
                        <th class="p-3 text-left">Amount</th>
                        <th class="p-3 text-left">Bonus</th>
                        <th class="p-3 text-left">Cash Tag</th>
                        <th class="p-3 text-left">Wallet Agent</th>
                        <th class="p-3 text-left">Wallet Name</th>
                        <th class="p-3 text-left">Wallet Remarks</th>
                        <th class="p-3 text-left">Time</th>
                        <th class="px-4 py-2 text-right">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($transactionsByDate[$date] ?? [] as $t)

                        <tr class="border-t">
                            <td class="p-3">#{{ $t->id }}</td>
                            <td class="p-3">{{ $t->player->username ?? '-' }}</td>
                            <td class="p-3">{{ $t->player->player_name ?? '-' }}</td>
                            <td class="p-3">{{ $t->player->facebook_profile ?? '-' }}</td>
                            <td class="p-3">{{ $t->player->assignedStaff->staff_name ?? '-' }}</td>
                            <td class="p-3">{{ $t->player->assignedStaff->facebook_profile ?? '-' }}</td>
                            <td class="p-3">{{ $t->game->name ?? '-' }}</td>
                            <td class="p-3">
                                {{ $t->cashin > 0 ? 'Cash In' : 'Cash Out' }}
                            </td>

                            <td class="p-3">
                                ${{ number_format($t->cashin > 0 ? $t->cashin : $t->cashout, 2) }}
                            </td>


                            <td class="p-3">$ {{ number_format($t->bonus_added,2) }}</td>
                            <td class="p-3">{{ $t->cash_tag }}</td>
                            <td class="p-3">{{ $t->agent }}</td>
                            <td class="p-3">{{ $t->wallet_name }}</td>
                            <td class="p-3">{{ $t->wallet_remarks }}</td>
                            <td class="p-3">{{ $t->transaction_time }}</td>
                            <td class="p-3 text-right flex justify-end gap-2">
                                @if($currentUser->role === 'admin' || ($t->player->staff_id === $currentUser->id))
                                    <button wire:click="editTransaction({{ $t->id }})" class="bg-blue-200 text-black px-3 py-1 rounded">Edit</button>
                                @endif
                                @if($this->canDelete())
                                    <button wire:click="confirmDelete({{ $t->id }})" class="bg-red-600 text-white px-3 py-1 rounded">Delete</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="15" class="p-3 text-center">No transactions found for this date.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div class="p-3 text-center">No transactions found.</div>
    @endforelse
</div>


    <div class="mt-3">
        {{ $transactionsDates->links() }}
    </div>

    {{-- EDIT MODAL --}}
    @if($editModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded shadow p-6 w-96">
                <h2 class="text-lg font-bold mb-2">Edit Transaction</h2>
                <div class="space-y-2">
                    <label class="text-xs">Player:</label>
                    <select wire:model="editPlayerId" class="border rounded w-full px-2 py-1">
                        <option value="">Select Player</option>
                        @foreach($players as $p)
                            <option value="{{ $p->id }}">{{ $p->username }}</option>
                        @endforeach
                    </select>

                    <label class="text-xs">Game:</label>
                    <select wire:model="editGameId" class="border rounded w-full px-2 py-1">
                        <option value="">Select Game</option>
                        @foreach($games as $g)
                            <option value="{{ $g->id }}">{{ $g->name }}</option>
                        @endforeach
                    </select>

                    <label class="text-xs">Type:</label>
                    <select wire:model="editTransactionType" class="border rounded w-full px-2 py-1">
                        <option value="">Select Type</option>
                        <option value="cashin">Cash In</option>
                        <option value="cashout">Cash Out</option>
                    </select>

                    <label class="text-xs">Amount:</label>
                    <input type="number" wire:model="editAmount" placeholder="Amount" class="border rounded w-full px-2 py-1" />

                    <label class="text-xs">Bonus:</label>
                    <input type="number" wire:model="editBonusAdded" placeholder="Bonus Added" class="border rounded w-full px-2 py-1" />
                    <label class="text-xs">Cash Tag:</label>
                    <input type="text" wire:model="editCashTag" placeholder="Cash Tag" class="border rounded w-full px-2 py-1" />
                    <!-- Agent -->
                    <div class="flex items-center justify-between">
                        <label class="text-xs">Wallet Agent:</label>
                        <select wire:model.live="editAgent" class="w-full border rounded p-2">
                            <option value="">Select Agent</option>
                            @foreach($editAgents as $a)
                                <option value="{{ $a }}">{{ $a }}</option>
                            @endforeach
                        </select>
                    </div>


                    <!-- Wallet Name -->
                    <div class="flex items-center justify-between">
                    <label class="text-xs">Wallet Name:</label>
                    <select wire:model.live="editWalletName" class="w-full border rounded p-2">
                        <option value="">Select Wallet Name</option>
                        @foreach($editWalletNames as $w)
                            <option value="{{ $w }}">{{ $w }}</option>
                        @endforeach
                    </select>
                    </div>
                    <!-- Wallet Remarks -->
                    <div class="flex items-center">
                    <label class="text-xs">Wallet Remarks:</label>
                    <select wire:model.live="editWalletRemarks" class="w-full border rounded p-2">
                        <option value="">Select Wallet Remarks</option>
                        @foreach($editWalletRemarksOptions as $r)
                            <option value="{{ $r }}">{{ $r }}</option>
                        @endforeach
                    </select>
                    </div>



                    <label class="text-xs">Notes:</label>
                    <textarea wire:model="editNotes" placeholder="Notes" class="border rounded w-full px-2 py-1"></textarea>
                    <label class="text-xs">Date:</label>
                    <input type="date" wire:model="editTransactionDate" class="border rounded w-full px-2 py-1" />
                </div>

                <div class="flex justify-end gap-2 mt-4">
                    <button wire:click="$set('editModal', false)" class="px-4 py-2 border rounded">Cancel</button>
                    <button wire:click="updateTransaction" class="px-4 py-2 bg-green-600 text-white rounded">Save Changes</button>
                </div>
            </div>
        </div>
    @endif
    {{-- DELETE CONFIRM MODAL --}}
    @if($deleteModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded shadow p-6 w-80 text-center">
                <h2 class="text-lg font-semibold mb-4">Confirm Delete</h2>
                <p>Are you sure you want to delete this transaction?</p>
                <div class="mt-4 flex justify-center gap-2">
                    <button wire:click="$set('deleteModal', false)" class="px-4 py-2 border rounded"> Cancel </button>
                    <button wire:click="deleteTransaction" class="px-4 py-2 bg-red-600 text-white rounded"> Yes, Delete </button>
                </div>
            </div>
        </div>
    @endif

</div>
