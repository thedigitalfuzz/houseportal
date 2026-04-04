<?php
namespace App\Livewire;

use Livewire\Component;
use App\Models\Game;
use App\Models\GameCreditCredential;

class GameCreditsCredentialsTable extends Component
{
    public $games;

    public $addModal = false;
    public $editModal = false;
    public $deleteModal = false;

    public $editingId;
    public $confirmDeleteId;

    public $game_id;
    public $type = 'subdistributor';
    public $username;
    public $password;
    public $filterGame = null;

    public function mount()
    {
        $this->games = Game::orderBy('name')->get();
    }

    public function openAddModal()
    {
        $this->reset(['editModal']);
        $this->reset(['editingId','game_id','type','username','password']);
        $this->type = 'subdistributor';
        $this->addModal = true;
    }

    public function openEditModal($id)
    {
        $this->reset(['addModal']);
        $record = GameCreditCredential::findOrFail($id);

        $this->editingId = $id;
        $this->game_id = $record->game_id;
        $this->type = $record->type;
        $this->username = $record->username;
        $this->password = $record->password;

        $this->editModal = true;
    }

    public function save()
    {
        $validated = $this->validate([
            'game_id' => 'required|exists:games,id',
            'type' => 'required|in:subdistributor,store',
            'username' => 'required|string|max:255',
            'password' => 'required|string|max:255',
        ]);

        if ($this->editingId) {
            GameCreditCredential::findOrFail($this->editingId)->update($validated);
            $this->editModal = false;
        } else {
            GameCreditCredential::create($validated);
            $this->addModal = false;
        }

        $this->reset(['editingId','game_id','type','username','password']);
    }

    public function confirmDelete($id)
    {
        $this->confirmDeleteId = $id;
        $this->deleteModal = true;
    }

    public function delete()
    {
        GameCreditCredential::findOrFail($this->confirmDeleteId)->delete();

        $this->deleteModal = false;
        $this->confirmDeleteId = null;
    }

    public function render()
    {
        return view('livewire.game-credits-credentials-table', [

            'subdistributors' => GameCreditCredential::with('game')
                ->when($this->filterGame, fn($q) => $q->where('game_id', $this->filterGame))
                ->where('type','subdistributor')
                ->get(),

            'stores' => GameCreditCredential::with('game')
                ->when($this->filterGame, fn($q) => $q->where('game_id', $this->filterGame))
                ->where('type','store')
                ->get(),
        ]);
    }
}
