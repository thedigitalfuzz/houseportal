<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminEditProfile extends Component
{
    use WithFileUploads;

    public $name;
    public $photo = null;         // FIX: initialize properly
    public $existingPhoto = null; // FIX: same pattern as Staffs
    public $current_password;
    public $new_password;

    public function mount()
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'admin') {
            abort(403);
        }

        $this->name = $user->name;
        $this->existingPhoto = $user->photo; // FIX: load existing photo
    }

    public function saveProfile()
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'photo' => 'nullable|image|max:2048',
            'current_password' => 'nullable|string',
            'new_password' => 'nullable|string|min:4',
        ]);

        if ($this->photo) {
            $path = $this->photo->store('admin_photos', 'public');
            $user->photo = $path;
        }

        $user->name = $this->name;

        if ($this->new_password) {
            if (!Hash::check($this->current_password, $user->password)) {
                $this->addError('current_password', 'Current password is incorrect.');
                return;
            }

            $user->password = Hash::make($this->new_password);
        }

        $user->save();

        session()->flash('success', 'Profile updated successfully.');
    }

    public function render()
    {
        return view('livewire.admin-edit-profile');
    }
}
