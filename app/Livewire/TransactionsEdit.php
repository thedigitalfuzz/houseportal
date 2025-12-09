<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Transaction;
use App\Models\Player;
use App\Models\Game;

class TransactionsEdit extends Component
{
    public $transactionId;
    public $player_id;
    public $game_id;
    public $cashin;
    public $cashout;
    public $transaction_time;

    protected $listeners = ['load-transaction' => 'loadTransaction'];

    public function loadTransaction($id)
    {
        $this->transactionId = $id;
        $t = Transaction::findOrFail($id);
        $this->player_id = $t->player_id;
        $this->game_id = $t->game_id;
        $this->cashin = $t->cashin;
        $this->cashout = $t->cashout;
        $this->transaction_time = $t->transaction_time;
    }

    public function update()
    {
        $t = Transaction::findOrFail($this->transactionId);
        $t->update([
            'player_id' => $this->player_id,
            'game_id' => $this->game_id,
            'cashin' => $this->cashin,
            'cashout' => $this->cashout,
            'transaction_time' => $this->transaction_time,
        ]);

        $this->dispatch('transactionUpdated');
        $this->dispatch('close-edit-modal');
    }

    public function render()
    {
        return view('livewire.transactions-edit', [
            'players' => Player::all(),
            'games' => Game::all(),
        ]);
    }
}
