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
    public $isSupportAgent = false;
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
    public $topGamePerformance=[];

    public $allTimeCashinLabels = [];
    public $allTimeCashinData = [];

    public $allTimeNetLabels = [];
    public $allTimeNetData = [];
    public $dailyStaffSummary = [];
    public $dailySummaryData = [];
    public $allTimeSummaryData = [];
    public $myDailySummary = [];

public $gameStats;
    public $last5DaysDetailed = [];

    public function mount()
    {
        $user = Auth::guard('web')->user() ?? Auth::guard('staff')->user();

        $this->isSupportAgent = $user && $user->role === 'support_agent';
        $this->isWalletManager = $user && $user->role === 'wallet_manager';

        // Only show for admin and wallet manager
        if ($user) {
            $start = now()->startOfDay();
            $end = now()->endOfDay();

            // Use the same logic as StaffPerformanceTable
            $this->dailyStaffSummary = \App\Models\Staff::query()
                ->leftJoin('transactions', function ($join) use ($start, $end) {
                    $join->on('transactions.created_by_id', '=', 'staffs.id')
                        ->whereBetween('transactions.transaction_date', [$start, $end]);
                })
                ->selectRaw('
            staffs.id,
            staffs.staff_name,
            COUNT(transactions.id) as transactions,
            COALESCE(SUM(transactions.cashin),0) as cashin,
            COALESCE(SUM(transactions.cashout),0) as cashout,
            (COALESCE(SUM(transactions.cashin),0) - COALESCE(SUM(transactions.cashout),0)) as net
        ')
                ->groupBy('staffs.id', 'staffs.staff_name')
                ->get()
                ->map(function ($staff) use ($start, $end) {

                    $playersQuery = \App\Models\Player::where('created_by_id', $staff->id)
                        ->where('created_by_type', \App\Models\Staff::class)
                        ->whereDate('created_at', $start); // daily only

                    $staff->players_added = $playersQuery->count();

                    return $staff;
                })
                ->filter(fn ($s) => $s->players_added > 0 || $s->transactions > 0)
                ->sortByDesc('cashin')
                ->values();
            // ================= DASHBOARD SUMMARY (REPORT STYLE) =================

// DAILY
            $todayStart = now()->startOfDay();
            $todayEnd = now()->endOfDay();

            $dailyQ = \App\Models\Transaction::whereBetween('transaction_date', [$todayStart, $todayEnd]);

            $this->dailySummaryData = [
                'transactions' => $dailyQ->count(),
                'cashin' => $dailyQ->sum('cashin'),
                'cashout' => $dailyQ->sum('cashout'),
                'net' => $dailyQ->sum('cashin') - $dailyQ->sum('cashout'),
            ];

// ALL TIME (ADMIN ONLY)
            if ($user->role === 'admin') {
                $allQ = \App\Models\Transaction::query();

                $this->allTimeSummaryData = [
                    'transactions' => $allQ->count(),
                    'cashin' => $allQ->sum('cashin'),
                    'cashout' => $allQ->sum('cashout'),
                    'net' => $allQ->sum('cashin') - $allQ->sum('cashout'),
                ];
            }
        }

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
        if ($this->isSupportAgent || $this->isWalletManager) {
            $userId = $user->id;
            $now = Carbon::now();

            $todayStart = now()->startOfDay();
            $todayEnd = now()->endOfDay();

            $staffTodayTxn = Transaction::where('created_by_id', $userId)
                ->whereBetween('transaction_date', [$todayStart, $todayEnd]);

            $this->myDailySummary = (object) [
                'transactions' => $staffTodayTxn->count(),
                'cashin' => $staffTodayTxn->sum('cashin'),
                'cashout' => $staffTodayTxn->sum('cashout'),
                'net' => $staffTodayTxn->sum('cashin') - $staffTodayTxn->sum('cashout'),
            ];

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
        // TOP GAME PERFORMANCE (Top 5 by transactions)
        $start = now()->startOfMonth();
        $end = now()->endOfMonth();

        $this->topGamePerformance = \App\Models\Transaction::join('games', 'transactions.game_id', '=', 'games.id')
            ->whereBetween('transactions.transaction_date', [$start, $end]) // ✅ ADD THIS
            ->selectRaw('
        games.id as game_id,
        games.name as game_name,
        COUNT(transactions.id) as total_transactions,
        SUM(transactions.cashin) as total_cashin,
        SUM(transactions.cashout) as total_cashout
    ')
            ->groupBy('games.id', 'games.name')
            ->orderByDesc('total_transactions')
            ->limit(5)
            ->get()
            ->map(function ($row) use ($start, $end) {
                $row->used_points = \App\Models\GamePoint::where('game_id', $row->game_id)
                    ->whereBetween('date', [$start, $end]) // ✅ already correct
                    ->sum('used_points') ?? 0;

                return $row;
            });

        if ($this->isSupportAgent || $this->isWalletManager) {
            $userId = $user->id;

            $allTime = Transaction::selectRaw('
            DATE(transaction_date) as day,
            SUM(cashin) as total_cashin,
            SUM(cashout) as total_cashout
        ')
                ->where('created_by_id', $userId)
                ->groupBy('day')
                ->orderBy('day')
                ->get();

            $this->allTimeCashinLabels = $allTime->pluck('day')
                ->map(fn ($d) => \Carbon\Carbon::parse($d)->format('d M Y'))
                ->toArray();

            $this->allTimeCashinData = $allTime->pluck('total_cashin')->toArray();

            $this->allTimeNetLabels = $this->allTimeCashinLabels;

            $this->allTimeNetData = $allTime->map(function ($row) {
                return $row->total_cashin - $row->total_cashout;
            })->toArray();
        }
        else {
            $allTime = Transaction::selectRaw('
            DATE(transaction_date) as day,
            SUM(cashin) as total_cashin,
            SUM(cashout) as total_cashout
        ')
                ->groupBy('day')
                ->orderBy('day')
                ->get();

            $this->allTimeCashinLabels = $allTime->pluck('day')
                ->map(fn ($d) => \Carbon\Carbon::parse($d)->format('d M Y'))
                ->toArray();

            $this->allTimeCashinData = $allTime->pluck('total_cashin')->toArray();

            $this->allTimeNetLabels = $this->allTimeCashinLabels;

            $this->allTimeNetData = $allTime->map(function ($row) {
                return $row->total_cashin - $row->total_cashout;
            })->toArray();
        }
    }
    private function getSummary($start = null, $end = null)
    {
        $q = \App\Models\Transaction::query();

        if ($start && $end) {
            $q->whereBetween('transaction_date', [$start, $end]);
        }

        return [
            'totalTransactions' => $q->count(),
            'totalCashin' => $q->sum('cashin'),
            'totalCashout' => $q->sum('cashout'),
            'net' => $q->sum('cashin') - $q->sum('cashout'),
        ];
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
