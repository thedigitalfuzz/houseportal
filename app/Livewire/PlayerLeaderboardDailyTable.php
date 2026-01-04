<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Transaction;
use Carbon\Carbon;

class PlayerLeaderboardDailyTable extends Component
{
    public $searchPlayer = '';
    public $searchDate = '';
    public $searchGame = '';

    public function render()
    {
        // Build a base query with filters applied
        $baseQuery = Transaction::query();

// Apply player filter
        if ($this->searchPlayer) {
            $baseQuery->whereHas('player', function($q) {
                $q->where('player_name', 'like', '%' . $this->searchPlayer . '%');
            });
        }

// Apply date filter
        if ($this->searchDate) {
            $baseQuery->whereDate('transaction_date', $this->searchDate);
        }

// Apply game filter
        if ($this->searchGame) {
            $baseQuery->where('game_id', $this->searchGame);
        }


// Get only dates that actually have filtered transactions
        $dates = (clone $baseQuery)
            ->selectRaw('DATE(transaction_date) as d')
            ->distinct()
            ->orderByDesc('d')
            ->pluck('d');


        $chunks = [];

        // Compute fixed ranks for all players per date (ignores search filters)
        $fixedRanks = [];
        foreach ($dates as $dateForRank) {
            $transactionsForRank = Transaction::with(['player', 'game'])
                ->whereDate('transaction_date', $dateForRank)
                ->get();

            $playersForRank = $transactionsForRank->groupBy(function ($txn) {
                return optional($txn->player)->player_name;
            });

            $rowsForRank = [];

            foreach ($playersForRank as $playerName => $playerTxns) {
                if (!$playerName) continue;

                $totalTransactions = $playerTxns->count();
                $totalCashin = $playerTxns->sum('cashin');
                $totalCashout = $playerTxns->sum('cashout');

                $rowsForRank[] = [
                    'player_name' => $playerName,
                    'total_transactions' => $totalTransactions,
                    'total_cashin' => $totalCashin,
                    'total_cashout' => $totalCashout,
                    'net' => $totalCashin - $totalCashout,
                ];
            }

            $rowsForRank = collect($rowsForRank)
                ->sortByDesc('net')
                ->sortByDesc('total_cashin')
                ->sortByDesc('total_transactions')
                ->values();

            foreach ($rowsForRank as $rank => $r) {
                $fixedRanks[$dateForRank][$r['player_name']] = $rank + 1;
            }
        }


        foreach ($dates as $date) {

            // Load transactions with required relations
            $transactions = Transaction::with(['player', 'game'])
                ->whereDate('transaction_date', $date)
                ->when($this->searchPlayer, function($q) {
                    $q->whereHas('player', function($q2) {
                        $q2->where('player_name', 'like', '%' . $this->searchPlayer . '%');
                    });
                })
                ->when($this->searchDate, function($q) {
                    $q->whereDate('transaction_date', $this->searchDate);
                })
                ->when($this->searchGame, function($q) {
                    $q->where('game_id', $this->searchGame);
                })
                ->get();

            /**
             * GROUP BY PLAYER NAME
             * (Joe Stark = one block, even if multiple usernames)
             */
            $players = $transactions->groupBy(function ($txn) {
                return optional($txn->player)->player_name;
            });

            $rows = [];

            foreach ($players as $playerName => $playerTxns) {

                if (!$playerName) {
                    continue;
                }

                /**
                 * GROUP BY player_id
                 * Each player_id = ONE username
                 */
                $usernameGroups = $playerTxns->groupBy('player_id');

                $usernameRows = [];

                $totalTxn = 0;
                $totalCashin = 0;
                $totalCashout = 0;

                foreach ($usernameGroups as $group) {

                    $first = $group->first();

                    $username = optional($first->player)->username;
                    $gameName = optional($first->game)->name;

                    $txnCount = $group->count();
                    $cashin = $group->sum('cashin');
                    $cashout = $group->sum('cashout');

                    $totalTxn += $txnCount;
                    $totalCashin += $cashin;
                    $totalCashout += $cashout;

                    $usernameRows[] = [
                        'username'     => $username,
                        'game'         => $gameName,
                        'transactions' => $txnCount,
                        'cashin'       => $cashin,
                        'cashout'      => $cashout,
                        'net'          => $cashin - $cashout,
                    ];
                }

                $rows[] = [
                    'player_name'        => $playerName,
                    'total_transactions' => $totalTxn,
                    'total_cashin'       => $totalCashin,
                    'total_cashout'      => $totalCashout,
                    'net'                => $totalCashin - $totalCashout,
                    'usernames'          => $usernameRows,
                ];
            }

            /**
             * SORTING:
             * 1. Transactions
             * 2. Cashin
             * 3. Net
             */
            $rows = collect($rows)
                ->sortByDesc('net')
                ->sortByDesc('total_cashin')
                ->sortByDesc('total_transactions')
                ->values();

            $dateTotals = [
                'cashin'  => $rows->sum('total_cashin'),
                'cashout' => $rows->sum('total_cashout'),
                'net'     => $rows->sum('net'),
                'transactions' => $rows->sum('total_transactions'),
            ];

            $chunks[] = [
                'label'  => Carbon::parse($date)->format('d M Y'),
                'date'   => $date,
                'rows'   => $rows,
                'totals' => $dateTotals,
            ];
        }

        $games = \App\Models\Game::orderBy('name')->get();

        return view('livewire.player-leaderboard-daily-table', compact('chunks', 'games','fixedRanks'));
    }
}
