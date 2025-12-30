<div>
    <!-- Header with Add Game button -->
    <div class="mb-4 flex flex-col gap-2 md:flex-row justify-between md:items-center">
        <div>
            @if($this->canEdit())
                <button wire:click="openAddModal" class="px-4 py-2 bg-green-600 text-white rounded">Add Game</button>
            @endif
        </div>

        <div class="flex flex-col md:flex-row justify-between gap-2">
            <input type="text" wire:model="searchInput" placeholder="Search game" class="border rounded px-2 py-1" />
            <div class="justify-self-end">
                <button wire:click="applySearch" class="px-4 py-1 bg-blue-600 text-white rounded">Search</button>
            </div>

        </div>

    </div>

    <!-- Games Table -->
    <div class="grid grid-cols-1">
    <div class="bg-white rounded shadow overflow-x-auto">
        <table class="min-w-full table-auto">
            <thead class="bg-gray-50">
            <tr>
                <th class="p-3 text-left">ID</th>
                <th class="p-3 text-left">Name</th>
                <th class="p-3 text-left">Game Code</th>
                <th class="p-3 text-left">Backend Link</th>
                <th class="p-3 text-left">Created At</th>
                @if($this->canEdit())
                    <th class="px-4 py-2 text-right">Actions</th>
                @endif
            </tr>
            </thead>
            <tbody>
            @forelse($games as $game)
                <tr class="border-t">
                    <td class="p-3">{{ $game->id }}</td>
                    <td class="p-3">{{ $game->name }}</td>
                    <td class="p-3">{{ $game->game_code ?? '-' }}</td>
                    <td class="p-3">{{ $game->backend_link ?? '-' }}</td>
                    <td class="p-3">{{ $game->created_at->format('Y-m-d H:i') }}</td>
                    <td class="p-3 text-right flex justify-end gap-2">
                        @if($this->canEdit())
                            <button wire:click="openEditModal({{ $game->id }})" class="bg-blue-200 text-black px-3 py-1 rounded">Edit</button>
                        @endif
                        @if($this->canDelete())
                            <button wire:click="confirmDelete({{ $game->id }})" class="bg-red-600 text-white px-3 py-1 rounded">Delete</button>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="p-3 text-center">No games found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    </div>
    <div class="mt-3">
        {{ $games->links() }}
    </div>

    <!-- Add/Edit Modal -->
    @if($modalOpen)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" wire:keydown.escape="$set('modalOpen', false)">
            <div class="bg-white rounded shadow p-6 w-full max-w-md">
                <h2 class="text-xl font-bold mb-4">{{ $editingGameId ? 'Edit Game' : 'Add Game' }}</h2>
                @if($duplicateGameError)
                    <div class="mb-3 p-2 bg-red-600 text-white rounded text-sm">
                        {{ $duplicateGameError }}
                    </div>
                @endif
                <div class="space-y-3">
                    <input type="text" wire:model="name" placeholder="Game Name" class="w-full border rounded p-2" />
                    <input type="text" wire:model="game_code" placeholder="Game Code / Invite Code" class="w-full border rounded p-2" />
                    <input type="text" wire:model="backend_link" placeholder="Backend Link of Game" class="w-full border rounded p-2" />
                </div>
                <div class="mt-4 flex justify-end gap-2">
                    <button wire:click="$set('modalOpen', false)" class="px-4 py-2 border rounded">Cancel</button>
                    <button wire:click="saveGame" class="px-4 py-2 bg-green-600 text-white rounded">{{ $editingGameId ? 'Save Changes' : 'Add Game' }}</button>
                </div>
            </div>
        </div>
    @endif

    <!-- DELETE CONFIRM MODAL -->
    @if($deleteModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded shadow  p-6 w-80 max-w-sm text-center">
                <h2 class="text-lg font-semibold mb-4">Confirm Delete</h2>
                <p>Are you sure you want to delete this game?</p>

                <div class="mt-4 flex justify-center gap-2">
                    <button wire:click="$set('deleteModal', false)" class="px-4 py-2 border rounded">
                        Cancel
                    </button>

                    <button wire:click="deleteGame" class="px-4 py-2 bg-red-600 text-white rounded">
                        Yes, Delete
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
