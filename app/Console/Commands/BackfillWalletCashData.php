<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class BackfillWalletCashData extends Command
{
    protected $signature = 'wallets:backfill-cash';
    protected $description = 'Backfill cashin, cashout and bonus data into wallets table';

    public function handle()
    {
        $this->info('Starting wallet cash backfill...');

        Wallet::chunk(200, function ($wallets) {
            foreach ($wallets as $wallet) {

                $query = Transaction::where('agent', $wallet->agent)
                    ->where('wallet_name', $wallet->wallet_name)
                    ->whereDate('transaction_date', $wallet->date);

                if (is_null($wallet->wallet_remarks)) {
                    $query->whereNull('wallet_remarks');
                } else {
                    $query->where('wallet_remarks', $wallet->wallet_remarks);
                }

                $cashin  = $query->sum('cashin');
                $cashout = $query->sum('cashout');
                $bonus   = $query->sum('bonus_added');

                $wallet->update([
                    'cashin' => $cashin,
                    'cashout' => $cashout,
                    'bonus' => $bonus,
                ]);
            }
        });

        $this->info('Wallet cash backfill completed successfully.');
        return 0;
    }
}
