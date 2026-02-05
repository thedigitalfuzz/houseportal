<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Player;
use App\Models\Staff;
use Illuminate\Support\Facades\Auth;

class PlayersTable extends Component
{
    use WithPagination;

    public $searchInput = '';
    public $search = '';
    public $perPage = 15;

    public $editModal = false;
    public $addModal = false;

    public $editingPlayerId;
    public $player_name;
    public $username;
    public $facebook_profile;
    public $phone;

    public $staff_id; // for add/edit
    public $filter_staff_id; // for admin filter dropdown
    public $allStaffs = [];

    public $confirmDeleteId = null;
    public $deleteModal = false;
    public $duplicateUsernameError = null;

    protected $listeners = [
        'playerCreated' => '$refresh',
        'playerUpdated' => '$refresh'
    ];

    public function updatingSearchInput()
    {
        $this->resetPage();
    }

    public function applySearch()
    {
        $this->search = $this->searchInput;
        $this->resetPage();
    }

    protected function currentUser()
    {
        return Auth::guard('web')->user() ?? Auth::guard('staff')->user();
    }

    public function canEdit(): bool
    {
        return $this->currentUser()?->role === 'admin';
    }

    public function canDelete(): bool
    {
        return $this->currentUser()?->role === 'admin';
    }

    public function openAddModal()
    {
        $this->reset(['editingPlayerId','username','player_name','facebook_profile','phone','staff_id']);
        $this->duplicateUsernameError = null;

        // BOTH admin and staff can select staff now
        $this->allStaffs = Staff::all();
        $this->staff_id = null;

        $this->addModal = true;
    }


    public function openEditModal($id)
    {
        $this->duplicateUsernameError = null;
        $player = Player::with('assignedStaff')->findOrFail($id);

        $this->editingPlayerId = $id;
        $this->player_name = $player->player_name ?? '';
        $this->username = $player->username;
        $this->facebook_profile = $player->facebook_profile ?? '';
        $this->phone = $player->phone ?? '';
        $this->staff_id = $player->staff_id;

        $this->allStaffs = Staff::all();

        $this->editModal = true;
    }

    public function savePlayer()
    {
        $user = $this->currentUser();
        if (Auth::guard('staff')->check()) {
            $userType = \App\Models\Staff::class;
        } else {
            $userType = \App\Models\User::class;
        }
        $this->duplicateUsernameError = null;

        $rules = [
            'username' => 'required|string|max:255|unique:players,username' . ($this->editingPlayerId ? ',' . $this->editingPlayerId : ''),
            'player_name' => 'required|string|max:255',
            'facebook_profile' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
        ];

        try {
            $validated = $this->validate($rules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($e->validator->errors()->has('username')) {
                $this->duplicateUsernameError = 'The player "' . $this->username . '" already exists.';
                return;
            }
            throw $e;
        }

        if ($this->editingPlayerId) {
            Player::findOrFail($this->editingPlayerId)
                ->update(array_merge($validated, [
                    'staff_id' => $this->staff_id,
                    'updated_by_id' => $user->id,
                    'updated_by_type' => $userType,
                ]));

            $this->dispatch('playerUpdated');
            $this->editModal = false;
        } else {
            Player::create(array_merge($validated, [
                'staff_id' => $this->staff_id,
                'created_by_id' => $user->id,
                'created_by_type' => $userType,
            ]));

            $this->dispatch('playerCreated');
            $this->addModal = false;
        }

        $this->reset([
            'editingPlayerId',
            'username',
            'player_name',
            'facebook_profile',
            'phone',
            'staff_id',
            'duplicateUsernameError'
        ]);
    }


    public function confirmDelete($id)
    {
        $this->confirmDeleteId = $id;
        $this->deleteModal = true;
    }

    public function deletePlayer()
    {
        Player::findOrFail($this->confirmDeleteId)->delete();
        $this->deleteModal = false;
        $this->confirmDeleteId = null;
        $this->resetPage();
    }

    public function render()
    {
        $user = $this->currentUser();

        $query = Player::with('assignedStaff', 'createdBy', 'updatedBy')
            // Allow BOTH admin and staff to see all players
            ->when($this->filter_staff_id, fn($q) => $q->where('staff_id', $this->filter_staff_id))
            ->when($this->search, fn($q) => $q->where(function($p) {
                $p->where('username', 'like', '%'.$this->search.'%')
                    ->orWhere('player_name', 'like', '%'.$this->search.'%');
            }));


        $players = $query->orderBy('id','asc')->paginate($this->perPage);

       // $this->allStaffs = Staff::all();

        return view('livewire.players-table', [
            'players' => $players,
            'currentUser' => $user,
           // 'allStaffs' => $allStaffs
        ]);
    }
}
