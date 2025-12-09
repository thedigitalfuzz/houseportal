<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AdminProfile extends Component
{
    use WithFileUploads;

    public $name;
    public $current_password;
    public $new_password;
    public $new_password_confirmation;
    public $photo;
    public $existingPhoto;

    public $modalOpen = false;

    protected $listeners = ['openAdminProfileModal' => 'openModal'];

    public function mount()
    {
        $user = Auth::user();

        if (!$user || $user->email !== 'admin@housesupport.us') {
            abort(403);
        }

        $this->name = $user->name;
        $this->existingPhoto = $user->photo ? asset('storage/' . $user->photo) : null;
    }

    public function openModal()
    {
        $this->modalOpen = true;
    }

    public function save()
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'current_password' => 'nullable|string',
            'new_password' => 'nullable|string|min:4|confirmed',
            'photo' => 'nullable|image|max:2048',
        ]);

        // If user wants to change password, validate current password
        if ($this->new_password) {
            if (!Hash::check($this->current_password, $user->password)) {
                throw ValidationException::withMessages([
                    'current_password' => 'Current password is incorrect.',
                ]);
            }
            $user->password = Hash::make($this->new_password);
        }

        $user->name = $validated['name'];

        if ($this->photo) {
            $path = $this->photo->store('admin_photos', 'public');
            $user->photo = $path;
        }

        $user->save();

        $this->existingPhoto = $user->photo ? asset('storage/' . $user->photo) : null;
        $this->reset(['current_password','new_password','new_password_confirmation','photo']);
        $this->modalOpen = false;

        session()->flash('message', 'Profile updated successfully.');
    }

    public function render()
    {
        return view('livewire.admin-profile');
    }
}
