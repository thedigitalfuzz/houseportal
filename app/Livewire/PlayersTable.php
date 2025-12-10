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
        $user = $this->currentUser();

        if ($user->role === 'admin') {
            $this->allStaffs = Staff::all();
            $this->staff_id = null;
        } else {
            $this->staff_id = $user->id;
            $this->allStaffs = [];
        }

        $this->addModal = true;
    }

    public function openEditModal($id)
    {
        $player = Player::with('assignedStaff')->findOrFail($id);

        $this->editingPlayerId = $id;
        $this->player_name = $player->player_name ?? '';
        $this->username = $player->username;
        $this->facebook_profile = $player->facebook_profile ?? '';
        $this->phone = $player->phone ?? '';
        $this->staff_id = $player->staff_id;

        $user = $this->currentUser();
        $this->allStaffs = $user->role === 'admin' ? Staff::all() : [];

        $this->editModal = true;
    }

    public function savePlayer()
    {
        $rules = [
            'username' => 'required|string|max:255',
            'player_name' => 'required|string|max:255',
            'facebook_profile' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
        ];

        $validated = $this->validate($rules);

        if($this->editingPlayerId){
            $player = Player::findOrFail($this->editingPlayerId);
            $player->update(array_merge($validated, ['staff_id' => $this->staff_id]));
            $this->dispatch('playerUpdated');
            $this->editModal = false;
        } else {
            Player::create(array_merge($validated, ['staff_id' => $this->staff_id]));
            $this->dispatch('playerCreated');
            $this->addModal = false;
        }

        $this->reset(['editingPlayerId','username','player_name','facebook_profile','phone','staff_id']);
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

        $query = Player::with('assignedStaff')
            // Staff sees only their assigned players
            ->when($user->role !== 'admin', fn($q) => $q->where('staff_id', $user->id))
            // Admin filter by staff dropdown
            ->when($this->filter_staff_id && $user->role === 'admin', fn($q) => $q->where('staff_id', $this->filter_staff_id))
            // Search by username or player_name
            ->when($this->search, fn($q) => $q->where(function($p) {
                $p->where('username', 'like', '%'.$this->search.'%')
                    ->orWhere('player_name', 'like', '%'.$this->search.'%');
            }));

        $players = $query->orderBy('id','asc')->paginate($this->perPage);

        $this->allStaffs = $user->role === 'admin' ? Staff::all() : collect();

        return view('livewire.players-table', [
            'players' => $players,
            'currentUser' => $user,
           // 'allStaffs' => $allStaffs
        ]);
    }
}
