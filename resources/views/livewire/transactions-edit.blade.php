<div
    x-data="{ open: false }"
    x-show="open"
    x-cloak
    @show-edit-modal.window="open = true"
    @close-edit-modal.window="open = false"
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
>
    <div class="bg-white rounded shadow p-6 w-96">
        <h2 class="text-lg font-bold mb-4">Edit Transaction</h2>

        <!-- Form fields -->
        <select wire:model="player_id" class="border rounded w-full px-2 py-1">
            <option value="">Select Player</option>
            @foreach($players as $p)
                <option value="{{ $p->id }}">{{ $p->username }}</option>
            @endforeach
        </select>

        <select wire:model="game_id" class="border rounded w-full px-2 py-1">
            <option value="">Select Game</option>
            @foreach($games as $g)
                <option value="{{ $g->id }}">{{ $g->name }}</option>
            @endforeach
        </select>

        <input type="number" wire:model="cashin" placeholder="Cash In" class="border rounded w-full px-2 py-1" />
        <input type="number" wire:model="cashout" placeholder="Cash Out" class="border rounded w-full px-2 py-1" />
        <input type="number" wire:model="bonus_added" placeholder="Bonus Added" class="border rounded w-full px-2 py-1" />
        <input type="number" wire:model="deposit" placeholder="Deposit" class="border rounded w-full px-2 py-1" />
        <textarea wire:model="notes" placeholder="Notes" class="border rounded w-full px-2 py-1"></textarea>

        <div class="flex justify-end gap-2 mt-4">
            <button @click="open = false" class="px-4 py-2 border rounded">Cancel</button>
            <button wire:click="updateTransaction" class="px-4 py-2 bg-green-600 text-white rounded">Save Changes</button>
        </div>
    </div>
</div>
