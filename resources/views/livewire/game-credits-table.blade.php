<div>
    <div class="mb-4 flex flex-col md:flex-row md:items-center md:justify-between gap-2">
        <div class="flex gap-2 flex-wrap">
            <div>
                <button wire:click="openAddModal" class="px-4 py-2 bg-green-600 text-white rounded">Add Record</button>
            </div>
            <a href="{{ route('game-points') }}"
               class="px-4 py-2 bg-indigo-600 text-white rounded">
                Game Points
            </a>
        </div>
        <div class="flex flex-col md:flex-row gap-2">
            <div class="flex gap-2">
                <input type="text" wire:model="searchStoreInput" placeholder="Search Store" class="border w-full rounded px-2 py-1" />
                <input type="date" wire:model="filterDateInput" class="border rounded px-2 w-full py-1" />
            </div>
            <div class="flex items-center gap-2">
                <select wire:model="game_id" class="border rounded w-full px-2 py-1">
                    <option value="">All Games</option>
                    @foreach($games as $g)
                        <option value="{{ $g->id }}">{{ $g->name }}</option>
                    @endforeach
                </select>

        <div>
            <button wire:click="applySearch" class="px-4 py-1 bg-blue-600 text-white rounded">Search</button>
        </div>

            </div>

        </div>

    </div>

    @forelse($creditsByDate as $date => $creditsChunk)
        <div class="mb-6 border-4 border-gray-300 p-3  grid grid-cols-1">
            <!-- Date chunk -->
            <h3 class="font-bold text-lg mb-2">{{ \Carbon\Carbon::parse($date)->format('Y-F-d') }}</h3>

            @foreach($creditsChunk->groupBy('game_id') as $gameId => $gameRecords)
                <!-- Game chunk -->
                <div class="mb-4 border border-gray-300 mt-2.5 p-2">
                    <h4 class="font-semibold mb-1 text-blue-700">{{ $gameRecords->first()->game->name }}</h4>
                    <div class="bg-white rounded shadow overflow-x-auto">
                        <table class="min-w-full table-auto">
                            <thead class="bg-gray-50">
                            <tr>
                                <th class="p-3 text-left">Sub Balance</th>
                                <th class="p-3 text-left">Store</th>
                                <th class="p-3 text-left">Store Balance</th>
                                <th class="p-3 text-left">Created By</th>
                                <th class="p-3 text-left">Last Edited By</th>
                                <th class="px-4 py-2 text-right">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($gameRecords as $record)
                                <tr class="border-t">
                                    <td class="p-3">$ {{ number_format($record->subdistributor_balance,2) }}</td>
                                    <td class="p-3">{{ $record->store_name }}</td>
                                    <td class="p-3">$ {{ number_format($record->store_balance,2) }}</td>
                                    <td class="p-3">{{ $record->created_by_name }}</td>
                                    <td class="p-3">{{ $record->updated_by_name }}</td>
                                    <td class="p-3 text-right flex justify-end gap-1">
                                        <button wire:click="openEditModal({{ $record->id }})" class="bg-blue-200 text-black px-3 py-1 rounded">Edit</button>
                                        @if($this->canDelete())
                                        <button wire:click="confirmDelete({{ $record->id }})" class="bg-red-600 text-white px-3 py-1 rounded">Delete</button>
                                            @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    @empty
        <p class="text-center py-6">No records found.</p>
    @endforelse

    <div class="mt-3">{{ $credits->links() }}</div>

    <!-- Add/Edit Modal -->
    @if($addModal || $editModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded shadow p-6 w-full max-w-md">
                <h2 class="text-xl font-bold mb-4">{{ $addModal ? 'Add Game Credit' : 'Edit Game Credit' }}</h2>
                <div class="space-y-3">
                    <select wire:model="game_id" class="w-full border rounded p-2">
                        <option value="">Select Game</option>
                        @foreach($games as $game)
                            <option value="{{ $game->id }}">{{ $game->name }}</option>
                        @endforeach
                    </select>

                    <input type="number" wire:model="subdistributor_balance" placeholder="Subdistributor Balance" class="w-full border rounded p-2" />
                    <input type="text" wire:model="store_name" placeholder="Store Name" class="w-full border rounded p-2" />
                    <input type="number" wire:model="store_balance" placeholder="Store Balance" class="w-full border rounded p-2" />
                    <input type="date" wire:model="date" class="w-full border rounded p-2" />
                </div>
                <div class="mt-4 flex justify-end gap-2">
                    <button wire:click="$set('{{ $addModal ? 'addModal' : 'editModal' }}', false)" class="px-4 py-2 border rounded bg-gray-500 text-white">Cancel</button>
                    <button wire:click="saveRecord" class="px-4 py-2 bg-green-600 text-white rounded">{{ $addModal ? 'Add' : 'Save Changes' }}</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Modal -->
    @if($deleteModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded shadow p-6 w-80 max-w-sm text-center">
                <h2 class="text-lg font-semibold mb-4">Confirm Delete</h2>
                <p>Are you sure you want to delete this record?</p>
                <div class="mt-4 flex justify-center gap-2">
                    <button wire:click="$set('deleteModal', false)" class="px-4 py-2 border rounded">Cancel</button>
                    <button wire:click="deleteRecord" class="px-4 py-2 bg-red-600 text-white rounded">Yes, Delete</button>
                </div>
            </div>
        </div>
    @endif
</div>
