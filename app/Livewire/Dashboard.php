<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Wallet;
use App\Models\GameCredit;
use App\Models\Transaction;
use App\Models\WalletDetail;


class Dashboard extends Component
{
    public $recentWallets = [];
    public $recentGameCredits = [];
    public $recentTransactions = [];
    public $recentWalletDetails = [];

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
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
