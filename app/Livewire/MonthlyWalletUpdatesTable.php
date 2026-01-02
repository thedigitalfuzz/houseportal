<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Wallet;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MonthlyWalletUpdatesTable extends Component
{
    public $month;
    public $year;
    public $searchMode = false;

    public function mount()
    {
        $this->month = '';
        $this->year = '';
    }

    public function search()
    {
        $this->searchMode = true;
    }

    public function render()
    {
        $query = Wallet::query();

        if ($this->searchMode) {
            if ($this->year) $query->whereYear('date', $this->year);
            if ($this->month) $query->whereMonth('date', $this->month);
        }

        $walletsGrouped = $query->get()
            ->groupBy(function($item) {
                return Carbon::parse($item->date)->format('Y-m');
            })
            ->sortKeysDesc();

        $monthlyData = [];

        foreach ($walletsGrouped as $monthKey => $wallets) {

            $uniqueWallets = $wallets->unique(function($item) {
                return $item->agent.'-'.$item->wallet_name.'-'.($item->wallet_remarks ?? '');
            });

            $walletRows = [];

            foreach ($uniqueWallets->sortBy([
                fn($a,$b) => strcmp($a->agent,$b->agent),
                fn($a,$b) => strcmp($a->wallet_name,$b->wallet_name),
                fn($a,$b) => strcmp($a->wallet_remarks ?? '', $b->wallet_remarks ?? '')
            ]) as $wallet) {

                // Opening & Closing balances
                $firstEntry = Wallet::where('agent', $wallet->agent)
                    ->where('wallet_name', $wallet->wallet_name)
                    ->when($wallet->wallet_remarks, fn($q)=>$q->where('wallet_remarks', $wallet->wallet_remarks))
                    ->whereYear('date', Carbon::parse($monthKey.'-01')->year)
                    ->whereMonth('date', Carbon::parse($monthKey.'-01')->month)
                    ->orderBy('date','asc')->first();

                $lastEntry = Wallet::where('agent', $wallet->agent)
                    ->where('wallet_name', $wallet->wallet_name)
                    ->when($wallet->wallet_remarks, fn($q)=>$q->where('wallet_remarks', $wallet->wallet_remarks))
                    ->whereYear('date', Carbon::parse($monthKey.'-01')->year)
                    ->whereMonth('date', Carbon::parse($monthKey.'-01')->month)
                    ->orderBy('date','desc')->first();

                // Sum transactions
                $totals = Transaction::where('agent', $wallet->agent)
                    ->where('wallet_name', $wallet->wallet_name)
                    ->when($wallet->wallet_remarks, fn($q)=>$q->where('wallet_remarks', $wallet->wallet_remarks))
                    ->whereYear('transaction_date', Carbon::parse($monthKey.'-01')->year)
                    ->whereMonth('transaction_date', Carbon::parse($monthKey.'-01')->month)
                    ->selectRaw('SUM(cashin) as total_cashin, SUM(cashout) as total_cashout, SUM(cashin - cashout) as net_transaction')
                    ->first();

                $walletRows[] = [
                    'agent' => $wallet->agent,
                    'wallet_name' => $wallet->wallet_name,
                    'wallet_remarks' => $wallet->wallet_remarks,
                    'total_cashin' => $totals->total_cashin ?? 0,
                    'total_cashout' => $totals->total_cashout ?? 0,
                    'net_transaction' => $totals->net_transaction ?? 0,
                    'opening_balance' => $firstEntry->current_balance ?? 0,
                    'closing_balance' => $lastEntry->current_balance ?? 0,
                ];
            }

            $monthlyData[] = [
                'month' => $monthKey,
                'wallets' => $walletRows,
            ];
        }

        return view('livewire.monthly-wallet-updates-table', [
            'monthlyData' => $monthlyData,
        ]);
    }
}
