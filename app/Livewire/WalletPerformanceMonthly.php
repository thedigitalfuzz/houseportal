<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Transaction;
use App\Models\WalletDetail;
use Illuminate\Pagination\LengthAwarePaginator;

class WalletPerformanceMonthly extends Component
{
    use WithPagination;

    public $year;
    public $month;

    public $wallet_agent;
    public $wallet_name;
    public $wallet_remarks;

    public $walletAgents = [];
    public $walletNames = [];
    public $walletRemarksOptions = [];

    public $years = [];

    public function mount()
    {
        $this->walletAgents = WalletDetail::where('status','active')
            ->distinct()->orderBy('agent')->pluck('agent')->toArray();

        // Years starting 2026 and future-safe
        $currentYear = now()->year;
        $this->years = range(2026, $currentYear + 2);
    }

    public function updatedWalletAgent()
    {
        $this->wallet_name = null;
        $this->wallet_remarks = null;

        $this->walletNames = WalletDetail::where('agent', $this->wallet_agent)
            ->where('status','active')
            ->distinct()->pluck('wallet_name')->toArray();

        $this->walletRemarksOptions = [];
    }

    public function updatedWalletName()
    {
        $this->wallet_remarks = null;

        $this->walletRemarksOptions = WalletDetail::where('wallet_name', $this->wallet_name)
            ->where('agent', $this->wallet_agent)
            ->where('status','active')
            ->pluck('wallet_remarks')->toArray();
    }

    public function render()
    {
        $query = Transaction::query()
            ->when($this->year, fn($q) => $q->whereYear('transaction_date', $this->year))
            ->when($this->month, fn($q) => $q->whereMonth('transaction_date', $this->month))
            ->when($this->wallet_agent, fn($q) => $q->where('agent', $this->wallet_agent))
            ->when($this->wallet_name, fn($q) => $q->where('wallet_name', $this->wallet_name))
            ->when($this->wallet_remarks, fn($q) => $q->where('wallet_remarks', $this->wallet_remarks));

        $all = $query->get();

        $months = $all->pluck('transaction_date')
            ->filter()
            ->map(fn($d) => $d->format('Y-m'))
            ->unique()
            ->sortDesc()
            ->values();

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 7;

        $currentMonths = $months->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $paginatedMonths = new LengthAwarePaginator(
            $currentMonths,
            $months->count(),
            $perPage,
            $currentPage
        );

        $data = [];

        foreach ($currentMonths as $month) {

            $monthTxns = $all->filter(fn($t) =>
                $t->transaction_date && $t->transaction_date->format('Y-m') === $month
            );

            $grouped = $monthTxns->groupBy(fn($t) =>
                $t->wallet_name.'|'.$t->wallet_remarks.'|'.$t->agent
            );

            $rows = $grouped->map(function ($txns) {

                $count = $txns->count();
                $cashin = $txns->sum('cashin');
                $cashout = $txns->sum('cashout');

                $topPlayerId = $txns->groupBy('player_id')->map->count()->sortDesc()->keys()->first();
                $staffCounts = $txns->groupBy('created_by_id')->map->count()->sortDesc();

                $topStaffId = $staffCounts->keys()->first();
                $topStaffCount = $staffCounts->first();

                $topStaffName = optional(\App\Models\Staff::find($topStaffId))->staff_name ?? '-';

                return [
                    'wallet_name' => $txns->first()->wallet_name,
                    'wallet_remarks' => $txns->first()->wallet_remarks,
                    'agent' => $txns->first()->agent,
                    'count' => $count,
                    'cashin' => $cashin,
                    'cashout' => $cashout,
                    'net' => $cashin - $cashout,
                    'top_player' => optional(\App\Models\Player::find($topPlayerId))->player_name ?? '-',
                    'top_staff' => $topStaffName !== '-' ? "{$topStaffName} ({$topStaffCount})" : '-',
                ];
            })
                ->sortByDesc('count')
                ->sortByDesc('cashin')
                ->values()
                ->map(fn($row,$i)=> array_merge($row,['rank'=>$i+1]));

            $data[$month] = $rows;
        }

        return view('livewire.wallet-performance-monthly', [
            'data' => $data,
            'months' => $paginatedMonths,
            'walletAgents' => $this->walletAgents,
            'walletNames' => $this->walletNames,
            'walletRemarksOptions' => $this->walletRemarksOptions,
            'years' => $this->years,
        ]);
    }
}
