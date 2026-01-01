<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class PlayerRankings extends Component
{
    protected function currentUser()
    {
        return Auth::guard('web')->user() ?? Auth::guard('staff')->user();
    }

    public function render()
    {
        $user = $this->currentUser();

        $rankings = Transaction::query()
            ->join('players', 'players.id', '=', 'transactions.player_id')
            ->selectRaw('
                players.player_name as player_name,
                SUM(transactions.cashin) as total_cashin,
                SUM(transactions.cashout) as total_cashout
            ')
            ->when($user->role !== 'admin', function ($q) use ($user) {
                $q->where('players.staff_id', $user->id);
            })
            ->groupBy('players.player_name')
            ->orderByDesc('total_cashin')
            ->get()
            ->values(); // reindex for ranking

        return view('livewire.player-rankings', [
            'rankings' => $rankings
        ]);
    }
}
