<div>
    <!-- Filters + Add Player Button -->
    <div class="mb-4 flex flex-col md:flex-row md:items-center md:justify-between gap-2">
        <div class="flex gap-2">
            <input type="text" wire:model="searchInput" placeholder="Search player" class="border rounded px-2 py-1" />
            <button wire:click="applySearch" class="px-4 py-1 bg-blue-600 text-white rounded">Search</button>
        </div>
        <div>
            <button wire:click="openAddModal" class="px-4 py-2 bg-green-600 text-white rounded">Add Player</button>
        </div>
    </div>

    <!-- Players Table -->
    <div class="bg-white rounded shadow overflow-x-auto">
        <table class="min-w-full table-auto">
            <thead class="bg-gray-50">
            <tr>
                <th class="p-3 text-left">ID</th>
                <th class="p-3 text-left">Username</th>
                <th class="p-3 text-left">Facebook/Instagram Link</th>
                <th class="p-3 text-left">Phone</th>
                <th class="p-3 text-left">Created At</th>
                <th class="px-4 py-2 text-right">Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($players as $player)
                <tr class="border-t">
                    <td class="p-3">{{ $player->id }}</td>
                    <td class="p-3">{{ $player->username }}</td>
                    <td class="p-3">{{ $player->facebook_profile ?? '-' }}</td>
                    <td class="p-3">{{ $player->phone ?? '-' }}</td>
                    <td class="p-3">{{ $player->created_at->format('Y-m-d H:i') }}</td>
                    <td class="p-3 text-right flex justify-end gap-2">

                        <button wire:click="openEditModal({{ $player->id }})"
                                class="bg-blue-200 text-black px-3 py-1 rounded">Edit</button>

                        @if($this->canDelete())
                            <button wire:click="confirmDelete({{ $player->id }})"
                                    class="bg-red-600 text-white px-3 py-1 rounded">Delete</button>
                        @endif

                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="p-3 text-center">No players found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $players->links() }}
    </div>

    <!-- ADD MODAL -->
    @if($addModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded shadow p-6 w-full max-w-md">
                <h2 class="text-xl font-bold mb-4">Add Player</h2>
                <div class="space-y-3">
                    <input type="text" wire:model="username" placeholder="Username" class="w-full border rounded p-2" />
                    <input type="text" wire:model="facebook_link" placeholder="Facebook Link" class="w-full border rounded p-2" />
                    <input type="text" wire:model="phone" placeholder="Phone" class="w-full border rounded p-2" />
                </div>
                <div class="mt-4 flex justify-end gap-2">
                    <button wire:click="$set('addModal', false)" class="px-4 py-2 border rounded">Cancel</button>
                    <button wire:click="savePlayer" class="px-4 py-2 bg-green-600 text-white rounded">Add Player</button>
                </div>
            </div>
        </div>
    @endif

    <!-- EDIT MODAL -->
    @if($editModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded shadow p-6 w-full max-w-md">
                <h2 class="text-xl font-bold mb-4">Edit Player</h2>
                <div class="space-y-3">
                    <input type="text" wire:model="username" placeholder="Username" class="w-full border rounded p-2" />
                    <input type="text" wire:model="facebook_link" placeholder="Facebook Link" class="w-full border rounded p-2" />
                    <input type="text" wire:model="phone" placeholder="Phone" class="w-full border rounded p-2" />
                </div>
                <div class="mt-4 flex justify-end gap-2">
                    <button wire:click="$set('editModal', false)" class="px-4 py-2 border rounded">Cancel</button>
                    <button wire:click="savePlayer" class="px-4 py-2 bg-green-600 text-white rounded">Save Changes</button>
                </div>
            </div>
        </div>
    @endif

    <!-- DELETE CONFIRM MODAL -->
    @if($deleteModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded shadow  p-6 w-80 max-w-sm text-center">
                <h2 class="text-lg font-semibold mb-4">Confirm Delete</h2>
                <p>Are you sure you want to delete this player?</p>

                <div class="mt-4 flex justify-center gap-2">
                    <button wire:click="$set('deleteModal', false)" class="px-4 py-2 border rounded">
                        Cancel
                    </button>

                    <button wire:click="deletePlayer" class="px-4 py-2 bg-red-600 text-white rounded">
                        Yes, Delete
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
