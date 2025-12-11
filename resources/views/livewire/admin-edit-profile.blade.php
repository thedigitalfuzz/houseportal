<div class="max-w-md mt-8">

    @if (session()->has('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="space-y-3 p-4 bg-white">

        <!-- Name -->
        <div>
            <label class="block font-medium">Name</label>
            <input type="text" wire:model="name" class="w-full border rounded p-2" />
            <x-input-error :messages="$errors->get('name')" class="mt-1" />
        </div>



        <!-- Passwords -->
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

        <!-- Photo -->
        <div>
            <label class="block font-medium">Photo</label>
            <input type="file" wire:model="photo" class="w-full border rounded p-2" />

            <!-- Preview new upload -->
            @if ($photo)
                <img src="{{ $photo->temporaryUrl() }}" class="mt-2 w-20 h-20 object-cover rounded-full" alt="Preview">

                <!-- Show existing photo -->
            @elseif ($existingPhoto)
                <img src="{{ asset('storage/' . $existingPhoto) }}" class="mt-2 w-20 h-20 object-cover rounded-full" alt="Current Photo">
            @endif

            <x-input-error :messages="$errors->get('photo')" class="mt-1" />
        </div>

    </div>

    <div class="mt-4 flex justify-end gap-2">
        <a href="{{ route('dashboard') }}" class="px-4 bg-white py-2 border rounded">Cancel</a>
        <button wire:click="saveProfile" class="px-4 py-2 bg-green-600 text-white rounded">Save</button>
    </div>
</div>
