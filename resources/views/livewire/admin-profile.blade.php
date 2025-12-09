<div>
    @if($modalOpen)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded shadow p-6 w-full max-w-md">
                <h2 class="text-xl font-bold mb-4">Edit Profile</h2>

                <div class="space-y-3">
                    <div>
                        <label class="block font-medium">Name</label>
                        <input type="text" wire:model="name" class="w-full border rounded p-2" />
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>

                    <div>
                        <label class="block font-medium">Photo</label>
                        <input type="file" wire:model="photo" class="w-full border rounded p-2" />
                        @if($photo)
                            <img src="{{ $photo->temporaryUrl() }}" class="mt-2 w-20 h-20 object-cover rounded-full" alt="Preview">
                        @elseif(Auth::user()->photo)
                            <img src="{{ asset('storage/' . Auth::user()->photo) }}" class="mt-2 w-20 h-20 object-cover rounded-full" alt="Current Photo">
                        @endif
                        <x-input-error :messages="$errors->get('photo')" class="mt-1" />
                    </div>

                    <div>
                        <label class="block font-medium">Current Password</label>
                        <input type="password" wire:model="current_password" class="w-full border rounded p-2" />
                        <x-input-error :messages="$errors->get('current_password')" class="mt-1" />
                    </div>

                    <div>
                        <label class="block font-medium">New Password</label>
                        <input type="password" wire:model="new_password" class="w-full border rounded p-2" />
                        <x-input-error :messages="$errors->get('new_password')" class="mt-1" />
                    </div>
                </div>

                <div class="mt-4 flex justify-end gap-2">
                    <button wire:click="$set('modalOpen', false)" class="px-4 py-2 border rounded">Cancel</button>
                    <button wire:click="saveProfile" class="px-4 py-2 bg-green-600 text-white rounded">Save</button>
                </div>
            </div>
        </div>
    @endif
</div>
