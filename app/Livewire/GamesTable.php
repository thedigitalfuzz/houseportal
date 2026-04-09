<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Game;
use Illuminate\Support\Facades\Auth;
use App\Helpers\NotificationHelper;
use App\Models\User;
use App\Models\Staff;


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
    public $game_link;
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
        $this->reset(['editingGameId', 'name', 'game_code', 'backend_link', 'game_link']);
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
        $this->backend_link = $game->backend_link ?? '';
        $this->game_link = $game->game_link ?? ''; // ✅ ADD THIS
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
            'game_link' => 'nullable|string|max:255',
        ]);

        if ($this->editingGameId) {
            $game = Game::findOrFail($this->editingGameId);

            // track old values
            $oldGameLink = $game->game_link;

            $game->update($validated);

            // 🔥 General update notification
            $this->sendGameNotification(
                'Game Updated',
                "Game {$game->name} has been updated on " . now()->format('Y-m-d')
            );

            // 🔥 Game link updated notification (ONLY if changed)
            if ($oldGameLink !== $this->game_link) {
                $this->sendGameNotification(
                    'Game Link Updated',
                    "Game {$game->name} link has been updated on " . now()->format('Y-m-d')
                );
            }

            $this->dispatch('gameUpdated');
        }else {
            Game::create($validated);
            $this->dispatch('gameAdded');
        }

        if (!$this->editingGameId) {
            $this->sendGameNotification(
                'New Game Alert',
                "Game-  {$this->name} has been added on " . now()->format('Y-m-d')
            );
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
        $game = Game::findOrFail($this->confirmDeleteId);

        $gameName = $game->name;

        $game->delete();

        $this->sendGameNotification(
            'Game Deleted',
            "Game {$gameName} has been deleted on " . now()->format('Y-m-d')
        );

        $this->deleteModal = false;
        $this->confirmDeleteId = null;
        $this->resetPage();
    }

    protected function sendGameNotification($type, $message)
    {
        $users = collect()
            ->merge(\App\Models\User::all())
            ->merge(\App\Models\Staff::all());

        NotificationHelper::send($users, $type, $message, '/games');

        // refresh notification bell everywhere
        $this->dispatch('refreshNotifications');
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
