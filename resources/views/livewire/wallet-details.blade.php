<div class="p-4">
    <div class="flex items-start md:items-center justify-between flex-col md:flex-row mb-4 gap-2">
        <h1 class="text-3xl font-bold">Wallet Details</h1>

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
                <th class="p-3 text-center">Actions</th>
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
                    <td class="p-3 text-center space-x-2">
                        <button wire:click="openEditModal({{ $wd->id }})"
                                class="px-2 py-1 bg-blue-600 text-white rounded">
                            Edit
                        </button>

                        <button wire:click="confirmDelete({{ $wd->id }})"
                                class="px-2 py-1 bg-red-600 text-white rounded">
                            Delete
                        </button>

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
                            class="px-4 py-2 border rounded">
                        Cancel
                    </button>
                    <button wire:click="deleteConfirmed"
                            class="px-4 py-2 bg-red-600 text-white rounded">
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
                    <button wire:click="$set('editModal', false)" class="px-4 py-2 border rounded">Cancel</button>
                    <button wire:click="updateStatus" class="px-4 py-2 bg-green-600 text-white rounded">Save</button>
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


</div>
