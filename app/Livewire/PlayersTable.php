<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Player;
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
    public $username;
    public $facebook_link;
    public $phone;

    // NEW DELETE CONFIRMATION
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
        $this->reset(['editingPlayerId','username','facebook_link','phone']);
        $this->addModal = true;
    }

    public function openEditModal($id)
    {
        $player = Player::findOrFail($id);

        $this->editingPlayerId = $id;
        $this->username = $player->username;
        $this->facebook_link = $player->facebook_profile ?? '';
        $this->phone = $player->phone ?? '';

        $this->editModal = true;
    }

    public function savePlayer()
    {
        $rules = [
            'username' => 'required|string|max:255',
            'facebook_link' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
        ];

        $validated = $this->validate($rules);

        if ($this->editingPlayerId) {
            $player = Player::findOrFail($this->editingPlayerId);
            $player->update($validated);
            $this->dispatch('playerUpdated');
            $this->editModal = false;
        } else {
            Player::create($validated);
            $this->dispatch('playerCreated');
            $this->addModal = false;
        }

        $this->reset(['editingPlayerId','username','facebook_link','phone']);
    }

    // OPEN CONFIRM DELETE MODAL
    public function confirmDelete($id)
    {
        $this->confirmDeleteId = $id;
        $this->deleteModal = true;
    }

    // DELETE AFTER CONFIRM
    public function deletePlayer()
    {
        Player::findOrFail($this->confirmDeleteId)->delete();
        $this->deleteModal = false;
        $this->confirmDeleteId = null;
        $this->resetPage();
    }

    public function render()
    {
        $query = Player::query()
            ->when($this->search, fn($q) =>
            $q->where('username','like','%'.$this->search.'%')
            );

        $players = $query->orderBy('id','asc')->paginate($this->perPage);

        return view('livewire.players-table', [
            'players' => $players,
        ]);
    }
}
