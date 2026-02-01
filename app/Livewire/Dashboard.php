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

    public $monthlyStats = [];
    public $cashinLast10Days = [];
    public $dailyCashinLabels = [];
    public $dailyCashinData = [];
    public $monthLabel;

    public $totalTransactions = 0;
    public $totalCashin = 0;
    public $totalCashout = 0;
    public $totalNet = 0;
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

        $monthQuery = Transaction::query()
            ->whereMonth('transaction_date', $now->month)
            ->whereYear('transaction_date', $now->year);
        $this->monthLabel = $now->format('F Y');

        $monthlyTransactions = Transaction::whereMonth('transaction_date', $now->month)
            ->whereYear('transaction_date', $now->year);

        $this->totalTransactions = $monthlyTransactions->count();
        $this->totalCashin = $monthlyTransactions->sum('cashin');
        $this->totalCashout = $monthlyTransactions->sum('cashout');
        $this->totalNet = $this->totalCashin - $this->totalCashout;



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
          //  ->when($user && $user->role !== 'admin', function ($q) use ($user) {
            //    $q->where('players.staff_id', $user->id);
            //})
            ->groupBy('players.player_name')
            ->orderByDesc('total_cashin')
            ->limit(5)
            ->get();

        // Last 10 days cashin bar chart
        $last10Days = Transaction::selectRaw('
        DATE(transaction_date) as day,
        SUM(cashin) as total
    ')
            ->where('transaction_date', '>=', now()->subDays(9)->startOfDay())
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $this->dailyCashinLabels = $last10Days
            ->pluck('day')
            ->map(fn ($d) => Carbon::parse($d)->format('d M'))
            ->toArray();

        $this->dailyCashinData = $last10Days
            ->pluck('total')
            ->toArray();

    }

    public function render()
    {
        return view('livewire.dashboard');



    }
}
