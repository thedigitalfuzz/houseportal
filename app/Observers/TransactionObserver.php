<?php

namespace App\Observers;

use App\Models\Transaction;
use App\Models\Wallet;

class TransactionObserver
{
    /**
     * Recalculate net transaction for a given wallet on a given date.
     */
    protected function updateWalletNetTransaction($agent, $walletName, $walletRemarks, $date)
    {
        $wallet = Wallet::where('agent', $agent)
            ->where('wallet_name', $walletName)
            ->where('wallet_remarks', $walletRemarks)
            ->whereDate('date', $date)
            ->first();

        if (!$wallet) {
            return;
        }

        $netTransaction = Transaction::where('agent', $agent)
            ->where('wallet_name', $walletName)
            ->where('wallet_remarks', $walletRemarks)
            ->whereDate('transaction_date', $date)
            ->sum(\DB::raw('cashin - (cashout + IFNULL(bonus_added,0))'));

        $wallet->update(['net_transaction' => $netTransaction]);
    }

    public function created(Transaction $transaction)
    {
        $this->updateWalletNetTransaction(
            $transaction->agent,
            $transaction->wallet_name,
            $transaction->wallet_remarks,
            $transaction->transaction_date
        );
    }

    public function updated(Transaction $transaction)
    {
        // Recalculate for **old wallet** first
        $original = $transaction->getOriginal(); // original values before update
        $this->updateWalletNetTransaction(
            $original['agent'],
            $original['wallet_name'],
            $original['wallet_remarks'],
            $original['transaction_date']
        );

        // Then recalculate for **current wallet** after update
        $this->updateWalletNetTransaction(
            $transaction->agent,
            $transaction->wallet_name,
            $transaction->wallet_remarks,
            $transaction->transaction_date
        );
    }

    public function deleted(Transaction $transaction)
    {
        // Recalculate net transaction for the wallet of the deleted transaction
        $this->updateWalletNetTransaction(
            $transaction->agent,
            $transaction->wallet_name,
            $transaction->wallet_remarks,
            $transaction->transaction_date
        );
    }
}
