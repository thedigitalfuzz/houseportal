<div>

    <!-- HEADER -->
    <div class="mb-4 flex flex-col md:flex-row justify-between items-start md:items-center">
        <div>
            @if($this->canEdit())
                <button wire:click="openAddModal"
                        class="px-4 py-2 bg-green-600 text-white rounded mb-3 md:mb-0">
                    Add Recharge
                </button>
            @endif
        </div>

        <!-- FILTERS -->
        <div class="flex gap-2">
            <select wire:model.live="gameFilter" class="border rounded px-2 py-1">
                <option value="">All Games</option>
                @foreach($games as $g)
                    <option value="{{ $g->id }}">{{ $g->name }}</option>
                @endforeach
            </select>

            <input type="date" wire:model.live="dateFilter" class="border rounded px-2 py-1">
        </div>
    </div>

    <!-- TABLE -->
    <div class="grid grid-cols-1 mb-4">
    <div class="bg-white rounded shadow overflow-x-auto">
        <table class="min-w-full table-auto">
            <thead class="bg-gray-50">
            <tr>
                <th class="p-3 text-left">Date</th>
                <th class="p-3 text-left">Game</th>
                <th class="p-3 text-left">Subdistributor</th>
                <th class="p-3 text-right">Amount</th>
                @if($this->canEdit())
                    <th class="p-3 text-right">Actions</th>
                @endif
            </tr>
            </thead>
            <tbody>
            @foreach($records as $r)
                <tr class="border-t">
                    <td class="p-3">{{ $r->date->format('Y-m-d') }}</td>
                    <td class="p-3">{{ $r->game->name }}</td>
                    <td class="p-3">{{ $r->subdistributor->sub_username }}</td>
                    <td class="p-3 text-right">${{ number_format($r->amount,2) }}</td>

                    @if($this->canEdit())
                        <td class="p-3 text-right flex justify-end gap-2">
                            <button wire:click="openEditModal({{ $r->id }})"
                                    class="bg-blue-200 px-3 py-1 rounded">
                                Edit
                            </button>

                            <button wire:click="confirmDelete({{ $r->id }})"
                                    class="bg-red-600 text-white px-3 py-1 rounded">
                                Delete
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
        {{ $records->links() }}
    </div>

    <!-- MODAL -->
    @if($modalOpen)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
            <div class="bg-white p-6 rounded w-full max-w-md">

                <h2 class="text-xl font-bold mb-4">
                    {{ $editingId ? 'Edit Recharge' : 'Add Recharge' }}
                </h2>

                <div class="space-y-3">

                    <input type="date" wire:model="date" class="w-full border p-2 rounded">

                    <select wire:model.live="game_id" class="w-full border p-2 rounded">
                        <option value="">Select Game</option>
                        @foreach($games as $g)
                            <option value="{{ $g->id }}">{{ $g->name }}</option>
                        @endforeach
                    </select>

                    <select wire:model="subdistributor_id"
                            class="w-full border p-2 rounded"
                        {{ !$game_id ? 'disabled' : '' }}>
                        <option value="">
                            {{ $game_id ? 'Select Subdistributor' : 'Select Game First' }}
                        </option>

                        @foreach($subUsers as $sub)
                            <option value="{{ $sub->id }}">{{ $sub->sub_username }}</option>
                        @endforeach
                    </select>

                    <input type="number" wire:model="amount"
                           placeholder="Recharge Amount"
                           class="w-full border p-2 rounded">

                </div>

                <div class="mt-4 flex justify-end gap-2">
                    <button wire:click="$set('modalOpen', false)"
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

</div>
