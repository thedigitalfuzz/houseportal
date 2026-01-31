<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Transaction;
use App\Models\Game;
use App\Models\WalletDetail;
use App\Models\Player;
use App\Models\Staff;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class TransactionsTable extends Component
{
    use WithPagination;

    public $searchInput = '';
    public $search = '';
    public $game_id = null;
    public $staff_id = null;
    public $date_from = null;
    public $date_to = null;
    public $perPage = 15;

    public $editModal = false;
    public $editingTransactionId;
    public $editPlayerId;
    public $editGameId;
    public $editCashin;
    public $editCashout;
    public $editTransactionType;
    public $editAmount;

    public $editBonusAdded;
    public $editCashTag;
    public $editAgent;
    public $editWalletName;
    public $editWalletRemarks;

    public $editAgents = [];
    public $editWalletNames = [];
    public $editWalletRemarksOptions = [];
    public $editDeposit;
    public $editNotes;
    public $editTransactionTime;
    public $editTransactionDate;
    public $wallet_agent = null;
    public $wallet_name = null;
    public $wallet_remarks = null;

    public $walletAgents = [];
    public $walletNames = [];
    public $walletRemarksOptions = [];

    public $editPlayerSearch = '';
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


    public function mount()
    {
        $this->editAgents = WalletDetail::select('agent')
            ->distinct()
            ->orderBy('agent')
            ->pluck('agent')
            ->toArray();

        $this->walletAgents = WalletDetail::select('agent')
            ->distinct()
            ->orderBy('agent')
            ->pluck('agent')
            ->toArray();
    }

    public function updatedWalletAgent()
    {
        $this->wallet_name = null;
        $this->wallet_remarks = null;

        if (!$this->wallet_agent) {
            $this->walletNames = [];
            $this->walletRemarksOptions = [];
            return;
        }

        $this->walletNames = WalletDetail::where('agent', $this->wallet_agent)
            ->select('wallet_name')
            ->distinct()
            ->orderBy('wallet_name')
            ->pluck('wallet_name')
            ->toArray();

        $this->walletRemarksOptions = [];
    }

    public function updatedWalletName()
    {
        $this->wallet_remarks = null;

        if (!$this->wallet_agent || !$this->wallet_name) {
            $this->walletRemarksOptions = [];
            return;
        }

        $this->walletRemarksOptions = WalletDetail::where('agent', $this->wallet_agent)
            ->where('wallet_name', $this->wallet_name)
            ->orderBy('wallet_remarks')
            ->pluck('wallet_remarks')
            ->toArray();
    }

    public function updatedEditAgent()
    {
        $this->editWalletName = null;
        $this->editWalletRemarks = null;

        if (!$this->editAgent) {
            $this->editWalletNames = [];
            $this->editWalletRemarksOptions = [];
            return;
        }

        $this->editWalletNames = WalletDetail::where('agent', $this->editAgent)
            ->select('wallet_name')
            ->distinct()
            ->orderBy('wallet_name')
            ->pluck('wallet_name')
            ->toArray();

        $this->editWalletRemarksOptions = [];
    }
    public function updatedEditWalletName()
    {
        $this->editWalletRemarks = null;

        if (!$this->editAgent || !$this->editWalletName) {
            $this->editWalletRemarksOptions = [];
            return;
        }

        $this->editWalletRemarksOptions = WalletDetail::where('agent', $this->editAgent)
            ->where('wallet_name', $this->editWalletName)
            ->orderBy('wallet_remarks')
            ->pluck('wallet_remarks')
            ->toArray();
    }





    public function editTransaction($id)
    {
        $transaction = Transaction::findOrFail($id);
        $user = $this->currentUser();

       // if ($user->role !== 'admin' && $transaction->player->staff_id !== $user->id) {
         //   $this->dispatchBrowserEvent('notify', ['type' => 'error', 'message' => 'You cannot edit this transaction.']);
           // return;
        //}

        $this->editingTransactionId = $id;
        $this->editPlayerId = $transaction->player_id;
        $this->editPlayerSearch = $transaction->player->username;

        $this->editGameId = $transaction->game_id;
        $this->editCashin = $transaction->cashin;
        $this->editCashout = $transaction->cashout;
        $this->editTransactionType = $transaction->cashin > 0 ? 'cashin' : 'cashout';
        $this->editAmount = $transaction->cashin > 0 ? $transaction->cashin : $transaction->cashout;

        $this->editBonusAdded = $transaction->bonus_added;
        $this->editCashTag = $transaction->cash_tag;


        $this->editDeposit = $transaction->deposit;
        $this->editNotes = $transaction->notes;
        $this->editAgent = $transaction->agent;
        $this->editWalletName = $transaction->wallet_name;
        $this->editWalletRemarks = $transaction->wallet_remarks;

        // Populate wallet names for selected agent
        $this->editWalletNames = WalletDetail::where('agent', $this->editAgent)
            ->select('wallet_name')
            ->distinct()
            ->pluck('wallet_name')
            ->toArray();

        // Populate wallet remarks for selected agent + wallet
        $this->editWalletRemarksOptions = WalletDetail::where('agent', $this->editAgent)
            ->where('wallet_name', $this->editWalletName)
            ->pluck('wallet_remarks')
            ->toArray();

        // Only date for display
        $this->editTransactionDate = $transaction->transaction_date
            ? Carbon::parse($transaction->transaction_date)->format('Y-m-d')
            : now()->format('Y-m-d');

        $this->editModal = true;
    }

    public function updateTransaction()
    {
        $this->editPlayerId = Player::where('username', $this->editPlayerSearch)->value('id');
        $this->validate([
            'editPlayerId' => 'required|exists:players,id',
            'editGameId' => 'required|exists:games,id',
            'editCashin' => 'nullable|numeric|min:0',
            'editCashout' => 'nullable|numeric|min:0',
            'editTransactionType' => 'required|in:cashin,cashout',
            'editAmount' => 'required|numeric|min:0',

            'editBonusAdded' => 'nullable|numeric|min:0',
            'editCashTag' => 'nullable|string|max:255',
            'editAgent' => 'nullable|string|max:255',
            'editWalletName' => 'nullable|string|max:255',
            'editWalletRemarks' => 'nullable|string|max:255',
            'editDeposit' => 'nullable|numeric|min:0',
            'editNotes' => 'nullable|string',
            'editTransactionDate' => 'required|date',
        ]);

        $cashin = 0;
        $cashout = 0;

        if ($this->editTransactionType === 'cashin') {
            $cashin = $this->editAmount;
        } else {
            $cashout = $this->editAmount;
        }

        $total = $cashin - $cashout;

        $bonusAdded = $this->editBonusAdded !== null && $this->editBonusAdded !== ''
            ? $this->editBonusAdded
            : 0;

        $transaction = Transaction::findOrFail($this->editingTransactionId);
        $transaction->update([
            'player_id' => $this->editPlayerId,
            'game_id' => $this->editGameId,
            'cashin' => $this->editTransactionType === 'cashin' ? floatval($this->editAmount) : 0,
            'cashout' => $this->editTransactionType === 'cashout' ? floatval($this->editAmount) : 0,
            'total_transaction' => $this->editTransactionType === 'cashin' ? floatval($this->editAmount) : -floatval($this->editAmount),
            'bonus_added' => floatval($bonusAdded),
            'cash_tag' => $this->editCashTag,
            'agent' => $this->editAgent, // <--- add this line
            'wallet_name' => $this->editWalletName,
            'wallet_remarks' => $this->editWalletRemarks,
            'deposit' => $this->editDeposit ?? 0,
            'notes' => $this->editNotes,
            'transaction_date' => $this->editTransactionDate,
            // remove transaction_time completely
        ]);

        $this->editModal = false;
        $this->resetPage();
    }
    public function render()
    {
        $user = $this->currentUser();

        // Base query with all filters
        $query = Transaction::with(['player.assignedStaff','game'])
            //->when($user->role !== 'admin', fn($q) => $q->whereHas('player', fn($p) => $p->where('staff_id', $user->id)))
            ->when($this->game_id, fn($q) => $q->where('game_id', $this->game_id))
            ->when($this->date_from, fn($q) => $q->whereDate('transaction_date', '>=', $this->date_from))
            ->when($this->date_to, fn($q) => $q->whereDate('transaction_date', '<=', $this->date_to))
            ->when($this->search, fn($q) => $q->whereHas('player', function($p) {
                $p->where('username','like','%'.$this->search.'%')
                    ->orWhere('player_name','like','%'.$this->search.'%');
            }))
            //->when($this->staff_id && $user->role === 'admin', fn($q) => $q->whereHas('player', fn($p) => $p->where('staff_id', $this->staff_id)))
            ->when($this->staff_id, fn($q) =>
            $q->whereHas('player', fn($p) => $p->where('staff_id', $this->staff_id))
            )
            ->when($this->wallet_agent, fn ($q) =>
    $q->where('agent', $this->wallet_agent)
    )
        ->when($this->wallet_name, fn ($q) =>
        $q->where('wallet_name', $this->wallet_name)
        )
        ->when($this->wallet_remarks, fn ($q) =>
        $q->where('wallet_remarks', $this->wallet_remarks)
        );

        // Get all filtered transactions
        $allTransactions = $query->orderBy('transaction_time', 'desc')->get();

        // Get distinct dates from filtered transactions, ignoring nulls
        $dates = $allTransactions->filter(fn($t) => $t->transaction_date !== null)
            ->pluck('transaction_date')
            ->unique()
            ->map(fn($d) => $d->format('Y-m-d'))
            ->sortDesc();

// Pagination for dates
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 5;
        $currentDates = $dates->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $paginatedDates = new LengthAwarePaginator(
            $currentDates,
            $dates->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

// Only fetch transactions that belong to current page of dates, ignoring null dates
        $transactionsByDate = $allTransactions->filter(fn($t) => $t->transaction_date !== null && in_array($t->transaction_date->format('Y-m-d'), $currentDates->toArray()))
            ->groupBy(fn($t) => $t->transaction_date->format('Y-m-d'));

        $allStaffs = Staff::all();
        $players = Player::all();

        return view('livewire.transactions-table', [
            'transactionsByDate' => $transactionsByDate,
            'transactionsDates' => $paginatedDates,
            'games' => Game::all(),
            'players' => $players,
            'currentUser' => $user,
            'allStaffs' => $allStaffs,
            'walletAgents' => $this->walletAgents,
            'walletNames' => $this->walletNames,
            'walletRemarksOptions' => $this->walletRemarksOptions,
        ]);
    }

}
