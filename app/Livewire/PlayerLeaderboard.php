<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PlayerLeaderboard extends Component
{
    public $month;
    public $year;
    public $searchMode = false;

    protected function currentUser()
    {
        return Auth::guard('web')->user() ?? Auth::guard('staff')->user();
    }

    public function mount()
    {
        $this->month = now()->month;
        $this->year  = now()->year;
    }

    public function search()
    {
        $this->searchMode = true;
    }

    protected function leaderboardQuery($month, $year)
    {
        $user = $this->currentUser();

        return Transaction::query()
            ->join('players', 'players.id', '=', 'transactions.player_id')
            ->selectRaw('
                players.player_name as player_name,
                SUM(transactions.cashin) as total_cashin,
                SUM(transactions.cashout) as total_cashout
            ')
            ->whereMonth('transactions.transaction_date', $month)
            ->whereYear('transactions.transaction_date', $year)
            ->groupBy('players.player_name')
            ->orderByDesc('total_cashin')
            ->limit(10)
            ->get();
    }

    public function render()
    {
        $leaderboards = [];

        if ($this->searchMode) {
            // Single month leaderboard
            $leaderboards[] = [
                'month' => $this->month,
                'year'  => $this->year,
                'rows'  => $this->leaderboardQuery($this->month, $this->year),
            ];
        } else {
            // Multi-month leaderboard (latest first)
            $months = Transaction::selectRaw('
                    YEAR(transaction_date) as year,
                    MONTH(transaction_date) as month
                ')
                ->distinct()
                ->orderByDesc('year')
                ->orderByDesc('month')
                ->limit(6)
                ->get();

            foreach ($months as $m) {
                $leaderboards[] = [
                    'month' => $m->month,
                    'year'  => $m->year,
                    'rows'  => $this->leaderboardQuery($m->month, $m->year),
                ];
            }
        }

        return view('livewire.player-leaderboard', [
            'leaderboards' => $leaderboards,
        ]);
    }
}
