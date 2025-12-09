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
    public $deposit;
    public $notes;
    public $showModal = false;

    protected $rules = [
        'player_id' => 'required|exists:players,id',
        'game_id' => 'required|exists:games,id',
        'cashin' => 'nullable|numeric|min:0',
        'cashout' => 'nullable|numeric|min:0',
        'bonus_added' => 'nullable|numeric|min:0',
        'deposit' => 'nullable|numeric|min:0',
        'notes' => 'nullable|string',
    ];

    protected $listeners = ['open-create-transaction' => 'openModal'];

    public function canEdit(): bool
    {
        return auth()->user()->role === 'admin';
    }

    public function canDelete(): bool
    {
        return auth()->user()->role === 'admin';
    }

    public function openModal()
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->reset(['player_id','game_id','cashin','cashout','bonus_added','deposit','notes']);
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function save()
    {
        $this->validate();

        Transaction::create([
            'player_id' => $this->player_id,
            'game_id' => $this->game_id,
            'cashin' => $this->cashin ?? 0,
            'cashout' => $this->cashout ?? 0,
            'bonus_added' => $this->bonus_added ?? 0,
            'deposit' => $this->deposit ?? 0,
            'notes' => $this->notes,
            'transaction_time' => now(),
        ]);


        $this->showModal = false;
    }

    public function render()
    {
        return view('livewire.transactions-create', [
            'players' => Player::all(),
            'games' => Game::all(),
        ]);
    }
}
