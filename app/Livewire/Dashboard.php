<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Wallet;
use App\Models\GameCredit;
use App\Models\Transaction;
use App\Models\WalletDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Player;


class Dashboard extends Component
{
    public $recentWallets = [];
    public $recentGameCredits = [];
    public $recentTransactions = [];
    public $recentWalletDetails = [];
    public $topPlayersCurrentMonth = [];


    public function mount()
    {
        // Fetch 5 most recent Wallet records
        $this->recentWallets = Wallet::with(['createdBy'])
            ->orderBy('date', 'desc')
            ->take(5)
            ->get();

        // Fetch 5 most recent GameCredit records
        $this->recentGameCredits = GameCredit::with(['game', 'createdBy'])
            ->orderBy('date', 'desc')
            ->take(5)
            ->get();

        // Fetch 5 most recent Transactions
        $this->recentTransactions = Transaction::with(['player', 'game'])
            ->orderBy('transaction_time', 'desc')
            ->take(5)
            ->get();

        $this->recentWalletDetails = WalletDetail::orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $now = Carbon::now();

        $user = Auth::guard('web')->user() ?? Auth::guard('staff')->user();

        $this->topPlayersCurrentMonth = Transaction::query()
            ->join('players', 'players.id', '=', 'transactions.player_id')
            ->selectRaw(
        'players.player_name as player_name,
        SUM(transactions.cashin) as total_cashin,
        SUM(transactions.cashout) as total_cashout'
            )
            ->whereMonth('transactions.transaction_date', $now->month)
            ->whereYear('transactions.transaction_date', $now->year)
            ->when($user && $user->role !== 'admin', function ($q) use ($user) {
                $q->where('players.staff_id', $user->id);
            })
            ->groupBy('players.player_name')
            ->orderByDesc('total_cashin')
            ->limit(5)
            ->get();

    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
