<div class="p-4">
    <div id="wallet-alert" class="fixed top-4 right-4 z-50 hidden">
        <div class="bg-green-600 text-white px-4 py-2 rounded shadow flex items-center justify-between gap-4">
            <span id="wallet-alert-message"></span>
            <button id="wallet-alert-close" class="font-bold">&times;</button>
        </div>
    </div>
    <div class="mb-4">
        <h1 class="text-3xl font-bold mb-4">Wallet Details</h1>
        <div class="flex justify-between items-start md:items-center flex-col md:flex-row gap-4">
            <div class="w-full">
                <button wire:click="openAddWalletDetailModal"
                        class="px-3 py-1.5 bg-blue-600 text-white rounded hover:bg-blue-500 transition-all">
                    Add Wallet
                </button>
            </div>

            <div class="mb-4 flex gap-2 justify-end w-full">
                <input type="text" wire:model.defer="searchTerm" placeholder="Search Agent, Wallet, Remarks" class="border rounded px-3 py-1.5 w-full md:w-1/3">
                <button wire:click="loadData" class="px-3 py-1.5 bg-blue-600 text-white rounded hover:bg-blue-500 transition-all">Search</button>
            </div>
        </div>

    </div>

    <div class="grid grid-cols-1 mb-6">
    <div class="bg-white shadow rounded p-4 overflow-x-auto">
        <table class="min-w-full table-auto">
            <thead>
            <tr class="bg-gray-100">
                <th class="p-3 text-left">Agent</th>
                <th class="p-3 text-left">Wallet Name</th>
                <th class="p-3 text-left">Wallet Remarks</th>
                <th class="p-3 text-left">Status</th>
                <th class="p-3 text-left">Active / Disabled Since</th>
                <th class="p-3 text-left">Created At</th>
                <th class="p-3 text-left">Created By</th>
                <th class="p-3 text-left">Last Edited By</th>
                <th class="p-3 text-right">Actions</th>
            </tr>
            </thead>

            <tbody>
            @foreach($walletDetails as $wd)
                <tr class="border-t">
                    <td class="p-3">{{ $wd->agent }}</td>
                    <td class="p-3">{{ $wd->wallet_name }}</td>
                    <td class="p-3">{{ $wd->wallet_remarks ?? '-' }}</td>
                    <td class="p-3 font-semibold">
                        @if($wd->status === 'disabled')
                            <span class="text-red-600">Disabled</span>
                        @else
                            <span class="text-green-600">Active</span>
                        @endif
                    </td>

                    <td class="p-3">
                        {{ optional($wd->effective_status_date)->format('Y-m-d') }}
                    </td>

                    <td class="p-2">{{ $wd->created_at->format('Y-m-d') }}</td>

                    <td class="p-3">{{ $wd->created_by_name }}</td>
                    <td class="p-3">{{ $wd->updated_by_name }}</td>

                    <td class="p-3 text-right flex justify-end gap-2">

                        <button wire:click="openEditModal({{ $wd->id }})" class="bg-blue-200 text-black px-3 py-1 rounded hover:bg-blue-100 transition-all">Edit</button>

                        @if($this->canDelete())
                            <button wire:click="confirmDelete({{ $wd->id }})" class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-500 transition-all">Delete</button>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    </div>
    <!-- DELETE MODAL (UNCHANGED) -->
    @if($deleteModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded shadow p-6 w-80 max-w-sm text-center">
                <h2 class="text-lg font-semibold mb-4">Confirm Delete</h2>
                <p>Are you sure you want to delete this wallet record?</p>
                <div class="mt-4 flex justify-center gap-2">
                    <button wire:click="closeDeleteModal"
                            class="px-4 py-2 bg-gray-200 border rounded hover:bg-gray-100 transition-all">
                        Cancel
                    </button>
                    <button wire:click="deleteConfirmed"
                            class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-500 transition-all">
                        Yes, Delete
                    </button>

                </div>
            </div>
        </div>
    @endif

    @if($editModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded shadow p-6 w-96">
                <h2 class="text-lg font-bold mb-4">Update Wallet Status</h2>

                <div class="space-y-3">
                    <!-- Wallet Info (readonly) -->
                    <div>
                        <label class="text-xs font-semibold">Agent</label>
                        <input type="text" value="{{ $agent }}" readonly class="border rounded w-full px-2 py-1 bg-gray-100"/>
                    </div>
                    <div>
                        <label class="text-xs font-semibold">Wallet Name</label>
                        <input type="text" value="{{ $wallet_name }}" readonly class="border rounded w-full px-2 py-1 bg-gray-100"/>
                    </div>
                    <div>
                        <label class="text-xs font-semibold">Wallet Remarks</label>
                        <input type="text" value="{{ $wallet_remarks }}" readonly class="border rounded w-full px-2 py-1 bg-gray-100"/>
                    </div>

                    <!-- Status radio buttons -->
                    <div class="flex items-center gap-4 mt-2">
                        <label class="flex items-center gap-1">
                            <input type="radio" wire:model="status" value="active" id="active">
                            <span>Active</span>
                        </label>
                        <label class="flex items-center gap-1">
                            <input type="radio" wire:model="status" value="disabled" id="disabled">
                            <span class="text-red-600 font-semibold">Disabled</span>
                        </label>
                    </div>

                    <!-- Date input: always in DOM, hide/show via style -->
                    <div class="mt-2" id="disabledDateDiv" style="{{$status !== 'disabled' ? "display: none" : ''}}">
                        <label class="text-xs block">Disabled Since</label>
                        <input type="date" wire:model="status_date"
                               class="border rounded w-full px-2 py-1"/>
                    </div>
                </div>

                <div class="mt-4 flex justify-end gap-2">
                    <button wire:click="$set('editModal', false)" class="px-4 py-2 bg-gray-300 border rounded hover:bg-gray-200 transition-all">Cancel</button>
                    <button wire:click="updateStatus" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-500 transition-all">Save</button>
                </div>
            </div>
            <script>
                // Get references to the elements
                const activeRadio = document.getElementById('active');
                const disabledRadio = document.getElementById('disabled');
                const additionalInfoDiv = document.getElementById('disabledDateDiv');

                // Function to handle the visibility change
                function updateDivVisibility() {
                    if (disabledRadio.checked) {
                        // If 'Disabled' is selected, show the div
                        additionalInfoDiv.style.display = 'block';
                    } else {
                        // If 'Active' is selected, hide the div
                        additionalInfoDiv.style.display = 'none';
                    }
                }

                // Add event listeners to both radio buttons
                activeRadio.addEventListener('change', updateDivVisibility);
                disabledRadio.addEventListener('change', updateDivVisibility);

                // Call the function initially to set the correct state on page load
                updateDivVisibility();
            </script>
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
                            class="px-4 py-2 border rounded bg-gray-500 text-white  hover:bg-gray-400 transition-all">
                        Cancel
                    </button>

                    <button wire:click="saveWalletDetail"
                            class="px-4 py-2 bg-green-600 text-white rounded  hover:bg-green-500 transition-all">
                        Save
                    </button>
                </div>
            </div>
        </div>
    @endif

    <script>
        document.addEventListener('livewire:load', () => {
            // Listen to browser event
            window.addEventListener('wallet-added', event => {
                const alertBox = document.getElementById('wallet-alert');
                const messageSpan = document.getElementById('wallet-alert-message');
                const closeBtn = document.getElementById('wallet-alert-close');

                // Payload comes directly in event.detail
                messageSpan.textContent = event.detail.message ?? 'Success!';

                alertBox.classList.remove('hidden');

                // Close on click
                closeBtn.onclick = () => {
                    alertBox.classList.add('hidden');
                };

                // Keep it visible for 8 seconds
                setTimeout(() => {
                    alertBox.classList.add('hidden');
                }, 8000);
            });
        });
    </script>
</div>
