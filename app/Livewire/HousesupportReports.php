<?php

namespace App\Livewire;

use Livewire\Component;
use Carbon\Carbon;
use App\Models\Transaction;
use App\Models\Player;
use App\Models\Game;
use Barryvdh\DomPDF\Facade\Pdf;

class HousesupportReports extends Component
{
    public $activeTab = 'daily';
    public $searchDate = null;

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    protected function baseQuery($start = null, $end = null)
    {
        return Transaction::query()
            ->when($start && $end, fn($q) => $q->whereBetween('transaction_date', [$start, $end]));
    }

    public function exportPdf()
    {
        if (!$this->searchDate) {
            $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => 'Please select a date first.']);
            return;
        }

        $date = Carbon::parse($this->searchDate);
        $pdfData = [];

        // ----------------------------
        // 1. Daily Report
        // ----------------------------
        $start = $date->copy()->startOfDay();
        $end = $date->copy()->endOfDay();
        $q = $this->baseQuery($start, $end);
        $pdfData[] = [
            'title' => '1. Daily Report (' . $date->format('d M Y') . ')',
            'summary' => $this->calculateSummary($q),
        ];

        // ----------------------------
        // 2. Weekly Report
        // ----------------------------
        $monthStart = $date->copy()->startOfMonth();
        $monthEnd = $date->copy()->endOfMonth();
        $daysInMonth = $monthEnd->day;

        $weekRanges = [
            [1, min(7, $daysInMonth)],
            [8, min(14, $daysInMonth)],
            [15, min(21, $daysInMonth)],
            [22, $daysInMonth],
        ];

        foreach ($weekRanges as $i => [$startDay, $endDay]) {
            $weekStart = Carbon::create($date->year, $date->month, $startDay)->startOfDay();
            $weekEnd = Carbon::create($date->year, $date->month, $endDay)->endOfDay();

            if ($date->between($weekStart, $weekEnd)) {
                $q = $this->baseQuery($weekStart, $weekEnd);
                $pdfData[] = [
                    'title' => '2. Weekly Report (' . $weekStart->format('d M') . ' - ' . $weekEnd->format('d M Y') . ')',
                    'summary' => $this->calculateSummary($q),
                ];
                break;
            }
        }

        // ----------------------------
        // 3. Monthly Report
        // ----------------------------
        $monthStart = $date->copy()->startOfMonth();
        $monthEnd = $date->copy()->endOfMonth();
        $q = $this->baseQuery($monthStart, $monthEnd);
        $pdfData[] = [
            'title' => '3. Monthly Report (' . $monthStart->format('F Y') . ')',
            'summary' => $this->calculateSummary($q),
        ];

        // ----------------------------
        // 4. All Time Report
        // ----------------------------
        $q = $this->baseQuery();
        $pdfData[] = [
            'title' => '4. All Time Report',
            'summary' => $this->calculateSummary($q),
        ];

        $pdf = Pdf::loadView('pdf.housesupport-reports', compact('pdfData'));

        return $pdf->download('housesupport-report-' . $date->format('Y-m-d') . '.pdf');
    }

    public function generatePdfData()
    {
        $date = Carbon::parse($this->searchDate);
        $pdfData = [];

        // 1. Daily
        $start = $date->copy()->startOfDay();
        $end = $date->copy()->endOfDay();
        $q = $this->baseQuery($start, $end);
        $pdfData[] = [
            'title' => '1. Daily Report (' . $date->format('d M Y') . ')',
            'summary' => $this->calculateSummary($q),
        ];

        // 2. Weekly
        $monthStart = $date->copy()->startOfMonth();
        $monthEnd = $date->copy()->endOfMonth();
        $daysInMonth = $monthEnd->day;

        $weekRanges = [
            [1, min(7, $daysInMonth)],
            [8, min(14, $daysInMonth)],
            [15, min(21, $daysInMonth)],
            [22, $daysInMonth],
        ];

        foreach ($weekRanges as $i => [$startDay, $endDay]) {
            $weekStart = Carbon::create($date->year, $date->month, $startDay)->startOfDay();
            $weekEnd = Carbon::create($date->year, $date->month, $endDay)->endOfDay();

            if ($date->between($weekStart, $weekEnd)) {
                $q = $this->baseQuery($weekStart, $weekEnd);
                $pdfData[] = [
                    'title' => '2. Weekly Report (' . $weekStart->format('d M') . ' - ' . $weekEnd->format('d M Y') . ')',
                    'summary' => $this->calculateSummary($q),
                ];
                break;
            }
        }

        // 3. Monthly
        $monthStart = $date->copy()->startOfMonth();
        $monthEnd = $date->copy()->endOfMonth();
        $q = $this->baseQuery($monthStart, $monthEnd);
        $pdfData[] = [
            'title' => '3. Monthly Report (' . $monthStart->format('F Y') . ')',
            'summary' => $this->calculateSummary($q),
        ];

        // 4. All Time
        $q = $this->baseQuery();
        $pdfData[] = [
            'title' => '4. All Time Report',
            'summary' => $this->calculateSummary($q),
        ];

        return $pdfData;
    }

    public function render()
    {
        $chunks = [];

        // If a search date is entered, parse it
        $searchDate = $this->searchDate ? Carbon::parse($this->searchDate) : null;

        if ($this->activeTab === 'daily') {
            $dates = $searchDate
                ? collect([['d' => $searchDate->toDateString()]])
                : Transaction::selectRaw('DATE(transaction_date) as d')
                    ->distinct()
                    ->orderByDesc('d')
                    ->get();

            foreach ($dates as $d) {
                $start = Carbon::parse($d['d'] ?? $d->d)->startOfDay();
                $end   = Carbon::parse($d['d'] ?? $d->d)->endOfDay();

                $q = $this->baseQuery($start, $end);

                if ($q->count() === 0) continue;

                $chunks[] = [
                    'label' => $start->format('d M Y'),
                    'summary' => $this->calculateSummary($q),
                ];
            }
        }

        if ($this->activeTab === 'weekly') {
            $months = $searchDate
                ? collect([['y' => $searchDate->year, 'm' => $searchDate->month]])
                : Transaction::selectRaw('YEAR(transaction_date) as y, MONTH(transaction_date) as m')
                    ->distinct()
                    ->orderByDesc('y')
                    ->orderByDesc('m')
                    ->get();

            foreach ($months as $m) {
                $year = $m['y'] ?? $m->y;
                $month = $m['m'] ?? $m->m;
                $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;

                $weeks = [
                    [1, min(7, $daysInMonth)],
                    [8, min(14, $daysInMonth)],
                    [15, min(21, $daysInMonth)],
                    [22, $daysInMonth],
                ];

                foreach ($weeks as $i => [$startDay, $endDay]) {
                    $start = Carbon::create($year, $month, $startDay)->startOfDay();
                    $end   = Carbon::create($year, $month, $endDay)->endOfDay();

                    // If searchDate is set, only include the week containing that date
                    if ($searchDate && !($searchDate->between($start, $end))) continue;

                    $q = $this->baseQuery($start, $end);

                    if ($q->count() === 0) continue;

                    $chunks[] = [
                        'label' => Carbon::create($year, $month, 1)->format('F') . " - Week " . ($i + 1),
                        'summary' => $this->calculateSummary($q),
                    ];
                }
            }
        }

        if ($this->activeTab === 'monthly') {
            $months = $searchDate
                ? collect([['y' => $searchDate->year, 'm' => $searchDate->month]])
                : Transaction::selectRaw('YEAR(transaction_date) y, MONTH(transaction_date) m')
                    ->distinct()
                    ->orderByDesc('y')
                    ->orderByDesc('m')
                    ->get();

            foreach ($months as $m) {
                $start = Carbon::create($m['y'] ?? $m->y, $m['m'] ?? $m->m, 1)->startOfMonth();
                $end   = Carbon::create($m['y'] ?? $m->y, $m['m'] ?? $m->m, 1)->endOfMonth();

                $q = $this->baseQuery($start, $end);

                if ($q->count() === 0) continue;

                $chunks[] = [
                    'label' => $start->format('F Y'),
                    'summary' => $this->calculateSummary($q),
                ];
            }
        }

        if ($this->activeTab === 'all') {
            $q = $this->baseQuery();
            $chunks[] = [
                'label' => 'All Time Reports',
                'summary' => $this->calculateSummary($q),
            ];
        }

        return view('livewire.housesupport-reports', compact('chunks', 'searchDate'));
    }

    protected function calculateSummary($q)
    {
        $falseTransactions = $q->clone()
            ->where('cashin', 0)
            ->where('cashout', 0);

        return [
            'totalTransactions' => $q->count(),
            'totalWalletTransactions' => $q->count(),

            'totalCashin' => $q->sum('cashin'),
            'totalCashout' => $q->sum('cashout'),

            'totalCashinTransactions' => $q->clone()->where('cashin', '>', 0)->count(),
            'totalCashoutTransactions' => $q->clone()->where('cashout', '>', 0)->count(),

            'netAmount' => $q->sum('cashin') - $q->sum('cashout'),

            'totalPlayers' => $q->clone()
                ->join('players', 'players.id', '=', 'transactions.player_id')
                ->distinct('players.player_name')
                ->count('players.player_name'),

            'falseTransactionCount' => $falseTransactions->count(),

            'falseTransactionPlayers' => $falseTransactions
                ->join('players', 'players.id', '=', 'transactions.player_id')
                ->select('players.player_name')
                ->distinct()
                ->pluck('player_name'),

            'topCashinPlayers' => $q->clone()
                ->join('players','players.id','=','transactions.player_id')
                ->selectRaw('players.player_name, SUM(transactions.cashin) as total')
                ->groupBy('players.player_name')
                ->orderByDesc('total')
                ->limit(5)
                ->get(),

            'topCashoutPlayers' => $q->clone()
                ->join('players','players.id','=','transactions.player_id')
                ->selectRaw('players.player_name, SUM(transactions.cashout) as total')
                ->groupBy('players.player_name')
                ->orderByDesc('total')
                ->limit(5)
                ->get(),

            'topCashinGame' => $q->clone()
                ->join('games','games.id','=','transactions.game_id')
                ->selectRaw('games.name, SUM(transactions.cashin) as amount')
                ->groupBy('games.name')
                ->orderByDesc('amount')
                ->first(),

            'topCashoutGame' => $q->clone()
                ->join('games','games.id','=','transactions.game_id')
                ->selectRaw('games.name, SUM(transactions.cashout) as amount')
                ->groupBy('games.name')
                ->orderByDesc('amount')
                ->first(),

            'topCashinWallet' => $q->clone()
                ->selectRaw('agent, wallet_name, wallet_remarks, SUM(cashin) as amount')
                ->groupBy('agent','wallet_name','wallet_remarks')
                ->orderByDesc('amount')
                ->first(),

            'topCashoutWallet' => $q->clone()
                ->selectRaw('agent, wallet_name, wallet_remarks, SUM(cashout) as amount')
                ->groupBy('agent','wallet_name','wallet_remarks')
                ->orderByDesc('amount')
                ->first(),

            'topTransactionWallet' => $q->clone()
                ->selectRaw('agent, wallet_name, wallet_remarks, COUNT(*) as transactions')
                ->groupBy('agent','wallet_name','wallet_remarks')
                ->orderByDesc('transactions')
                ->first(),
            'gamePointsPerformance' => $this->getGamePointsPerformance(
                $q->min('transaction_date') ?? null,
                $q->max('transaction_date') ?? null
            ),

            'walletSummary' => $q->clone()
                ->selectRaw('
                agent,
                wallet_name,
                wallet_remarks,
                COUNT(*) as transactions,
                SUM(cashin) as cashin,
                SUM(cashout) as cashout,
                (SUM(cashin) - SUM(cashout)) as net
            ')
                ->groupBy('agent','wallet_name','wallet_remarks')
                ->get(),

            'topStaffs' => $q->clone()
                ->join('players','players.id','=','transactions.player_id')
                ->join('staffs','staffs.id','=','players.staff_id')
                ->selectRaw('
                staffs.staff_name,
                COUNT(transactions.id) as transactions,
                SUM(transactions.cashin) as cashin,
                SUM(transactions.cashout) as cashout,
                (SUM(transactions.cashin) - SUM(transactions.cashout)) as net
            ')
                ->groupBy('staffs.staff_name')
                ->having('transactions', '>', 0)
                ->orderByDesc('transactions')
                ->get(),
            'topTransactionPlayer' => $q->clone()
                ->join('players','players.id','=','transactions.player_id')
                ->selectRaw('players.player_name, COUNT(transactions.id) as total_transactions')
                ->groupBy('players.player_name')
                ->orderByDesc('total_transactions')
                ->first(),
            'topCashinPlayer' => $q->clone()
                ->join('players','players.id','=','transactions.player_id')
                ->selectRaw('players.player_name, SUM(transactions.cashin) as total')
                ->groupBy('players.player_name')
                ->orderByDesc('total')
                ->first(),
        ];
    }
    protected function getGamePointsPerformance($start = null, $end = null)
    {
        $games = Game::orderBy('name')->get();

        $data = [];
        $totalStartingPoints = 0;
        $totalUsedPoints = 0;
        $totalClosingPoints = 0;
        $totalCashin = 0;
        $totalCashout = 0;
        $totalNet = 0;
        $topGamePointsUsed = null;
        $maxUsedPoints = 0;

        foreach ($games as $game) {
            $transactions = Transaction::where('game_id', $game->id);
            $gamePoints = \App\Models\GamePoint::where('game_id', $game->id);

            if ($start && $end) {
                $transactions->whereBetween('transaction_date', [$start, $end]);
                $gamePoints->whereBetween('date', [$start, $end]);
            }

            // Skip games with no data
            if ($transactions->count() === 0 && $gamePoints->count() === 0) continue;

            $gameStartingPoints = $gamePoints->sum('total_starting_points');
            $gameUsedPoints = $gamePoints->sum('used_points');
            $gameClosingPoints = $gamePoints->sum('points');

            $gameCashin = $transactions->sum('cashin');
            $gameCashout = $transactions->sum('cashout');
            $gameNet = $gameCashin - $gameCashout;

            $topPlayer = $transactions
                ->join('players','players.id','=','transactions.player_id')
                ->selectRaw('players.player_name, SUM(transactions.cashin) as total')
                ->groupBy('players.player_name')
                ->orderByDesc('total')
                ->value('players.player_name') ?? '-';

            $data[] = [
                'game_name' => $game->name,
                'total_starting_points' => $gameStartingPoints,
                'used_points' => $gameUsedPoints,
                'points' => $gameClosingPoints,
                'total_cashin' => $gameCashin,
                'total_cashout' => $gameCashout,
                'total_net' => $gameNet,
                'top_player' => $topPlayer,
            ];

            // Totals
            $totalStartingPoints += $gameStartingPoints;
            $totalUsedPoints += $gameUsedPoints;
            $totalClosingPoints += $gameClosingPoints;
            $totalCashin += $gameCashin;
            $totalCashout += $gameCashout;
            $totalNet += $gameNet;

            // Top game by used points
            if ($gameUsedPoints > $maxUsedPoints) {
                $maxUsedPoints = $gameUsedPoints;
                $topGamePointsUsed = $game->name;
            }
        }

        return [
            'data' => $data,
            'totals' => [
                'total_starting_points' => $totalStartingPoints,
                'used_points' => $totalUsedPoints,
                'total_closing_points' => $totalClosingPoints,
                'total_cashin' => $totalCashin,
                'total_cashout' => $totalCashout,
                'total_net' => $totalNet,
                'topGamePointsUsed' => $topGamePointsUsed,
            ],
        ];
    }


}
