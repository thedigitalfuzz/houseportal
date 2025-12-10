<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Transaction;
use App\Models\Player;
use App\Models\Game;

class TransactionsCreate extends Component
{
    public $player_id;
    public $game_id;
    public $cashin;
    public $cashout;
    public $bonus_added;
    public $total_transaction;
    public $cash_tag;
    public $wallet_name;
    public $wallet_remarks;
    public $notes;
    public $showModal = false;

    protected $rules = [
        'player_id' => 'required|exists:players,id',
        'game_id' => 'required|exists:games,id',
        'cashin' => 'nullable|numeric|min:0',
        'cashout' => 'nullable|numeric|min:0',
        'bonus_added' => 'nullable|numeric|min:0',
        'notes' => 'nullable|string',
        'cash_tag' => 'nullable|string|max:255',
        'wallet_name' => 'nullable|string|max:255',
        'wallet_remarks' => 'nullable|string',
    ];

    #[\Livewire\Attributes\On('open-create-transaction')]
    public function openModal()
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->reset([
            'player_id',
            'game_id',
            'cashin',
            'cashout',
            'bonus_added',
            'cash_tag',
            'wallet_name',
            'wallet_remarks',
            'notes',
        ]);

        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function save()
    {
        $this->validate();

        $total = ($this->cashin ?? 0) - ($this->cashout ?? 0);

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
            'cashin' => $this->cashin ?? 0,
            'cashout' => $this->cashout ?? 0,
            'total_transaction' => $total,
            'bonus_added' => $this->bonus_added ?? 0,
            'cash_tag' => $this->cash_tag,
            'wallet_name' => $this->wallet_name,
            'wallet_remarks' => $this->wallet_remarks,
            'notes' => $this->notes,
            'transaction_time' => now(),
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

        return view('livewire.transactions-create', [
            'players' => $players,
            'games' => Game::all(),
        ]);
    }
}
