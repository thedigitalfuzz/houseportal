<div>
    <!-- Filters + Add Wallet Button -->
    <div class="mb-4 flex flex-col md:flex-row md:items-center md:justify-between gap-2">
        <div>
            <button wire:click="openAddModal" class="px-4 py-2 bg-green-600 text-white rounded">Add Record</button>
        </div>
        <div class="flex gap-2 flex-wrap">
            <div class="flex gap-2">
                <input type="text" wire:model="searchAgentInput" placeholder="Search Agent" class="border rounded px-2 py-1 w-full" />
                <input type="text" wire:model="searchWalletInput" placeholder="Wallet Name" class="border rounded px-2 py-1 w-full" />
            </div>
            <div class="flex gap-2">
                <input type="text" wire:model="searchRemarksInput" placeholder="Wallet Remarks" class="border rounded w-full px-2 py-1" />
                <input type="date" wire:model="filterDateInput" class="border rounded px-2 py-1 w-full" />
            </div>

            <button wire:click="applySearch" class="px-4 py-1 bg-blue-600 text-white rounded">Search</button>
        </div>

    </div>

    @forelse($walletsByDate as $date => $walletsChunk)
        <div class="grid grid-cols-1 mb-6">
            <h3 class="font-bold text-lg mb-2">{{ \Carbon\Carbon::parse($date)->format('Y-F-d') }}</h3>

            <div class="bg-white rounded shadow overflow-x-auto">
                <table class="min-w-full table-auto">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="p-3 text-left">ID</th>
                        <th class="p-3 text-left">Agent</th>
                        <th class="p-3 text-left">Wallet Name</th>
                        <th class="p-3 text-left">Wallet Remarks</th>
                        <th class="p-3 text-left">Current Balance</th>
                        <th class="p-3 text-left">Date</th>
                        <th class="p-3 text-left">Created By</th>
                        <th class="p-3 text-left">Last Edited By</th>
                        <th class="px-4 py-2 text-right">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($walletsChunk as $wallet)
                        <tr class="border-t">
                            <td class="p-3">{{ $wallet->id }}</td>
                            <td class="p-3">{{ $wallet->agent }}</td>
                            <td class="p-3">{{ $wallet->wallet_name }}</td>
                            <td class="p-3">{{ $wallet->wallet_remarks ?? '-' }}</td>
                            <td class="p-3">$ {{ number_format($wallet->current_balance, 2) }}</td>
                            <td class="p-3">{{ $wallet->date->format('Y-m-d') }}</td>
                            <td class="p-3">{{ $wallet->created_by_name }}</td>
                            <td class="p-3">{{ $wallet->updated_by_name }}</td>
                            <td class="p-3 text-right flex justify-end gap-1">
                                <button wire:click="openEditModal({{ $wallet->id }})" class="bg-blue-200 text-black px-3 py-1 rounded">Edit</button>
                                @if($this->canDelete())
                                <button wire:click="confirmDelete({{ $wallet->id }})" class="bg-red-600 text-white px-3 py-1 rounded">Delete</button>
                                    @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <p class="text-center py-6">No wallet records found.</p>
    @endforelse

    <!-- Pagination Links -->
    <div class="mt-3">
        {{ $wallets->links() }}
    </div>

    <!-- ADD / EDIT MODALS -->
    @if($addModal || $editModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded shadow p-6 w-full max-w-md">
                <h2 class="text-xl font-bold mb-4">{{ $addModal ? 'Add Wallet' : 'Edit Wallet' }}</h2>
                <div class="space-y-3">
                    <input type="text" wire:model="agent" placeholder="Agent" class="w-full border rounded p-2" />
                    <input type="text" wire:model="wallet_name" placeholder="Wallet Name" class="w-full border rounded p-2" />
                    <input type="text" wire:model="wallet_remarks" placeholder="Wallet Remarks" class="w-full border rounded p-2" />
                    <input type="number" wire:model="current_balance" placeholder="Current Balance" class="w-full border rounded p-2" />
                    <input type="date" wire:model="date" class="w-full border rounded p-2" />
                </div>
                <div class="mt-4 flex justify-end gap-2">
                    <button wire:click="$set('{{ $addModal ? 'addModal' : 'editModal' }}', false)" class="px-4 py-2 border rounded">Cancel</button>
                    <button wire:click="saveWallet" class="px-4 py-2 bg-green-600 text-white rounded">{{ $addModal ? 'Add' : 'Save Changes' }}</button>
                </div>
            </div>
        </div>
    @endif

    <!-- DELETE CONFIRM MODAL -->
    @if($deleteModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded shadow p-6 w-80 max-w-sm text-center">
                <h2 class="text-lg font-semibold mb-4">Confirm Delete</h2>
                <p>Are you sure you want to delete this wallet record?</p>
                <div class="mt-4 flex justify-center gap-2">
                    <button wire:click="$set('deleteModal', false)" class="px-4 py-2 border rounded">Cancel</button>
                    <button wire:click="deleteWallet" class="px-4 py-2 bg-red-600 text-white rounded">Yes, Delete</button>
                </div>
            </div>
        </div>
    @endif
</div>
