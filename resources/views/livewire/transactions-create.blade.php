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

                    <select wire:model="transaction_type" class="border rounded w-full px-2 py-1">
                        <option value="">Select Type</option>
                        <option value="cashin">Cash In</option>
                        <option value="cashout">Cash Out</option>
                    </select>

                    <input type="number" wire:model="amount" class="border rounded w-full px-2 py-1" placeholder="Amount">

                    <input type="text" wire:model="cash_tag" class="border rounded w-full px-2 py-1" placeholder="Cash Tag">
                    <!-- Agent Dropdown -->

                    <select wire:model.live="agent" class="w-full border rounded p-2">
                        <option value="">Select Agent</option>
                        @foreach($agents as $a)
                            <option value="{{ $a }}">{{ $a }}</option>
                        @endforeach
                    </select>

                    <!-- Wallet Name Dropdown -->
                    <select wire:model.live="wallet_name" class="w-full border rounded p-2">
                        <option value="">Select Wallet Name</option>
                        @foreach($walletNames as $w)
                            <option value="{{ $w }}">{{ $w }}</option>
                        @endforeach
                    </select>

                    <!-- Wallet Remarks Dropdown -->
                    <select wire:model.live="wallet_remarks" class="w-full border rounded p-2">
                        <option value="">Select Wallet Remarks</option>
                        @foreach($walletRemarks as $r)
                            <option value="{{ $r }}">{{ $r }}</option>
                        @endforeach
                    </select>


                    <input type="number" wire:model="bonus_added" class="border rounded w-full px-2 py-1" placeholder="Bonus Added">
                    <input type="date" wire:model="transaction_date" class="border rounded w-full px-2 py-1" placeholder="Transaction Date">
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
