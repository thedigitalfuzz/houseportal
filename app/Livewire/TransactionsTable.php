<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Transaction;
use App\Models\Game;
use App\Models\WalletDetail;
use App\Models\Player;
use App\Models\PlayerAgent;
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
    public $agent_id = null;
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
    public $editWalletName;
    public $editWalletRemarks;
    public $editWalletNames = [];
    public $editWalletRemarksOptions = [];
    public $editDeposit;
    public $editNotes;
    public $editTransactionTime;
    public $editTransactionDate;
    public $editPlayerSearch = '';

    public $wallet_name = null;
    public $wallet_remarks = null;
    public $walletNames = [];
    public $walletRemarksOptions = [];

    public $confirmDeleteId = null;
    public $deleteModal = false;
    public $editCreditsUsed;

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
        $this->walletNames = WalletDetail::where('status', 'active')
            ->select('wallet_name')
            ->distinct()
            ->orderBy('wallet_name')
            ->pluck('wallet_name')
            ->toArray();
    }

    public function updatedWalletName()
    {
        $this->wallet_remarks = null;

        if (!$this->wallet_name) {
            $this->walletRemarksOptions = [];
            return;
        }

        $this->walletRemarksOptions = WalletDetail::where('wallet_name', $this->wallet_name)
            ->where('status', 'active')
            ->orderBy('wallet_remarks')
            ->pluck('wallet_remarks')
            ->toArray();
    }

    public function updatedEditWalletName()
    {
        $this->editWalletRemarks = null;

        if (!$this->editWalletName) {
            $this->editWalletRemarksOptions = [];
            return;
        }

        $this->editWalletRemarksOptions = WalletDetail::where('wallet_name', $this->editWalletName)
            ->where('status', 'active')
            ->orderBy('wallet_remarks')
            ->pluck('wallet_remarks')
            ->toArray();
    }

    public function editTransaction($id)
    {
        $transaction = Transaction::findOrFail($id);
        $user = $this->currentUser();

        $this->editingTransactionId = $id;
        $this->editPlayerId = $transaction->player_id;
        $this->editPlayerSearch = $transaction->player->username;
        $this->editGameId = $transaction->game_id;
        $this->editCashin = $transaction->cashin;
        $this->editCashout = $transaction->cashout;
        $this->editTransactionType = $transaction->cashin > 0 ? 'cashin' : 'cashout';
        $this->editAmount = $transaction->cashin > 0 ? $transaction->cashin : $transaction->cashout;
        $this->editBonusAdded = $transaction->bonus_added;
        $this->editCreditsUsed = $transaction->credits_used;
        $this->editCashTag = $transaction->cash_tag;
        $this->editDeposit = $transaction->deposit;
        $this->editNotes = $transaction->notes;
        $this->editWalletName = $transaction->wallet_name;
        $this->editWalletRemarks = $transaction->wallet_remarks;

        // Populate wallet names and remarks with only active wallets
        $this->editWalletNames = WalletDetail::where('status', 'active')
            ->select('wallet_name')
            ->distinct()
            ->pluck('wallet_name')
            ->toArray();

        $this->editWalletRemarksOptions = WalletDetail::where('wallet_name', $this->editWalletName)
            ->where('status', 'active')
            ->pluck('wallet_remarks')
            ->toArray();

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
            'editWalletName' => 'nullable|string|max:255',
            'editWalletRemarks' => 'nullable|string|max:255',
            'editDeposit' => 'nullable|numeric|min:0',
            'editNotes' => 'nullable|string',
            'editTransactionDate' => 'required|date',
            'editCreditsUsed' => 'nullable|numeric|min:0',
        ]);

        $cashin = $this->editTransactionType === 'cashin' ? $this->editAmount : 0;
        $cashout = $this->editTransactionType === 'cashout' ? $this->editAmount : 0;
        $total = $cashin - $cashout;
       // $bonusAdded = $this->editBonusAdded ?? 0;
        $creditsUsed = ($this->editCreditsUsed !== null && $this->editCreditsUsed !== '')
            ? floatval($this->editCreditsUsed)
            : floatval($this->editAmount);

        if ($this->editTransactionType === 'cashin') {
            $bonusAdded = $creditsUsed - floatval($this->editAmount);
        } else {
            $bonusAdded = 0;
        }
        $transaction = Transaction::findOrFail($this->editingTransactionId);
        $user = $this->currentUser();
        $userType = $user instanceof \App\Models\User ? 'App\Models\User' : 'App\Models\Staff';

        $walletDetail = WalletDetail::where('wallet_name', $this->editWalletName)
            ->where(function ($q) {
                if (empty($this->editWalletRemarks)) {
                    $q->whereNull('wallet_remarks');
                } else {
                    $q->where('wallet_remarks', $this->editWalletRemarks);
                }
            })
            ->orderByDesc('id')
            ->first();

        if (!$walletDetail) {
            $this->addError('editWalletName', 'Invalid wallet selection.');
            return;
        }

        $agent = $walletDetail->agent;

        $transaction->update([
            'player_id' => $this->editPlayerId,
            'game_id' => $this->editGameId,
            'cashin' => $cashin,
            'cashout' => $cashout,
            'transaction_type' => $this->editTransactionType,
            'total_transaction' => $cashin ? $cashin : -$cashout,
            'bonus_added' => floatval($bonusAdded),
            'cash_tag' => $this->editCashTag,
            'agent' => $agent,
            'wallet_name' => $this->editWalletName,
            'wallet_remarks' => $this->editWalletRemarks,
            'deposit' => $this->editDeposit ?? 0,
            'notes' => $this->editNotes,
            'transaction_date' => $this->editTransactionDate,
            'updated_by_id' => $user->id,
            'updated_by_type' => $userType,
            'credits_used' => $creditsUsed,
        ]);

        $this->editModal = false;
        $this->resetPage();
    }

    public function render()
    {
        $user = $this->currentUser();

        $query = Transaction::with(['player.assignedAgent','game'])
            ->when($this->game_id, fn($q) => $q->where('game_id', $this->game_id))
            ->when($this->date_from, fn($q) => $q->whereDate('transaction_date', '>=', $this->date_from))
            ->when($this->date_to, fn($q) => $q->whereDate('transaction_date', '<=', $this->date_to))
            ->when($this->search, fn($q) => $q->whereHas('player', function($p) {
                $p->where('username','like','%'.$this->search.'%')
                    ->orWhere('player_name','like','%'.$this->search.'%');
            }))
            ->when($this->wallet_name, fn ($q) => $q->where('wallet_name', $this->wallet_name))
            ->when($this->wallet_remarks, fn ($q) => $q->where('wallet_remarks', $this->wallet_remarks));

        $allTransactions = $query->orderBy('transaction_time', 'desc')->get();

        $dates = $allTransactions->filter(fn($t) => $t->transaction_date !== null)
            ->pluck('transaction_date')
            ->unique()
            ->map(fn($d) => $d->format('Y-m-d'))
            ->sortDesc();

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 3;
        $currentDates = $dates->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $paginatedDates = new LengthAwarePaginator(
            $currentDates,
            $dates->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        $transactionsByDate = $allTransactions->filter(fn($t) =>
            $t->transaction_date !== null &&
            in_array($t->transaction_date->format('Y-m-d'), $currentDates->toArray())
        )->groupBy(fn($t) => $t->transaction_date->format('Y-m-d'));

        $allAgents = PlayerAgent::all();
        $players = Player::all();

        return view('livewire.transactions-table', [
            'transactionsByDate' => $transactionsByDate,
            'transactionsDates' => $paginatedDates,
            'games' => Game::all(),
            'players' => $players,
            'currentUser' => $user,
            'allAgents' => $allAgents,
            'walletNames' => $this->walletNames,
            'walletRemarksOptions' => $this->walletRemarksOptions,
        ]);
    }
}
