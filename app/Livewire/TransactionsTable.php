<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Transaction;
use App\Models\Game;
use App\Models\Player;
use App\Models\Staff;
use Illuminate\Support\Facades\Auth;

class TransactionsTable extends Component
{
    use WithPagination;

    public $searchInput = '';
    public $search = '';
    public $game_id = null;
    public $staff_id = null; // only for admin filter
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
    public $editCashTag;
    public $editWalletName;
    public $editWalletRemarks;
    public $editDeposit;
    public $editNotes;
    public $editTransactionTime;

    public $confirmDeleteId = null;
    public $deleteModal = false;

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

    #[\Livewire\Attributes\On('transactionCreated')]
    public function refreshTable()
    {
        $this->resetPage();
    }

    public function confirmDelete($id)
    {
        $this->confirmDeleteId = $id;
        $this->deleteModal = true;
    }

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
        $user = $this->currentUser();

        if ($user->role !== 'admin' && $transaction->player->staff_id !== $user->id) {
            $this->dispatchBrowserEvent('notify', ['type' => 'error', 'message' => 'You cannot edit this transaction.']);
            return;
        }

        $this->editingTransactionId = $id;
        $this->editPlayerId = $transaction->player_id;
        $this->editGameId = $transaction->game_id;
        $this->editCashin = $transaction->cashin;
        $this->editCashout = $transaction->cashout;
        $this->editBonusAdded = $transaction->bonus_added;
        $this->editCashTag = $transaction->cash_tag;
        $this->editWalletName = $transaction->wallet_name;
        $this->editWalletRemarks = $transaction->wallet_remarks;
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
            'editCashTag' => 'nullable|string|max:255',
            'editWalletName' => 'nullable|string|max:255',
            'editWalletRemarks' => 'nullable|string|max:255',
            'editDeposit' => 'nullable|numeric|min:0',
            'editNotes' => 'nullable|string',
            'editTransactionTime' => 'required|date',
        ]);

        $total = ($this->editCashin ?? 0) - ($this->editCashout ?? 0);

        $transaction = Transaction::findOrFail($this->editingTransactionId);

        $transaction->update([
            'player_id' => $this->editPlayerId,
            'game_id' => $this->editGameId,
            'cashin' => $this->editCashin ?? 0,
            'cashout' => $this->editCashout ?? 0,
            'total_transaction' => $total,
            'bonus_added' => $this->editBonusAdded ?? 0,
            'cash_tag' => $this->editCashTag,
            'wallet_name' => $this->editWalletName,
            'wallet_remarks' => $this->editWalletRemarks,
            'deposit' => $this->editDeposit ?? 0,
            'notes' => $this->editNotes,
            'transaction_time' => $this->editTransactionTime,
        ]);

        $this->editModal = false;
        $this->resetPage();
    }

    public function render()
    {
        $user = $this->currentUser();

        $query = Transaction::with(['player.assignedStaff','game'])
            ->when($user->role !== 'admin', fn($q) => $q->whereHas('player', fn($p) => $p->where('staff_id', $user->id)))
            ->when($this->game_id, fn($q) => $q->where('game_id', $this->game_id))
            ->when($this->date_from, fn($q) => $q->whereDate('transaction_time', '>=', $this->date_from))
            ->when($this->date_to, fn($q) => $q->whereDate('transaction_time', '<=', $this->date_to))
            ->when($this->search, fn($q) => $q->whereHas('player', function($p) use ($user) {
                $p->where('username','like','%'.$this->search.'%')
                    ->orWhere('player_name','like','%'.$this->search.'%');
            }))
            ->when($this->staff_id && $user->role === 'admin', fn($q) => $q->whereHas('player', fn($p) => $p->where('staff_id', $this->staff_id)));

        $transactions = $query->orderBy('transaction_time','desc')->paginate($this->perPage);

        $allStaffs = $user->role === 'admin' ? Staff::all() : collect();

        // Filter players for edit modal
        $players = $user->role === 'admin'
            ? Player::all()
            : Player::where('staff_id', $user->id)->get();

        return view('livewire.transactions-table', [
            'transactions' => $transactions,
            'games' => Game::all(),
            'players' => $players, // <-- now only assigned players for staff
            'currentUser' => $user,
            'allStaffs' => $allStaffs,
        ]);
    }
}
