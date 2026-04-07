<?php

namespace App\Livewire;

use Livewire\Component;
use Carbon\Carbon;
use App\Models\Staff;
use App\Models\Transaction;
use App\Models\Player;

class StaffPerformanceTable extends Component
{
    public $activeMainTab = 'daily';
    public $activeStaffTab;
    public $activeStaffTableTab = 'daily';

    public $staffs;

    public function mount()
    {
        $this->staffs = Staff::orderBy('staff_name')->get();
        $this->activeStaffTab = $this->staffs->first()?->id;
    }

    public function setMainTab($tab)
    {
        $this->activeMainTab = $tab;
    }

    public function setStaffTab($id)
    {
        $this->activeStaffTab = $id;
    }

    public function setStaffTableTab($tab)
    {
        $this->activeStaffTableTab = $tab;
    }

    // =========================================
    // ✅ STAFF SUMMARY (CORRECT FIX)
    // =========================================
    public function getStaffSummary($staffId, $type)
    {
        $txn = Transaction::where('created_by_id', $staffId);
        $players = Player::where('created_by_id', $staffId);

        if ($type === 'daily') {
            $txn->whereDate('transaction_date', now());
            $players->whereDate('created_at', now());
        }

        if ($type === 'monthly') {
            $txn->whereMonth('transaction_date', now()->month)
                ->whereYear('transaction_date', now()->year);

            $players->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year);
        }

        return [
            'transactions' => $txn->count(),
            'players' => $players->count(),
            'cashin' => $txn->sum('cashin'),
            'cashout' => $txn->sum('cashout'),
            'net' => $txn->sum('cashin') - $txn->sum('cashout'),
        ];
    }

    // =========================================
    // ✅ STAFF TABLE (FIXED LOGIC)
    // =========================================
    public function getStaffTable($staffId)
    {
        $rows = [];

        $query = Transaction::where('created_by_id', $staffId);

        if ($this->activeStaffTableTab === 'daily') {
            $dates = $query->selectRaw('DATE(transaction_date) d')->distinct()->get();

            foreach ($dates as $d) {
                $rows[] = $this->calculateRow(
                    $staffId,
                    Carbon::parse($d->d)->startOfDay(),
                    Carbon::parse($d->d)->endOfDay(),
                    $d->d
                );
            }
        }

        if ($this->activeStaffTableTab === 'monthly') {
            $months = $query->selectRaw('YEAR(transaction_date) y, MONTH(transaction_date) m')->distinct()->get();

            foreach ($months as $m) {
                $start = Carbon::create($m->y, $m->m)->startOfMonth();
                $end = Carbon::create($m->y, $m->m)->endOfMonth();

                $rows[] = $this->calculateRow($staffId, $start, $end, $start->format('F Y'));
            }
        }

        if ($this->activeStaffTableTab === 'yearly') {
            $years = $query->selectRaw('YEAR(transaction_date) y')->distinct()->get();

            foreach ($years as $y) {
                $start = Carbon::create($y->y)->startOfYear();
                $end = Carbon::create($y->y)->endOfYear();

                $rows[] = $this->calculateRow($staffId, $start, $end, $y->y);
            }
        }
        return collect($rows)->sortByDesc(function ($row) {
            return strtotime($row['label']);
        })->values()->toArray();
        //return $rows;
    }

    private function calculateRow($staffId, $start, $end, $label)
    {
        $base = Transaction::where('created_by_id', $staffId)
            ->whereBetween('transaction_date', [$start, $end]);

        $transactions = (clone $base)->count();

        $players = (clone $base)
            ->distinct('player_id')
            ->count('player_id');

        $cashin = (clone $base)->sum('cashin');   // ✅ FIXED (clone)
        $cashout = (clone $base)->sum('cashout'); // ✅ FIXED (clone)

        return [
            'label' => $label,
            'transactions' => $transactions,
            'players' => $players,
            'cashin' => $cashin,
            'cashout' => $cashout,
            'net' => $cashin - $cashout,
        ];
    }
    // =========================================
    // ✅ STAFF PLAYERS
    // =========================================
    public function getStaffPlayers($staffId)
    {
        return Player::where('created_by_id', $staffId)
            ->latest()
            ->get();
    }

    // =========================================
    // ✅ STAFF TRANSACTIONS
    // =========================================
    public function getStaffTransactions($staffId)
    {
        return Transaction::with(['player','game'])
            ->where('created_by_id', $staffId)
            ->latest('transaction_date')
            ->get();
    }
    public function getGlobalReportChunks()
    {
        $chunks = [];

        if ($this->activeMainTab === 'daily') {
            $dates = Transaction::selectRaw('DATE(transaction_date) d')
                ->distinct()->orderByDesc('d')->get();

            foreach ($dates as $d) {
                $start = \Carbon\Carbon::parse($d->d)->startOfDay();
                $end = \Carbon\Carbon::parse($d->d)->endOfDay();

                $chunks[] = $this->calculateGlobal($start, $end, $d->d);
            }
        }

        if ($this->activeMainTab === 'monthly') {
            $months = Transaction::selectRaw('YEAR(transaction_date) y, MONTH(transaction_date) m')
                ->distinct()->orderByDesc('y')->orderByDesc('m')->get();

            foreach ($months as $m) {
                $start = \Carbon\Carbon::create($m->y, $m->m)->startOfMonth();
                $end = \Carbon\Carbon::create($m->y, $m->m)->endOfMonth();

                $chunks[] = $this->calculateGlobal($start, $end, $start->format('F Y'));
            }
        }

        if ($this->activeMainTab === 'weekly') {
            $months = Transaction::selectRaw('YEAR(transaction_date) y, MONTH(transaction_date) m')
                ->distinct()->get();

            foreach ($months as $m) {
                $weeks = [
                    [1, 7],
                    [8, 14],
                    [15, 21],
                    [22, \Carbon\Carbon::create($m->y, $m->m)->daysInMonth],
                ];

                foreach ($weeks as $i => [$s, $e]) {
                    $start = \Carbon\Carbon::create($m->y, $m->m, $s)->startOfDay();
                    $end = \Carbon\Carbon::create($m->y, $m->m, $e)->endOfDay();

                    $chunks[] = $this->calculateGlobal($start, $end, "Week ".($i+1));
                }
            }
        }

        if ($this->activeMainTab === 'all') {
            $chunks[] = $this->calculateGlobal(null, null, 'All Time');
        }

        return $chunks;
    }
    private function calculateGlobal($start, $end, $label)
    {
        $base = Transaction::query();

        if ($start && $end) {
            $base->whereBetween('transaction_date', [$start, $end]);
        }

        $transactions = (clone $base)->count();

        $players = (clone $base)
            ->distinct('player_id')
            ->count('player_id');

        $cashin = (clone $base)->sum('cashin');

        $cashout = (clone $base)->sum('cashout');

        return [
            'label' => $label,
            'transactions' => $transactions,
            'players' => $players,
            'cashin' => $cashin,
            'cashout' => $cashout,
            'net' => $cashin - $cashout,
        ];
    }
    public function getStaffPerformanceRows()
    {
        $start = null;
        $end = null;

        if ($this->activeMainTab === 'daily') {
            $start = now()->startOfDay();
            $end = now()->endOfDay();
        }

        if ($this->activeMainTab === 'monthly') {
            $start = now()->startOfMonth();
            $end = now()->endOfMonth();
        }

        if ($this->activeMainTab === 'weekly') {
            $start = now()->startOfWeek();
            $end = now()->endOfWeek();
        }

        $query = \App\Models\Staff::query()
            ->leftJoin('transactions', function ($join) use ($start, $end) {
                $join->on('transactions.created_by_id', '=', 'staffs.id');

                if ($start && $end) {
                    $join->whereBetween('transactions.transaction_date', [$start, $end]);
                }
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

                $playersQuery = Player::where('created_by_id', $staff->id)
                    ->where('created_by_type', \App\Models\Staff::class);

                if ($start && $end) {
                    if ($start->isSameDay($end)) {
                        $playersQuery->whereDate('created_at', $start);
                    } else {
                        $playersQuery->whereBetween('created_at', [$start, $end]);
                    }
                }

                $staff->players_added = $playersQuery->count();

                return $staff;
            })
            ->filter(fn ($s) => $s->players_added > 0 || $s->transactions > 0)
            ->sortByDesc('cashin')
            ->values();

        return $query;
    }
    public function render()
    {
        return view('livewire.staff-performance-table');
    }
}
