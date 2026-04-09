<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Notification;
use App\Models\Staff;
use App\Models\Transaction;
use App\Helpers\NotificationHelper;

class SendDailyStaffSummary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-daily-staff-summary';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $staffs = \App\Models\Staff::all();

        foreach ($staffs as $staff) {

            $today = now()->toDateString();

            $cashin = Transaction::where('created_by_id', $staff->id)
                ->whereDate('transaction_date', $today)
                ->sum('cashin');

            $cashout = Transaction::where('created_by_id', $staff->id)
                ->whereDate('transaction_date', $today)
                ->sum('cashout');

            $net = $cashin - $cashout;

            NotificationHelper::send(
                [$staff],
                'summary',
                "Today Cashin: $cashin, Cashout: $cashout, Net: $net",
                '/reports'
            );
        }
    }
}
