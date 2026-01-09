<div>
    <!-- Filters + Buttons -->
    <div class="mb-4 flex flex-col md:flex-row md:items-center md:justify-between gap-2">
        <div class="flex gap-2">
            <button wire:click="openAddModal"
                    class="px-4 py-2 bg-green-600 text-white rounded">
                Add Record
            </button>
            @if(auth()->check() && auth()->user()->role === 'admin')
                <button wire:click="openAddWalletDetailModal"
                        class="px-4 py-2 bg-indigo-600 text-white rounded">
                    Add Wallet
                </button>
            @endif
        </div>

        <div class="flex gap-2 flex-wrap">
            <div class="flex gap-2">
                <select wire:model.live="wallet_agent" class="border rounded px-2 py-1">
                    <option value="">All Wallet Agents</option>
                    @foreach($walletAgents as $a)
                        <option value="{{ $a }}">{{ $a }}</option>
                    @endforeach
                </select>

                <select wire:model.live="wallet_name_filter" class="border rounded px-2 py-1">
                    <option value="">All Wallet Names</option>
                    @foreach($walletNamesFilter as $w)
                        <option value="{{ $w }}">{{ $w }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2">
                <select wire:model.live="wallet_remarks_filter" class="border rounded px-2 py-1">
                    <option value="">All Wallet Remarks</option>
                    @foreach($walletRemarksFilter as $r)
                        <option value="{{ $r }}">{{ $r }}</option>
                    @endforeach
                </select>

                <input type="date" wire:model="filterDateInput"
                       class="border rounded px-2 py-1 w-full" />
            </div>

            <button wire:click="applySearch"
                    class="px-4 py-1 bg-blue-600 text-white rounded">
                Search
            </button>
        </div>
    </div>

    @forelse($walletsByDate as $date => $walletsChunk)
        <div class="grid grid-cols-1 mb-6">
            <h3 class="font-bold text-lg mb-2">
                {{ \Carbon\Carbon::parse($date)->format('Y-F-d') }}
            </h3>

            <div class="bg-white rounded shadow overflow-x-auto">
                <table class="min-w-full table-auto">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="p-3 text-left">ID</th>
                        <th class="p-3 text-left">Agent</th>
                        <th class="p-3 text-left">Wallet Name</th>
                        <th class="p-3 text-left">Wallet Remarks</th>
                        <th class="p-3 text-left">Current Balance</th>
                        <th class="p-3 text-left">Difference from Previous Balance</th>

                        <!-- ADDED -->
                        <th class="p-3 text-left">Cash In</th>
                        <th class="p-3 text-left">Cash Out</th>
                        <!--
                        <th class="p-3 text-left">Bonus</th>
                        -->
                        <th class="p-3 text-left">Net Transaction</th>
                        <th class="p-3 text-left">Variance</th>
                        <th class="p-3 text-left">Created By</th>
                        <th class="p-3 text-left">Last Edited By</th>
                        <th class="px-4 py-2 text-right">Actions</th>
                    </tr>
                    </thead>

                    <tbody>
                    @foreach($walletsChunk as $wallet)
                        @php
                            $key = $wallet->agent.'|'.$wallet->wallet_name.'|'.$wallet->wallet_remarks;
                            $disabled = $disabledWallets[$key] ?? null;
                          $highlight = $disabled
            && $wallet->date->gte($disabled->status_date ?? $wallet->date);
                        @endphp
                        <tr class="border-t  {{ $highlight ? 'bg-red-200' : '' }}">
                            <td class="p-3">{{ $wallet->id }}</td>
                            <td class="p-3">{{ $wallet->agent }}</td>
                            <td class="p-3">{{ $wallet->wallet_name }}</td>
                            <td class="p-3">{{ $wallet->wallet_remarks ?? '-' }}</td>
                            <td class="p-3">${{ number_format($wallet->current_balance, 2) }}</td>

                            <td class="p-3 font-semibold
                                {{ $wallet->balance_difference > 0 ? 'text-green-600' : '' }}
                                {{ $wallet->balance_difference < 0 ? 'text-red-600' : '' }}
                                {{ $wallet->balance_difference == 0 ? 'text-gray-500' : '' }}">
                                @if ($wallet->balance_difference < 0)
                                    -${{ number_format(abs($wallet->balance_difference), 2) }}
                                @else
                                    ${{ number_format($wallet->balance_difference, 2) }}
                                @endif
                            </td>

                            <!-- ADDED -->
                            <td class="p-3">${{ number_format($wallet->cashin, 2) }}</td>
                            <td class="p-3">${{ number_format($wallet->cashout, 2) }}</td>
                            <!--
                            <td class="p-3">${{ number_format($wallet->bonus, 2) }}</td>
                            -->
                            <td class="p-3 text-right">
                                @if($wallet->net_transaction < 0)
                                    -${{ number_format(abs($wallet->net_transaction), 2) }}
                                @else
                                    ${{ number_format($wallet->net_transaction, 2) }}
                                @endif
                            </td>

                            <td class="p-3 text-right">
                                @php
                                    $variance = floatval($wallet->balance_difference) - floatval($wallet->net_transaction);
                                @endphp

                                @if($variance == 0)
                                    <span class="text-green-600 font-bold">✔</span>
                                @elseif($variance > 0)
                                    <span class="text-green-600 font-bold">
                                        ${{ number_format($variance, 2) }}
                                    </span>
                                @else
                                    <span class="text-red-600 font-bold">
                                        ${{ number_format(abs($variance), 2) }}
                                    </span>
                                @endif
                            </td>

                            <td class="p-3">{{ $wallet->created_by_name }}</td>
                            <td class="p-3">{{ $wallet->updated_by_name }}</td>
                            <td class="p-3 text-right flex justify-end gap-1">
                                <button wire:click="openEditModal({{ $wallet->id }})"
                                        class="bg-blue-200 text-black px-3 py-1 rounded">
                                    Edit
                                </button>

                                @if($this->canDelete())
                                    <button wire:click="confirmDelete({{ $wallet->id }})"
                                            class="bg-red-600 text-white px-3 py-1 rounded">
                                        Delete
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach

                    <!-- ADDED TOTAL ROW -->
                    @php
                        $totalCashin = $walletsChunk->sum('cashin');
                        $totalCashout = $walletsChunk->sum('cashout');
                        $totalBonus = $walletsChunk->sum('bonus');
                        $totalNet = $walletsChunk->sum('net_transaction');
                    @endphp

                    <tr class="border-t bg-gray-100 font-bold">
                        <td colspan="6" class="p-3 text-right">TOTAL</td>
                        <td class="p-3">${{ number_format($totalCashin, 2) }}</td>
                        <td class="p-3">${{ number_format($totalCashout, 2) }}</td>
                        <!--
                        <td class="p-3">${{ number_format($totalBonus, 2) }}</td>
                        -->
                        <td class="p-3 text-right">
                            @if($totalNet < 0)
                                -${{ number_format(abs($totalNet), 2) }}
                            @else
                                ${{ number_format($totalNet, 2) }}
                            @endif
                        </td>
                        <td colspan="4"></td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <p class="text-center py-6">No wallet records found.</p>
    @endforelse

    <div class="mt-3">
        {{ $wallets->links() }}
    </div>
    <!-- ADD / EDIT WALLET RECORD MODAL -->
    @if($addModal || $editModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded shadow p-6 w-full max-w-md">
                <h2 class="text-xl font-bold mb-4">
                    {{ $addModal ? 'Add Wallet Record' : 'Edit Wallet Record' }}
                </h2>

                <div class="space-y-3">
                    @error('date')
                    <p class="mb-3 p-2 bg-red-600 text-white rounded text-sm">
                        {{ $message }}
                    </p>
                    @enderror

                    <select wire:model.live="agent" class="w-full border rounded p-2">

                    <option value="">Select Agent</option>
                        @foreach($agents as $a)
                            <option value="{{ $a }}">{{ $a }}</option>
                        @endforeach
                    </select>

                    <select wire:model.live="wallet_name" class="w-full border rounded p-2">
                        <option value="">Select Wallet Name</option>
                        @foreach($walletNames as $w)
                            <option value="{{ $w }}">{{ $w }}</option>
                        @endforeach
                    </select>

                    <select wire:model="wallet_remarks" class="w-full border rounded p-2">
                        <option value="">Select Wallet Remarks</option>
                        @foreach($walletRemarks as $r)
                            <option value="{{ $r }}">{{ $r }}</option>
                        @endforeach
                    </select>

                    <input type="number" wire:model="current_balance"
                           placeholder="Current Balance"
                           class="w-full border rounded p-2" />

                    <input type="date" wire:model="date"
                           class="w-full border rounded p-2" />
                </div>

                <div class="mt-4 flex justify-end gap-2">
                    <button wire:click="$set('{{ $addModal ? 'addModal' : 'editModal' }}', false)"
                            class="px-4 py-2 border rounded bg-gray-500 text-white">
                        Cancel
                    </button>

                    <button wire:click="saveWallet"
                            class="px-4 py-2 bg-green-600 text-white rounded">
                        {{ $addModal ? 'Add' : 'Save Changes' }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- ADD WALLET DETAIL MODAL -->
    @if($addWalletDetailModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded shadow p-6 w-full max-w-md">
                <h2 class="text-xl font-bold mb-4">Add Wallet</h2>
                @error('detail_wallet_name')
                <p class="mb-3 p-2 bg-red-600 text-white rounded text-sm">{{ $message }}</p>
                @enderror

                <div class="space-y-3">
                    <input wire:model="detail_agent"
                           class="w-full border rounded p-2"
                           placeholder="Agent">

                    <input wire:model="detail_wallet_name"
                           class="w-full border rounded p-2"
                           placeholder="Wallet Name">

                    <input wire:model="detail_wallet_remarks"
                           class="w-full border rounded p-2"
                           placeholder="Wallet Remarks">
                </div>

                <div class="mt-4 flex justify-end gap-2">
                    <button wire:click="$set('addWalletDetailModal', false)"
                            class="px-4 py-2 border rounded bg-gray-500 text-white">
                        Cancel
                    </button>

                    <button wire:click="saveWalletDetail"
                            class="px-4 py-2 bg-green-600 text-white rounded">
                        Save
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- DELETE MODAL (UNCHANGED) -->
    @if($deleteModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded shadow p-6 w-80 max-w-sm text-center">
                <h2 class="text-lg font-semibold mb-4">Confirm Delete</h2>
                <p>Are you sure you want to delete this wallet record?</p>
                <div class="mt-4 flex justify-center gap-2">
                    <button wire:click="$set('deleteModal', false)"
                            class="px-4 py-2 border rounded">
                        Cancel
                    </button>
                    <button wire:click="deleteWallet"
                            class="px-4 py-2 bg-red-600 text-white rounded">
                        Yes, Delete
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
