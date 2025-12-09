<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Transaction;
use App\Models\Game;
use App\Models\Player;
use Illuminate\Support\Facades\Auth;

class TransactionsTable extends Component
{
    use WithPagination;

    public $searchInput = '';
    public $search = '';
    public $game_id = null;
    public $date_from = null;
    public $date_to = null;
    public $perPage = 15;

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

    // NEW DELETE CONFIRMATION
    public $confirmDeleteId = null;
    public $deleteModal = false;

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

    // OPEN CONFIRM DELETE MODAL
    public function confirmDelete($id)
    {
        $this->confirmDeleteId = $id;
        $this->deleteModal = true;
    }

    // DELETE AFTER CONFIRM
    public function deleteTransaction()
    {
        Transaction::findOrFail($this->confirmDeleteId)->delete();
        $this->deleteModal = false;
        $this->confirmDeleteId = null;
        $this->resetPage();
    }

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
