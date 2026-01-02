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
                    <td class="p-2">{{ $wd->created_at->format('Y-m-d') }}</td>
                    <td class="p-3 text-center space-x-2">


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
</div>
