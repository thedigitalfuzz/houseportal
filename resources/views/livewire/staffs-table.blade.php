<div>

    <div class="mb-4 flex flex-col md:flex-row justify-between md:items-center gap-2">
        <div>
            <button wire:click="openAddModal" class="px-4 py-2 bg-green-600 text-white rounded">Add Staff</button>
        </div>

        <div class="flex gap-2 flex-col md:flex-row">
            <input type="text" wire:model="searchInput" placeholder="Search staff" class="border rounded px-2 py-1" />
            <div>
                <button wire:click="applySearch" class="px-4 py-1 bg-blue-600 text-white rounded">Search</button>
            </div>

        </div>


    </div>
    <div class="grid grid-cols-1">
    <div class="bg-white rounded shadow overflow-x-auto">
        <table class="min-w-full table-auto">
            <thead class="bg-gray-50">
            <tr>
                <th class="p-3 text-left">ID</th>
                <th class="p-3 text-left">Name</th>
                <th class="p-3 text-left">Username</th>
                <th class="p-3 text-left">Email</th>
                <th class="p-3 text-left">Password (plain)</th>
                <th class="p-3 text-left">Facebook Profile</th>
                <th class="p-3 text-left">Created At</th>
                <th class="px-4 py-2 text-right">Actions</th>
            </tr>
            </thead>

            <tbody>
            @forelse($staffs as $staff)
                <tr class="border-t">
                    <td class="p-3">{{ $staff->id }}</td>
                    <td class="p-3">{{ $staff->staff_name }}</td>
                    <td class="p-3">{{ $staff->staff_username }}</td>
                    <td class="p-3">{{ $staff->email }}</td>
                    <td class="p-3">{{ $staff->staff_plain_password }}</td>
                    <td class="p-3">{{ $staff->facebook_profile }}</td>
                    <td class="p-3">{{ $staff->created_at->format('Y-m-d H:i') }}</td>

                    <td class="p-3 text-right flex justify-end gap-2">
                        <button wire:click="openEditModal({{ $staff->id }})"
                                class="bg-yellow-500 text-white px-3 py-1 rounded">Edit</button>

                        <!-- Custom Delete modal button -->
                        <button wire:click="confirmDelete({{ $staff->id }})"
                                class="bg-red-600 text-white px-3 py-1 rounded">Delete</button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="p-3 text-center">No staffs found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    </div>
    <div class="mt-3">
        {{ $staffs->links() }}
    </div>


    {{-- ADD / EDIT MODAL --}}
    @if($modalOpen)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded shadow p-6 w-full max-w-md">

                <h2 class="text-xl font-bold mb-4">
                    {{ $editingStaffId ? 'Edit Staff' : 'Add Staff' }}
                </h2>

                <div class="space-y-3">
                    <input type="text" wire:model="staff_name" placeholder="Name" class="w-full border rounded p-2" />
                    <input type="text" wire:model="staff_username" placeholder="Username" class="w-full border rounded p-2" />
                    <input type="email" wire:model="email" placeholder="Email" class="w-full border rounded p-2" />
                    @if($editingStaffId)
                        <div class="flex items-center gap-1">
                            <label for="currentpw" class="text-sm">Current Password:</label>
                            <input type="text" name="currentpw" wire:model="plain_password" placeholder="Password" class="w-full border rounded" disabled />

                        </div>
                      @endif
                     <input type="text" wire:model="password" placeholder="Password" class="w-full border rounded p-2" />

                    <input type="text" wire:model="facebook_profile" placeholder="Facebook Profile" class="border rounded w-full p-2">
                    <!-- Photo Upload -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Staff Photo</label>
                        <input type="file" wire:model="photo" accept="image/*" class="w-full" />
                        <x-input-error :messages="$errors->get('photo')" class="mt-1" />

                        @if ($photo)
                            <div class="mt-2">
                                <span class="text-sm text-gray-600">Preview:</span>
                                <img src="{{ $photo->temporaryUrl() }}" class="w-24 h-24 rounded-full mt-1 object-cover" alt="Preview">
                            </div>
                        @elseif($editingStaffId && $existingPhoto)
                            <div class="mt-2">
                                <span class="text-sm text-gray-600">Current Photo:</span>
                                <img src="{{ asset('storage/' . $existingPhoto) }}" class="w-[160px] h-[160px] rounded-full mt-1 object-cover" alt="Staff Photo">
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mt-4 flex justify-end gap-2">
                    <button wire:click="$set('modalOpen', false)" class="px-4 py-2 border rounded bg-gray-500 text-white">Cancel</button>
                    <button wire:click="saveStaff" class="px-4 py-2 bg-green-600 text-white rounded">
                        {{ $editingStaffId ? 'Save Changes' : 'Add Staff' }}
                    </button>
                </div>

            </div>
        </div>
    @endif


    {{-- DELETE CONFIRMATION MODAL --}}
    @if($deleteModalOpen)
        <div class="fixed inset-0 bg-black bg-opacity-50  flex items-center justify-center z-50">
            <div class="bg-white rounded shadow  p-6 w-80 max-w-sm text-center">

                <h2 class="text-lg font-bold mb-4">
                    Confirm Delete
                </h2>

                <p class="mb-6">Are you sure you want to delete this staff?</p>

                <div class="flex justify-center gap-4">
                    <button wire:click="$set('deleteModalOpen', false)"
                            class="px-4 py-2 border rounded">
                        Cancel
                    </button>

                    <button wire:click="deleteStaff"
                            class="px-4 py-2 bg-red-600 text-white rounded">
                        Yes, Delete
                    </button>
                </div>

            </div>
        </div>
    @endif

</div>
