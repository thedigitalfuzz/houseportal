<div>
    <div class="mb-4 flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div class="flex gap-2">
            <input type="text" wire:model="searchInput" placeholder="Search player username" class="border rounded px-2 py-1" />
            <button wire:click="applySearch" class="px-4 py-1 bg-blue-600 text-white rounded">Search</button>

            <select wire:model="game_id" class="border rounded px-2 py-1">
                <option value="">All games</option>
                @foreach($games as $g)
                    <option value="{{ $g->id }}">{{ $g->name }}</option>
                @endforeach
            </select>

            <input type="date" wire:model="date_from" class="border rounded px-2 py-1" />
            <input type="date" wire:model="date_to" class="border rounded px-2 py-1" />
        </div>
    </div>

    <div class="bg-white rounded shadow overflow-x-auto">
        <table class="min-w-full table-auto">
            <thead class="bg-gray-50">
            <tr>
                <th class="p-3 text-left">ID</th>
                <th class="p-3 text-left">Player</th>
                <th class="p-3 text-left">Game</th>
                <th class="p-3 text-left">Cashin</th>
                <th class="p-3 text-left">Cashout</th>
                <th class="p-3 text-left">Rem. Balance</th>
                <th class="p-3 text-left">Time</th>
                <th class="px-4 py-2 text-right">Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($transactions as $t)
                <tr class="border-t">
                    <td class="p-3">#{{ $t->id }}</td>
                    <td class="p-3">{{ $t->player->username ?? '-' }}</td>
                    <td class="p-3">{{ $t->game->name ?? '-' }}</td>
                    <td class="p-3">₨ {{ number_format($t->cashin,2) }}</td>
                    <td class="p-3">₨ {{ number_format($t->cashout,2) }}</td>
                    <td class="p-3">₨ {{ number_format($t->player->balance ?? 0,2) }}</td>
                    <td class="p-3">{{ $t->transaction_time }}</td>

                    <td class="p-3 text-right flex justify-end gap-2">
                        <button wire:click="editTransaction({{ $t->id }})" class="bg-blue-200 text-black px-3 py-1 rounded">Edit</button>

                        @if($this->canDelete())
                            <button wire:click="confirmDelete({{ $t->id }})" class="bg-red-600 text-white px-3 py-1 rounded">Delete</button>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="p-3 text-center">No transactions found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
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
                    <select wire:model="editPlayerId" class="border rounded w-full px-2 py-1">
                        <option value="">Select Player</option>
                        @foreach($players as $p)
                            <option value="{{ $p->id }}">{{ $p->username }}</option>
                        @endforeach
                    </select>

                    <select wire:model="editGameId" class="border rounded w-full px-2 py-1">
                        <option value="">Select Game</option>
                        @foreach($games as $g)
                            <option value="{{ $g->id }}">{{ $g->name }}</option>
                        @endforeach
                    </select>

                    <input type="number" wire:model="editCashin" placeholder="Cash In" class="border rounded w-full px-2 py-1" />
                    <input type="number" wire:model="editCashout" placeholder="Cash Out" class="border rounded w-full px-2 py-1" />
                    <input type="number" wire:model="editBonusAdded" placeholder="Bonus Added" class="border rounded w-full px-2 py-1" />
                    <input type="number" wire:model="editDeposit" placeholder="Deposit" class="border rounded w-full px-2 py-1" />
                    <textarea wire:model="editNotes" placeholder="Notes" class="border rounded w-full px-2 py-1"></textarea>
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
                    <button wire:click="$set('deleteModal', false)" class="px-4 py-2 border rounded">
                        Cancel
                    </button>

                    <button wire:click="deleteTransaction" class="px-4 py-2 bg-red-600 text-white rounded">
                        Yes, Delete
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
