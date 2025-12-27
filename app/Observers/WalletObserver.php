<?php

namespace App\Observers;

use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\WalletDetail;

class WalletObserver
{
    public function created(Wallet $wallet)
    {
        $this->updateNetTransaction($wallet);
    }

    public function updated(Wallet $wallet)
    {
        $this->updateNetTransaction($wallet);
    }

    protected function updateNetTransaction(Wallet $wallet)
    {
        {
            $date = $wallet->date; // wallet date column

            $transactions = Transaction::whereDate('transaction_date', $date)
                ->where('agent', $wallet->agent)
                ->where('wallet_name', $wallet->wallet_name)
                ->where('wallet_remarks', $wallet->wallet_remarks)
                ->get();

            $net = 0;

            foreach ($transactions as $t) {
                // cashin adds
                $net += floatval($t->cashin);

                // cashout + bonus subtracts
                $net -= floatval($t->cashout);
                $net -= floatval($t->bonus_added);
            }

            // prevent infinite loop
            $wallet->withoutEvents(function () use ($wallet, $net) {
                $wallet->update([
                    'net_transaction' => $net
                ]);
            });
        }
    }

    protected function computeVarianceBalance(WalletDetail $wallet)
    {
        // Fetch previous day's wallet balance
        $previousWallet = WalletDetail::where('agent', $wallet->agent)
            ->where('wallet_name', $wallet->wallet_name)
            ->where('wallet_remarks', $wallet->wallet_remarks)
            ->whereDate('date', '<', $wallet->date)
            ->orderBy('date', 'desc')
            ->first();

        $previousBalance = $previousWallet->current_balance ?? 0;
        $balanceDiff = $wallet->current_balance - $previousBalance;

        if ($balanceDiff == $wallet->net_transaction) {
            $wallet->variance_balance = '✔';
        } else {
            $wallet->variance_balance = '✖ ' . number_format($balanceDiff - $wallet->net_transaction, 2);
        }
    }
}
