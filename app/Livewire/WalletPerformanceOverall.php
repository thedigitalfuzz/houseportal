<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Transaction;
use App\Models\WalletDetail;

class WalletPerformanceOverall extends Component
{
    public $wallet_agent;
    public $wallet_name;
    public $wallet_remarks;

    public $walletAgents = [];
    public $walletNames = [];
    public $walletRemarksOptions = [];

    public function mount()
    {
        $this->walletAgents = WalletDetail::where('status','active')
            ->select('agent')
            ->distinct()
            ->pluck('agent')
            ->toArray();
    }

    public function updatedWalletAgent()
    {
        $this->wallet_name = null;
        $this->wallet_remarks = null;

        $this->walletNames = WalletDetail::where('agent', $this->wallet_agent)
            ->where('status','active')
            ->select('wallet_name')
            ->distinct()
            ->pluck('wallet_name')
            ->toArray();

        $this->walletRemarksOptions = [];
    }

    public function updatedWalletName()
    {
        $this->wallet_remarks = null;

        $this->walletRemarksOptions = WalletDetail::where('wallet_name', $this->wallet_name)
            ->where('agent', $this->wallet_agent)
            ->where('status','active')
            ->pluck('wallet_remarks')
            ->toArray();
    }

    public function render()
    {
        $query = Transaction::query()
            ->when($this->wallet_agent, fn($q) => $q->where('agent', $this->wallet_agent))
            ->when($this->wallet_name, fn($q) => $q->where('wallet_name', $this->wallet_name))
            ->when($this->wallet_remarks, fn($q) => $q->where('wallet_remarks', $this->wallet_remarks));

        $all = $query->get();

        $grouped = $all->groupBy(fn($t) =>
            $t->wallet_name . '|' . $t->wallet_remarks . '|' . $t->agent
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

        return view('livewire.wallet-performance-overall',[
            'rows'=>$rows,
            'walletAgents'=>$this->walletAgents,
            'walletNames'=>$this->walletNames,
            'walletRemarksOptions'=>$this->walletRemarksOptions
        ]);
    }
}
