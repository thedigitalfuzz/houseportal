<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Transaction;
use App\Models\Player;
use App\Models\Game;
use App\Models\Wallet;
use App\Models\WalletDetail;

class TransactionsCreate extends Component
{
    public $player_id;
    public $game_id;
    public $cashin;
    public $cashout;
    public $bonus_added;
    public $transaction_type; // cashin | cashout
    public $amount;

    public $agent;
    public $wallet_name;
    public $wallet_remarks;

    public $agents = [];
    public $walletNames = [];
    public $walletRemarks = [];
    public $total_transaction;
    public $cash_tag;

    public $notes;
    public $transaction_date; // new

    public $walletOptions = [];



    public $showModal = false;

    protected $rules = [
        'player_id' => 'required|exists:players,id',
        'game_id' => 'required|exists:games,id',
        'transaction_type' => 'required|in:cashin,cashout',
        'amount' => 'required|numeric|min:0',
        'bonus_added' => 'nullable|numeric|min:0',
        'notes' => 'nullable|string',
        'cash_tag' => 'nullable|string|max:255',
        'agent' => 'required|string|max:255',
        'wallet_name' => 'nullable|string|max:255',
        'wallet_remarks' => 'nullable|string',
        'transaction_date' => 'required|date',
    ];


    #[\Livewire\Attributes\On('open-create-transaction')]


    public function openModal()
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->reset([
            'player_id',
            'game_id',
            'transaction_type',
            'amount',
            'bonus_added',
            'cash_tag',
            'agent',
            'wallet_name',
            'wallet_remarks',
            'walletNames',
            'walletRemarks',
            'notes',
            'transaction_date',
        ]);

        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function mount()
    {
        $this->agents = WalletDetail::select('agent')
            ->distinct()
            ->orderBy('agent')
            ->pluck('agent')
            ->toArray();
    }

    public function updatedAgent()
    {
        $this->wallet_name = null;
        $this->wallet_remarks = null;

        if (!$this->agent) {
            $this->walletNames = [];
            $this->walletRemarks = [];
            return;
        }

        $this->walletNames = WalletDetail::where('agent', $this->agent)
            ->select('wallet_name')
            ->distinct()
            ->orderBy('wallet_name')
            ->pluck('wallet_name')
            ->toArray();

        $this->walletRemarks = [];
    }

    public function updatedWalletName()
    {
        $this->wallet_remarks = null;

        if (!$this->agent || !$this->wallet_name) {
            $this->walletRemarks = [];
            return;
        }

        $this->walletRemarks = WalletDetail::where('agent', $this->agent)
            ->where('wallet_name', $this->wallet_name)
            ->orderBy('wallet_remarks')
            ->pluck('wallet_remarks')
            ->toArray();
    }



    public function save()
    {
        $this->validate();

        $cashin = 0;
        $cashout = 0;

        if ($this->transaction_type === 'cashin') {
            $cashin = $this->amount;
        } else {
            $cashout = $this->amount;
        }

        $total = $cashin - $cashout;

        $bonusAdded = $this->bonus_added !== null && $this->bonus_added !== ''
            ? $this->bonus_added
            : 0;

        // Get current user (admin or staff)
        $user = auth()->user() ?? auth()->guard('staff')->user();

        // Staff can only create transaction for their assigned players
        if ($user->role !== 'admin') {
            $player = Player::findOrFail($this->player_id);
            if ($player->staff_id !== $user->id) {
                $this->dispatchBrowserEvent('notify', ['type' => 'error', 'message' => 'You cannot create a transaction for this player.']);
                return;
            }
        }

        Transaction::create([
            'player_id' => $this->player_id,
            'game_id' => $this->game_id,
            'cashin' => $this->transaction_type === 'cashin' ? floatval($this->amount) : 0,
            'cashout' => $this->transaction_type === 'cashout' ? floatval($this->amount) : 0,
            'total_transaction' => $this->transaction_type === 'cashin' ? floatval($this->amount) : -floatval($this->amount),
            'bonus_added' => floatval($bonusAdded),
            'cash_tag' => $this->cash_tag,
            'agent' => $this->agent,
            'wallet_name' => $this->wallet_name,
            'wallet_remarks' => $this->wallet_remarks,
            'notes' => $this->notes,
            'transaction_time' => now(),           // keep auto-filled
            'transaction_date' => $this->transaction_date, // user-specified date used for chunking
            'staff_user_id' => $user->role === 'admin' ? $user->id : null,
        ]);

        $this->dispatch('transactionCreated');
        $this->showModal = false;
    }

    public function render()
    {
        // Get current user
        $user = auth()->user() ?? auth()->guard('staff')->user();

        // Admin sees all players, staff sees only their assigned players
        $players = $user->role === 'admin'
            ? Player::all()
            : Player::where('staff_id', $user->id)->get();

        // Fetch wallets from Wallets table
        $this->walletOptions = Wallet::pluck('wallet_name')->unique();

        return view('livewire.transactions-create', [
            'players' => $players,
            'games' => Game::all(),
        ]);
    }
}
