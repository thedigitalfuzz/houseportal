<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Game;
use Illuminate\Support\Facades\Auth;

class GamesTable extends Component
{
    use WithPagination;

    public $searchInput = '';
    public $search = '';
    public $perPage = 15;

    public $modalOpen = false;
    public $editingGameId;
    public $name;
    public $game_code;
    public $backend_link;

    // NEW DELETE CONFIRMATION
    public $confirmDeleteId = null;
    public $deleteModal = false;
    public $duplicateGameError = null;

    protected $listeners = ['gameAdded' => '$refresh', 'gameUpdated' => '$refresh'];

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
        $this->reset(['editingGameId', 'name', 'game_code', 'backend_link']);
        $this->duplicateGameError = null; // ✅ CLEAR ERROR
        $this->modalOpen = true;
    }

    public function openEditModal($id)
    {
        $this->duplicateGameError = null;
        $game = Game::findOrFail($id);
        $this->editingGameId = $id;
        $this->name = $game->name;
        $this->game_code = $game->game_code ?? '';
        $this->modalOpen = true;
    }

    public function saveGame()
    {
        $this->duplicateGameError = null;

        // Check duplicate name
        $exists = Game::where('name', $this->name)
            ->when($this->editingGameId, fn ($q) => $q->where('id', '!=', $this->editingGameId))
            ->exists();

        if ($exists) {
            $this->duplicateGameError = 'The game "' . $this->name . '" already exists.';
            return;
        }

        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'game_code' => 'nullable|string|max:100',
            'backend_link' => 'nullable|string|max:255',
        ]);

        if ($this->editingGameId) {
            $game = Game::findOrFail($this->editingGameId);
            $game->update($validated);
            $this->dispatch('gameUpdated');
        } else {
            Game::create($validated);
            $this->dispatch('gameAdded');
        }

        $this->modalOpen = false;
        $this->reset(['editingGameId', 'name', 'game_code', 'backend_link']);
    }

    // OPEN CONFIRM DELETE MODAL
    public function confirmDelete($id)
    {
        $this->confirmDeleteId = $id;
        $this->deleteModal = true;
    }

    // DELETE AFTER CONFIRM
    public function deleteGame()
    {
        Game::findOrFail($this->confirmDeleteId)->delete();
        $this->deleteModal = false;
        $this->confirmDeleteId = null;
        $this->resetPage();
    }

    public function render()
    {
        $query = Game::query()
            ->when($this->search, fn($q) => $q->where('name','like','%'.$this->search.'%'));

        $games = $query->orderBy('id','asc')->paginate($this->perPage);

        return view('livewire.games-table', [
            'games' => $games,
        ]);
    }
}
