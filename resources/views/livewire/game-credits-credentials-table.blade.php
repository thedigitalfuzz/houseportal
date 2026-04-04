<div>
    <script src="//unpkg.com/alpinejs" defer></script>
    <div class="mb-4 flex flex-col md:flex-row md:items-center md:justify-between gap-2">

        <!-- Left side -->
        <div>
            <button wire:click="openAddModal"
                    class="px-4 py-2 bg-green-600 text-white rounded">
                Add Credential
            </button>
        </div>

        <!-- Right side (FILTER) -->
        <div class="flex flex-col md:flex-row gap-2 items-center">
            <div>Search:</div>
            <select wire:model.live="filterGame"
                    class="border rounded px-2 py-1 w-full">
                <option value="">All Games</option>
                @foreach($games as $game)
                    <option value="{{ $game->id }}">{{ $game->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <h2 class="text-lg font-bold mb-2 text-blue-700">Subdistributors</h2>

    <div class="grid grid-cols-1">
    <div class="bg-white rounded shadow overflow-x-auto mb-6">
        <table class="min-w-full table-auto">
            <thead class="bg-gray-50">
            <tr>
                <th class="p-3 text-left">Game</th>
                <th class="p-3 text-left">Username</th>
                <th class="p-3 text-left">Password</th>
                <th class="p-3 text-right">Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($subdistributors as $row)
                <tr class="border-t"  wire:key="subdis-{{ $row->id }}">
                    <td class="p-3">{{ $row->game->name }}</td>
                    <td class="p-3">{{ $row->username }}</td>

                    <td class="p-3" x-data="{ show: false }">
                        <span x-text="show ? '{{ $row->password }}' : '••••••••'"></span>
                        <button
                            @click="show = !show"
                            class="text-blue-500 text-xs ml-2"
                            x-text="show ? 'Hide' : 'Show'">
                        </button>
                    </td>

                    <td class="p-3 text-right flex justify-end gap-2">
                        <button wire:click.prevent="openEditModal({{ $row->id }})"
                                class="bg-blue-200 text-black px-3 py-1 rounded">
                            Edit
                        </button>

                        <button wire:click="confirmDelete({{ $row->id }})"
                                class="bg-red-600 text-white px-3 py-1 rounded">
                            Delete
                        </button>
                    </td>
                </tr>
                @empty
                    <tr>
                        <td colspan="4" class="p-3 text-center">No subdistributor credentials found.</td>
                    </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    </div>

    <div class="grid grid-cols-1">
    <h2 class="text-lg font-bold mb-2 text-red-600">Stores</h2>

    <div class="bg-white rounded shadow overflow-x-auto">
        <table class="min-w-full table-auto">
            <thead class="bg-gray-50">
            <tr>
                <th class="p-3 text-left">Game</th>
                <th class="p-3 text-left">Username</th>
                <th class="p-3 text-left">Password</th>
                <th class="p-3 text-right">Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($stores as $row)
                <tr wire:key="store-{{ $row->id }}" class="border-t">
                    <td class="p-3">{{ $row->game->name }}</td>
                    <td class="p-3">{{ $row->username }}</td>

                    <td class="p-3" x-data="{ show: false }">
                        <span x-text="show ? '{{ $row->password }}' : '••••••••'"></span>

                        <button
                            @click="show = !show"
                            class="text-blue-500 text-xs ml-2"
                            x-text="show ? 'Hide' : 'Show'">
                        </button>
                    </td>

                    <td class="p-3 text-right flex justify-end gap-2">
                        <button wire:click.prevent="openEditModal({{ $row->id }})"
                                class="bg-blue-200 px-3 py-1 rounded">
                            Edit
                        </button>

                        <button wire:click="confirmDelete({{ $row->id }})"
                                class="bg-red-600 text-white px-3 py-1 rounded">
                            Delete
                        </button>
                    </td>
                </tr>
                @empty
                    <tr>
                        <td colspan="4" class="p-3 text-center">No store credentials found.</td>
                    </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    </div>
    @if($addModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded w-full max-w-md">
                <h2 class="text-xl font-bold mb-4">
                    Add Credential
                </h2>

                <div class="space-y-3">
                    <select wire:model="game_id" class="w-full border p-2 rounded">
                        <option value="">Select Game</option>
                        @foreach($games as $game)
                            <option value="{{ $game->id }}">{{ $game->name }}</option>
                        @endforeach
                    </select>

                    <select wire:model="type" class="w-full border p-2 rounded">
                        <option value="subdistributor">Subdistributor</option>
                        <option value="store">Store</option>
                    </select>

                    <input type="text" wire:model="username"
                           placeholder="Username"
                           class="w-full border p-2 rounded"/>

                    <input type="text" wire:model="password"
                           placeholder="Password"
                           class="w-full border p-2 rounded"/>
                </div>

                <div class="mt-4 flex justify-end gap-2">
                    <button wire:click="$set('addModal', false)"
                            class="px-4 py-2 bg-gray-500 text-white rounded">
                        Cancel
                    </button>

                    <button wire:click="save"
                            class="px-4 py-2 bg-green-600 text-white rounded">
                        Save
                    </button>
                </div>
            </div>
        </div>
@endif
    @if($editModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded w-full max-w-md">
                <h2 class="text-xl font-bold mb-4">
                    Edit Credential
                </h2>

                <div class="space-y-3">
                    <select wire:model="game_id" class="w-full border p-2 rounded">
                        <option value="">Select Game</option>
                        @foreach($games as $game)
                            <option value="{{ $game->id }}">{{ $game->name }}</option>
                        @endforeach
                    </select>

                    <select wire:model="type" class="w-full border p-2 rounded">
                        <option value="subdistributor">Subdistributor</option>
                        <option value="store">Store</option>
                    </select>

                    <input type="text" wire:model="username"
                           placeholder="Username"
                           class="w-full border p-2 rounded"/>

                    <input type="text" wire:model="password"
                           placeholder="Password"
                           class="w-full border p-2 rounded"/>
                </div>

                <div class="mt-4 flex justify-end gap-2">
                    <button wire:click="$set('editModal', false)"
                            class="px-4 py-2 bg-gray-500 text-white rounded">
                        Cancel
                    </button>

                    <button wire:click="save"
                            class="px-4 py-2 bg-green-600 text-white rounded">
                        Save
                    </button>
                </div>
            </div>
        </div>
    @endif




    @if($deleteModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded text-center w-80">
                <h2 class="text-lg font-semibold mb-4">Confirm Delete</h2>
                <p>Are you sure?</p>

                <div class="mt-4 flex justify-center gap-2">
                    <button wire:click="$set('deleteModal', false)"
                            class="px-4 py-2 border rounded">
                        Cancel
                    </button>

                    <button wire:click="delete"
                            class="px-4 py-2 bg-red-600 text-white rounded">
                        Delete
                    </button>
                </div>
            </div>
        </div>
@endif
</div>
