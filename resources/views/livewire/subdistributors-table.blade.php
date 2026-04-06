<div>
    <!-- TOP BUTTONS -->
    <div class="mb-4  flex flex-col md:flex-row justify-between items-start md:items-center">
        <div class="flex gap-2 mb-4 md:mb-0">
            @if($this->canEdit())
                <button wire:click="openAddModal" class="px-4 py-2 bg-green-600 text-white rounded">
                    Add Subdistributor
                </button>
            @endif

            <a href="{{route('sub-recharge')}}" class="px-4 py-2 bg-indigo-600 text-white rounded">
                Recharge Informations
            </a>
        </div>

        <!-- FILTERS -->
        <div class="flex gap-2">
            <!-- Game Filter -->
            <select wire:model.live="gameFilter" class="border rounded px-2 py-1">
                <option value="">All Games</option>
                @foreach($games as $g)
                    <option value="{{ $g->id }}">{{ $g->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- TABLE -->
    <div class="grid grid-cols-1 mb-4">
    <div class="bg-white rounded shadow overflow-x-auto">
        <table class="min-w-full table-auto">
            <thead class="bg-gray-50">
            <tr>
                <th class="p-3 text-left">Game</th>
                <th class="p-3 text-left">Subdistributor</th>
                <th class="p-3 text-left">Status</th>
                <th class="p-3 text-left">Created At</th>
                @if($this->canEdit())
                    <th class="p-3 text-right">Actions</th>
                @endif
            </tr>
            </thead>

            <tbody>
            @foreach($subs as $sub)
                <tr class="border-t {{ $sub->status == 'inactive' ? 'bg-gray-200 text-gray-500' : '' }}">
                    <td class="p-3">{{ $sub->game->name }}</td>
                    <td class="p-3">{{ $sub->sub_username }}</td>
                    <td class="p-3 capitalize">{{ $sub->status }}</td>
                    <td class="p-3">{{ $sub->created_at->format('Y-m-d H:i') }}</td>

                    @if($this->canEdit())
                        <td class="p-3 text-right">
                            <button wire:click="openEditModal({{ $sub->id }})"
                                    class="bg-blue-200 px-3 py-1 rounded">
                                Edit
                            </button>
                        </td>
                    @endif
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    </div>
    <div class="mt-3">
        {{ $subs->links() }}
    </div>

    <!-- MODAL -->
    @if($modalOpen)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded shadow p-6 w-full max-w-md">
                <h2 class="text-xl font-bold mb-4">
                    {{ $editingId ? 'Edit Subdistributor' : 'Add Subdistributor' }}
                </h2>

                <div class="space-y-3">
                    <select wire:model="game_id" class="w-full border rounded p-2">
                        <option value="">Select Game</option>
                        @foreach($games as $game)
                            <option value="{{ $game->id }}">{{ $game->name }}</option>
                        @endforeach
                    </select>

                    <input type="text" wire:model="sub_username"
                           placeholder="Subdistributor Username"
                           class="w-full border rounded p-2" />

                    <select wire:model="status" class="w-full border rounded p-2">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div class="mt-4 flex justify-end gap-2">
                    <button wire:click="$set('modalOpen', false)"
                            class="px-4 py-2 bg-gray-500 text-white rounded">
                        Cancel
                    </button>

                    <button wire:click="saveSub"
                            class="px-4 py-2 bg-green-600 text-white rounded">
                        {{ $editingId ? 'Save Changes' : 'Add Subdistributor' }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
