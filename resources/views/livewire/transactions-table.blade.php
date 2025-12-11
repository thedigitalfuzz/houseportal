<div>
    <div class="mb-4 flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div class="flex justify-start md:justify-between">
            <livewire:transactions-create />
            <button
                onclick="Livewire.dispatch('open-create-transaction')"
                class="px-4 py-2 bg-green-600 text-white rounded"
            >
                New Transaction
            </button>
        </div>
        <div class="flex gap-2 flex-col md:flex-row">

                <div class="flex gap-2 items-center">
                    <input type="text" wire:model="searchInput" placeholder="Search by username or player name" class="border rounded px-2 py-1 w-full" />
                </div>
               <div class="flex flex-col gap-2 md:flex-row">
                   <div class="flex flex-col gap-2 md:flex-row">
                       <div>
                           @if($currentUser->role === 'admin')
                               <select wire:model="staff_id" class="border rounded px-2 py-1 w-full">
                                   <option value="">All Staffs</option>
                                   @foreach($allStaffs as $staff)
                                       <option value="{{ $staff->id }}">{{ $staff->staff_name }}</option>
                                   @endforeach
                               </select>
                           @endif
                       </div>
                       <div>
                           <select wire:model="game_id" class="border rounded px-2 py-1 w-full">
                               <option value="">All games</option>
                               @foreach($games as $g)
                                   <option value="{{ $g->id }}">{{ $g->name }}</option>
                               @endforeach
                           </select>
                       </div>
                   </div>


                   <div class="flex gap-2 flex-col md:flex-row">
                       <div class="flex flex-col md:flex-row gap-2">
                           <input type="date" wire:model="date_from" class="border rounded px-2 py-1 w-full" />
                           <input type="date" wire:model="date_to" class="border rounded px-2 py-1 w-full" />
                       </div>
                        <div>
                            <button wire:click="applySearch" class="px-4 py-1 bg-blue-600 text-white rounded">Search</button>

                        </div>

                   </div>
                 </div>

        </div>
    </div>
    <div class="grid grid-cols-1">
        <div class="bg-white rounded shadow overflow-x-auto">
            <table class="min-w-full table-auto">
                <thead class="bg-gray-50">
                <tr>
                    <th class="p-3 text-left">ID</th>
                    <th class="p-3 text-left">Username</th>
                    <th class="p-3 text-left">Player Name</th>
                    <th class="p-3 text-left">Assigned Staff</th>
                    <th class="p-3 text-left">Staff Facebook Profile</th>
                    <th class="p-3 text-left">Game</th>
                    <th class="p-3 text-left">Cashin</th>
                    <th class="p-3 text-left">Cashout</th>
                    <th class="p-3 text-left">Total</th>
                    <th class="p-3 text-left">Cash Tag</th>
                    <th class="p-3 text-left">Wallet Name</th>
                    <th class="p-3 text-left">Wallet Remarks</th>
                    <th class="p-3 text-left">Time</th>
                    <th class="px-4 py-2 text-right">Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($transactions as $t)
                    <tr class="border-t">
                        <td class="p-3">#{{ $t->id }}</td>
                        <td class="p-3">{{ $t->player->username ?? '-' }}</td>
                        <td class="p-3">{{ $t->player->player_name ?? '-' }}</td>
                        <td class="p-3">{{ $t->player->assignedStaff->staff_name ?? '-' }}</td>
                        <td class="p-3">{{ $t->player->assignedStaff->facebook_profile ?? '-' }}</td>
                        <td class="p-3">{{ $t->game->name ?? '-' }}</td>
                        <td class="p-3">$ {{ number_format($t->cashin,2) }}</td>
                        <td class="p-3">$ {{ number_format($t->cashout,2) }}</td>
                        <td class="p-3">$ {{ number_format($t->total_transaction, 2) }}</td>
                        <td class="p-3">{{ $t->cash_tag }}</td>
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
                        <td colspan="13" class="p-3 text-center">No transactions found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

    </div>

    <div class="mt-3">
        {{ $transactions->links() }}
    </div>

    {{-- EDIT MODAL --}}
    @if($editModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded shadow p-6 w-96">
                <h2 class="text-lg font-bold mb-4">Edit Transaction</h2>
                <div class="space-y-2">
                    <label for="player" class="text-xs">Player:</label>
                    <select name="player" wire:model="editPlayerId" class="border rounded w-full px-2 py-1">
                        <option value="">Select Player</option>
                        @foreach($players as $p)
                            <option value="{{ $p->id }}">{{ $p->username }}</option>
                        @endforeach
                    </select>

                    <label for="game" class="text-xs">Game:</label>
                    <select name="game" wire:model="editGameId" class="border rounded w-full px-2 py-1">
                        <option value="">Select Game</option>
                        @foreach($games as $g)
                            <option value="{{ $g->id }}">{{ $g->name }}</option>
                        @endforeach
                    </select>

                    <label for="cashin" class="text-xs">Cash In:</label>
                    <input type="number" name="cashin" wire:model="editCashin" placeholder="Cash In" class="border rounded w-full px-2 py-1" />

                    <label for="cashout" class="text-xs">Cash Out:</label>
                    <input type="number" name="cashout" wire:model="editCashout" placeholder="Cash Out" class="border rounded w-full px-2 py-1" />

                    <label for="bonus" class="text-xs">Bonus:</label>
                    <input type="number" name="bonus" wire:model="editBonusAdded" placeholder="Bonus Added" class="border rounded w-full px-2 py-1" />

                    <label for="cashtag" class="text-xs">Cash Tag:</label>
                    <input type="text" name="cashtag" wire:model="editCashTag" class="border rounded w-full px-2 py-1" placeholder="Cash Tag">

                    <label for="wallet" class="text-xs">Wallet:</label>
                    <input type="text" name="wallet" wire:model="editWalletName" class="border rounded w-full px-2 py-1" placeholder="Wallet Name">

                    <label for="remarks" class="text-xs">Remarks:</label>
                    <input type="text" name="remarks" wire:model="editWalletRemarks" class="border rounded w-full px-2 py-1" placeholder="Wallet Remarks">

                    <label for="notes" class="text-xs">Notes:</label>
                    <textarea name="notes" wire:model="editNotes" placeholder="Notes" class="border rounded w-full px-2 py-1"></textarea>
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
