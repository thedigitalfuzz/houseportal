<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Hash;
use App\Models\Staff;
use Livewire\WithFileUploads;

class StaffsTable extends Component
{
    use WithPagination, WithFileUploads;

    public $searchInput = '';
    public $search = '';
    public $perPage = 15;

    public $modalOpen = false;
    public $editingStaffId;
    public $staff_name;
    public $staff_username;
    public $email;
    public $password;
    public $plain_password;
    public $facebook_profile;
    public $photo; // for upload
    public $existingPhoto; // to show current photo in edit modal

    public $confirmDeleteId = null;
    public $deleteModalOpen = false;

    protected $listeners = ['staffAdded' => '$refresh', 'staffUpdated' => '$refresh'];

    public function updatingSearchInput()
    {
        $this->resetPage();
    }

    public function applySearch()
    {
        $this->search = $this->searchInput;
        $this->resetPage();
    }

    public function mount()
    {
        // Only allow admin to access this component
        if (auth()->user()?->role !== 'admin') {
            abort(403, 'Unauthorized access');
        }
    }


    public function openAddModal()
    {
        $this->reset([
            'editingStaffId','staff_name','staff_username','email',
            'password','photo','existingPhoto'
        ]);

        $this->modalOpen = true;
    }

    public function openEditModal($id)
    {
        $staff = Staff::findOrFail($id);

        $this->editingStaffId = $id;
        $this->staff_name = $staff->staff_name;
        $this->staff_username = $staff->staff_username;
        $this->email = $staff->email;
        $this->password = '';
        $this->plain_password = $staff->staff_plain_password;
        $this->facebook_profile = $staff->facebook_profile;
        $this->existingPhoto = $staff->photo;

        $this->modalOpen = true;
    }

    public function saveStaff()
    {
        $rules = [
            'staff_name' => 'required|string|max:255',
            'staff_username' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:staffs,email,' . ($this->editingStaffId ?? 'NULL'),
            'password' => $this->editingStaffId ? 'nullable|string|min:4' : 'required|string|min:4',
            'facebook_profile' => 'nullable|string|max:255',
            'photo' => 'nullable|image|max:2048',
        ];

        $validated = $this->validate($rules);

        if ($this->photo) {
            $path = $this->photo->store('staff_photos', 'public');
            $validated['photo'] = $path;
        }

        if ($this->editingStaffId) {
            $staff = Staff::findOrFail($this->editingStaffId);
            $staff->update([
                'staff_name' => $validated['staff_name'],
                'staff_username' => $validated['staff_username'],
                'email' => $validated['email'],
                'staff_plain_password' => $validated['password'] ?: $staff->staff_plain_password,
                'password' => $validated['password'] ? Hash::make($validated['password']) : $staff->password,
                'facebook_profile' => $validated['facebook_profile'],
                'photo' => $validated['photo'] ?? $staff->photo,
            ]);
        } else {
            Staff::create([
                'staff_name' => $validated['staff_name'],
                'staff_username' => $validated['staff_username'],
                'email' => $validated['email'],
                'staff_plain_password' => $validated['password'],
                'password' => Hash::make($validated['password']),
                'facebook_profile' => $validated['facebook_profile'],
                'photo' => $validated['photo'] ?? null,
            ]);
        }

        $this->modalOpen = false;

        $this->reset([
            'editingStaffId','staff_name','staff_username','email',
            'password', 'facebook_profile','photo','existingPhoto'
        ]);
    }

    // OPEN THE CONFIRMATION MODAL
    public function confirmDelete($id)
    {
        $this->confirmDeleteId = $id;
        $this->deleteModalOpen = true;
    }

    // FINAL DELETE WHEN CLICK YES
    public function deleteStaff()
    {
        Staff::findOrFail($this->confirmDeleteId)->delete();

        $this->deleteModalOpen = false;
        $this->confirmDeleteId = null;

        $this->resetPage();
    }

    public function render()
    {
        $query = Staff::query()
            ->when($this->search, fn($q) =>
            $q->where('staff_name', 'like', '%'.$this->search.'%')
                ->orWhere('staff_username', 'like', '%'.$this->search.'%')
                ->orWhere('email', 'like', '%'.$this->search.'%')
            );

        $staffs = $query->orderBy('id','asc')->paginate($this->perPage);

        return view('livewire.staffs-table', ['staffs' => $staffs]);
    }
}
