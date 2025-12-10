<div>
    @if($showModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded shadow-lg w-96">
                <h2 class="text-lg font-bold mb-4">New Transaction</h2>
                <!-- FIELDS -->
                <div class="space-y-2">
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

                    <input type="number" wire:model="cashin" class="border rounded w-full px-2 py-1" placeholder="Cash In">
                    <input type="number" wire:model="cashout" class="border rounded w-full px-2 py-1" placeholder="Cash Out">
                    <input type="text" wire:model="cash_tag" class="border rounded w-full px-2 py-1" placeholder="Cash Tag">
                    <input type="text" wire:model="wallet_name" class="border rounded w-full px-2 py-1" placeholder="Wallet Name">
                    <input type="text" wire:model="wallet_remarks" class="border rounded w-full px-2 py-1" placeholder="Wallet Remarks">
                    <input type="number" wire:model="bonus_added" class="border rounded w-full px-2 py-1" placeholder="Bonus Added">
                    <textarea wire:model="notes" class="border rounded w-full px-2 py-1" placeholder="Notes"></textarea>
                </div>
                <!-- BUTTONS -->
                <div class="flex justify-end gap-2 mt-4">
                    <button wire:click="closeModal" class="px-4 py-2 bg-gray-500 text-white rounded"> Cancel </button>
                    <button wire:click="save" class="px-4 py-2 bg-green-600 text-white rounded"> Save </button>
                </div>
            </div>
        </div>
    @endif
</div>
