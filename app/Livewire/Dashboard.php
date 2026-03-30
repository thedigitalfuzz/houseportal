<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Wallet;
use App\Models\GameCredit;
use App\Models\Transaction;
use App\Models\WalletDetail;
use App\Models\Player;
use App\Models\Game;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Component
{
    public $recentWallets = [];
    public $recentGameCredits = [];
    public $recentTransactions = [];
    public $recentWalletDetails = [];
    public $topPlayersCurrentMonth = [];

    public $dailyCashinLabels = [];
    public $dailyCashinData = [];
    public $monthLabel;

    public $totalTransactions = 0;
    public $totalCashin = 0;
    public $totalCashout = 0;
    public $totalNet = 0;

    // STAFF
    public $isEntryStaff = false;
    public $isWalletManager = false;
    public $staffPlayersCount = 0;
    public $staffTransactionsCount = 0;
    public $staffTotalCashin = 0;
    public $staffTotalCashout = 0;

    public $topCashinTransactions = [];
    public $topPlayersAllTime = [];

    public $last5DaysTxnLabels = [];
    public $last5DaysTxnData = [];
    public $gameStatsTable = [];
    public $gamePieLabels = [];
    public $gamePieData = [];
    public $highestCashinTxn;
    public $highestCashoutTxn;
public $gameStats;
    public $last5DaysDetailed = [];

    public function mount()
    {
        $user = Auth::guard('web')->user() ?? Auth::guard('staff')->user();

        $this->isEntryStaff = $user && $user->role === 'entry_staff';
        $this->isWalletManager = $user && $user->role === 'wallet_manager';


        // ORIGINAL DATA (UNCHANGED)
        $this->recentWallets = Wallet::orderBy('date', 'desc')->take(5)->get();

        $this->recentGameCredits = GameCredit::with('game')
            ->orderBy('date', 'desc')
            ->take(5)
            ->get();

        $this->recentTransactions = Transaction::with(['player', 'game'])
            ->orderBy('transaction_time', 'desc')
            ->take(5)
            ->get();

        $now = Carbon::now();
        $this->monthLabel = $now->format('F Y');

        $monthlyTransactions = Transaction::whereMonth('transaction_date', $now->month)
            ->whereYear('transaction_date', $now->year);

        $this->totalTransactions = $monthlyTransactions->count();
        $this->totalCashin = $monthlyTransactions->sum('cashin');
        $this->totalCashout = $monthlyTransactions->sum('cashout');
        $this->totalNet = $this->totalCashin - $this->totalCashout;

        $this->topPlayersCurrentMonth = Transaction::join('players', 'players.id', '=', 'transactions.player_id')
            ->selectRaw('players.player_name, SUM(transactions.cashin) as total_cashin, SUM(transactions.cashout) as total_cashout')
            ->whereMonth('transactions.transaction_date', $now->month)
            ->whereYear('transactions.transaction_date', $now->year)
            ->groupBy('players.player_name')
            ->orderByDesc('total_cashin')
            ->limit(5)
            ->get();

        // ORIGINAL CHART
        $last10Days = Transaction::selectRaw('DATE(transaction_date) as day, SUM(cashin) as total')
            ->where('transaction_date', '>=', now()->subDays(9)->startOfDay())
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $this->dailyCashinLabels = $last10Days->pluck('day')
            ->map(fn ($d) => Carbon::parse($d)->format('d M'))
            ->toArray();

        $this->dailyCashinData = $last10Days->pluck('total')->toArray();

        // STAFF DATA
        if ($this->isEntryStaff || $this->isWalletManager) {
            $userId = $user->id;
            $now = Carbon::now();

            // Staff Transactions for current month
            $staffTxn = Transaction::where('created_by_id', $userId)
                ->whereMonth('transaction_date', $now->month)
                ->whereYear('transaction_date', $now->year);

            // Staff Players Added (this can be filtered by month if needed)
            $this->staffPlayersCount = Player::where('created_by_id', $userId)
                ->whereMonth('created_at', $now->month)
                ->whereYear('created_at', $now->year)
                ->count();

            $this->staffTransactionsCount = $staffTxn->count();
            $this->staffTotalCashin = $staffTxn->sum('cashin');
            $this->staffTotalCashout = $staffTxn->sum('cashout');
            $this->topCashinTransactions = Transaction::with('player')
                ->where('created_by_id', $user->id)
                ->orderByDesc('cashin')
                ->take(5)
                ->get();

            //$this->topPlayersAllTime = Transaction::join('players', 'players.id', '=', 'transactions.player_id')
              //  ->selectRaw('players.player_name, SUM(transactions.cashin) as total_cashin')
                //->where('players.created_by_id', $user->id)
                //->groupBy('players.player_name')
                //->orderByDesc('total_cashin')
                //->take(5)
                //->get();

            $this->topPlayersAllTime = Transaction::join('players','players.id','=','transactions.player_id')
                ->selectRaw('
        players.player_name,
        SUM(transactions.cashin) as total_cashin,
        MAX(transactions.transaction_date) as last_transaction_date
    ')
                ->where('players.created_by_id',$user->id)
                ->groupBy('players.player_name')
                ->orderByDesc('total_cashin')
                ->limit(5)
                ->get();

            $last5Days = Transaction::selectRaw('
        DATE(transaction_date) as day,
        SUM(cashin) as total
    ')
                ->where('created_by_id', $user->id)
                ->where('transaction_date', '>=', now()->subDays(4)->startOfDay())
                ->groupBy('day')
                ->orderBy('day')
                ->get();

            $this->last5DaysTxnLabels = $last5Days
                ->pluck('day')
                ->map(fn ($d) => \Carbon\Carbon::parse($d)->format('d M'))
                ->toArray();

            $this->last5DaysTxnData = $last5Days
                ->pluck('total')
                ->toArray();

            // Highest Cash In
            $this->highestCashinTxn = Transaction::with(['player','game'])
                ->where('created_by_id', $userId)
                ->whereMonth('transaction_date', $now->month)
                ->whereYear('transaction_date', $now->year)
                ->orderByDesc('cashin')
                ->first();

            // Highest Cash Out
            $this->highestCashoutTxn = Transaction::with(['player','game'])
                ->where('created_by_id', $userId)
                ->whereMonth('transaction_date', $now->month)
                ->whereYear('transaction_date', $now->year)
                ->orderByDesc('cashout')
                ->first();
        }
        $gameStats = Transaction::join('games','games.id','=','transactions.game_id')
            ->selectRaw('games.name as game_name, SUM(transactions.cashin) as total')
            ->groupBy('games.name')
            ->orderByDesc('total')
            ->get();

// For Pie Chart
        $this->gamePieLabels = $gameStats->pluck('game_name')->toArray();
        $this->gamePieData = $gameStats->pluck('total')->toArray();

// For Table
        $this->gameStatsTable = $gameStats;
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
