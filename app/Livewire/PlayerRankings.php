<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;



class PlayerRankings extends Component
{

    public $searchInput = '';
   public $search = '';
    protected function currentUser()
    {
        return Auth::guard('web')->user() ?? Auth::guard('staff')->user();
    }

    public $sortByDate = false;

    public function toggleSort()
    {
        $this->sortByDate = !$this->sortByDate;
    }
    public function applySearch()
    {
        $this->search = $this->searchInput;
    }

    public function render()
    {
        $user = $this->currentUser();

        // 1️⃣ Base query WITHOUT search (global ranking source)
        $baseQuery = Transaction::query()
            ->join('players', 'players.id', '=', 'transactions.player_id')
            ->selectRaw('
            players.player_name as player_name,
            SUM(transactions.cashin) as total_cashin,
            SUM(transactions.cashout) as total_cashout,
            MAX(transactions.transaction_date) as last_transaction_date
        ')
            ->groupBy('players.player_name')
            ->orderByDesc('total_cashin');

        // 2️⃣ Get full ranked list
        $fullRankings = $baseQuery->get()->values();

        // 3️⃣ Assign GLOBAL rank (before search)
        $ranked = $fullRankings->map(function ($row, $index) {
            $row->rank = $index + 1;
            return $row;
        });

        // 4️⃣ Apply search AFTER ranking (filter only)
        if ($this->search) {
            $ranked = $ranked->filter(fn ($r) =>
                stripos($r->player_name, $this->search) !== false
            )->values();
        }
        if ($this->sortByDate) {
            $ranked = $ranked->sortBy('last_transaction_date')->values();
        }
        // footer totals (based on visible rows)
        $totals = [
            'cashin' => $ranked->sum('total_cashin'),
            'cashout' => $ranked->sum('total_cashout'),
            'net' => $ranked->sum(fn ($r) => $r->total_cashin - $r->total_cashout),
        ];

        return view('livewire.player-rankings', [
            'rankings' => $ranked,
            'totals' => $totals,
        ]);
    }

}
