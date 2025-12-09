<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Transaction;
use App\Models\Game;
use App\Models\Player;

class TransactionsTable extends Component
{
    use WithPagination;

    public $searchInput = ''; // user types
    public $search = ''; // applied after clicking Search
    public $game_id = null;
    public $date_from = null;
    public $date_to = null;
    public $perPage = 15;

    // Edit modal properties
    public $editModal = false;
    public $editingTransactionId;
    public $editPlayerId;
    public $editGameId;
    public $editCashin;
    public $editCashout;
    public $editBonusAdded;
    public $editDeposit;
    public $editNotes;
    public $editTransactionTime;

    protected $listeners = ['transactionCreated' => '$refresh'];

    public function updatingSearchInput()
    {
        $this->resetPage();
    }

    public function applySearch()
    {
        $this->search = $this->searchInput;
        $this->resetPage();
    }
    public function canEdit(): bool
    {
        return auth()->user()->role === 'admin';
    }

    public function canDelete(): bool
    {
        return auth()->user()->role === 'admin';
    }
    public function deleteTransaction($id)
    {
        Transaction::findOrFail($id)->delete();
        $this->resetPage();
    }

    // Open edit modal and populate data
    public function editTransaction($id)
    {
        $transaction = Transaction::findOrFail($id);

        $this->editingTransactionId = $id;
        $this->editPlayerId = $transaction->player_id;
        $this->editGameId = $transaction->game_id;
        $this->editCashin = $transaction->cashin;
        $this->editCashout = $transaction->cashout;
        $this->editBonusAdded = $transaction->bonus_added;
        $this->editDeposit = $transaction->deposit;
        $this->editNotes = $transaction->notes;
        $this->editTransactionTime = $transaction->transaction_time;

        $this->editModal = true;
    }

    public function updateTransaction()
    {
        $this->validate([
            'editPlayerId' => 'required|exists:players,id',
            'editGameId' => 'required|exists:games,id',
            'editCashin' => 'nullable|numeric|min:0',
            'editCashout' => 'nullable|numeric|min:0',
            'editBonusAdded' => 'nullable|numeric|min:0',
            'editDeposit' => 'nullable|numeric|min:0',
            'editNotes' => 'nullable|string',
            'editTransactionTime' => 'required|date',
        ]);

        $transaction = Transaction::findOrFail($this->editingTransactionId);

        $transaction->update([
            'player_id' => $this->editPlayerId,
            'game_id' => $this->editGameId,
            'cashin' => $this->editCashin ?? 0,
            'cashout' => $this->editCashout ?? 0,
            'bonus_added' => $this->editBonusAdded ?? 0,
            'deposit' => $this->editDeposit ?? 0,
            'notes' => $this->editNotes,
            'transaction_time' => $this->editTransactionTime,
        ]);

        $this->editModal = false;
        $this->resetPage();
    }

    public function render()
    {
        $query = Transaction::with(['player','game'])
            ->when($this->game_id, fn($q) => $q->where('game_id', $this->game_id))
            ->when($this->date_from, fn($q) => $q->whereDate('transaction_time', '>=', $this->date_from))
            ->when($this->date_to, fn($q) => $q->whereDate('transaction_time', '<=', $this->date_to))
            ->when($this->search, fn($q) => $q->whereHas('player', fn($p) => $p->where('username','like','%'.$this->search.'%')));

        $transactions = $query->orderBy('transaction_time','desc')->paginate($this->perPage);

        return view('livewire.transactions-table', [
            'transactions' => $transactions,
            'games' => Game::all(),
            'players' => Player::all(),
        ]);
    }
}

//players table
namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Player;

class PlayersTable extends Component
{
    use WithPagination;

    public $searchInput = '';
    public $search = '';
    public $perPage = 15;

    // Add/Edit modal properties
    public $editModal = false;
    public $addModal = false;
    public $editingPlayerId;
    public $username;
    public $facebook_link;
    public $phone;

    protected $listeners = ['playerCreated' => '$refresh', 'playerUpdated' => '$refresh'];

    public function updatingSearchInput()
    {
        $this->resetPage();
    }

    public function applySearch()
    {
        $this->search = $this->searchInput;
        $this->resetPage();
    }

    public function canEdit(): bool
    {
        return auth()->user()->role === 'admin';
    }

    public function canDelete(): bool
    {
        return auth()->user()->role === 'admin';
    }

    // OPEN ADD MODAL
    public function openAddModal()
    {
        $this->reset(['editingPlayerId', 'username', 'facebook_link', 'phone']);
        $this->addModal = true;
    }

    // OPEN EDIT MODAL
    public function openEditModal($id)
    {
        $player = Player::findOrFail($id);
        $this->editingPlayerId = $id;
        $this->username = $player->username;
        $this->facebook_link = $player->facebook_profile ?? '';
        $this->phone = $player->phone ?? '';
        $this->editModal = true;
    }

    // SAVE PLAYER (Add or Update)
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

        $this->reset(['editingPlayerId', 'username', 'facebook_link', 'phone']);
    }

    // DELETE PLAYER
    public function deletePlayer($id)
    {
        Player::findOrFail($id)->delete();
        $this->resetPage();
    }

    public function render()
    {
        $query = Player::query()
            ->when($this->search, fn($q) => $q->where('username', 'like', '%' . $this->search . '%'));

        $players = $query->orderBy('id', 'asc')->paginate($this->perPage);

        return view('livewire.players-table', [
            'players' => $players,
        ]);
    }
}


//games table


namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Game;

class GamesTable extends Component
{
    use WithPagination;

    public $searchInput = '';
    public $search = '';
    public $perPage = 15;

    // Add/Edit modal properties
    public $modalOpen = false;
    public $editingGameId;
    public $name;
    public $game_code;

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

    public function canEdit(): bool
    {
        return auth()->user()->role === 'admin';
    }

    public function canDelete(): bool
    {
        return auth()->user()->role === 'admin';
    }

    public function openAddModal()
    {
        $this->reset(['editingGameId', 'name', 'game_code']);
        $this->modalOpen = true;
    }

    public function openEditModal($id)
    {
        $game = Game::findOrFail($id);
        $this->editingGameId = $id;
        $this->name = $game->name;
        $this->game_code = $game->game_code ?? '';
        $this->modalOpen = true;
    }

    public function saveGame()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'game_code' => 'nullable|string|max:100',
        ]);

        if ($this->editingGameId) {
            // Update
            $game = Game::findOrFail($this->editingGameId);
            $game->update($validated);
            $this->dispatch('gameUpdated');
        } else {
            // Create
            Game::create($validated);
            $this->dispatch('gameAdded');
        }

        $this->modalOpen = false;
        $this->reset(['editingGameId', 'name', 'game_code']);
    }

    public function deleteGame($id)
    {
        Game::findOrFail($id)->delete();
        $this->resetPage();
    }

    public function render()
    {
        $query = Game::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%'));

        $games = $query->orderBy('id', 'asc')->paginate($this->perPage);

        return view('livewire.games-table', [
            'games' => $games,
        ]);
    }
}
