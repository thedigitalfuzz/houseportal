<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Game;


class GamePerformance extends Component
{
    public $game_id = '';

    public $activeTab = 'daily';
    public $searchGame = '';
    public $searchDate = null;

    protected function baseQuery()
    {
        return Transaction::query()
            ->join('games', 'transactions.game_id', '=', 'games.id')
            // Use game_id from dropdown
            ->when($this->game_id, fn ($q) =>
            $q->where('games.id', $this->game_id)
            )
            ->when($this->searchDate, fn ($q) =>
            $q->whereDate('transactions.transaction_date', $this->searchDate)
            );
    }


    protected function aggregatedQuery($query)
    {
        return $query
            ->selectRaw('
                games.id as game_id,
                games.name as game_name,
                COUNT(transactions.id) as total_transactions,
                SUM(transactions.cashin) as total_cashin,
                SUM(transactions.cashout) as total_cashout,
                SUM(transactions.cashin - transactions.cashout) as total_net
            ')
            ->groupBy('games.id', 'games.name')
            ->orderByDesc('total_transactions')
            ->orderByDesc('total_cashin')
            ->orderByDesc('total_net')
            ->get();
    }

    protected function topPlayer($gameId, $range = null)
    {
        return Transaction::query()
            ->join('players', 'transactions.player_id', '=', 'players.id')
            ->where('transactions.game_id', $gameId)
            ->when($range, fn ($q) =>
            $q->whereBetween('transactions.transaction_date', $range)
            )
            ->selectRaw('players.player_name, SUM(transactions.cashin) as total')
            ->groupBy('players.player_name')
            ->orderByDesc('total')
            ->value('players.player_name');
    }

    public function render()
    {
        $chunks = [];

        $games = Game::orderBy('name')->get();


        /** DAILY */
        if ($this->activeTab === 'daily') {
            $dates = Transaction::selectRaw('DATE(transaction_date) as d')
                ->distinct()
                ->orderByDesc('d')
                ->get();

            foreach ($dates as $d) {
                $start = Carbon::parse($d->d)->startOfDay();
                $end   = Carbon::parse($d->d)->endOfDay();

                $rows = $this->aggregatedQuery(
                    $this->baseQuery()->whereBetween('transactions.transaction_date', [$start, $end])
                )->map(function ($row) use ($start, $end) {
                    $row->top_player = $this->topPlayer($row->game_id, [$start, $end]);
                    // --- NEW: Fetch used points for this game on this date ---
                    $gamePoint = \App\Models\GamePoint::where('game_id', $row->game_id)
                        ->where('date', $start->format('Y-m-d'))
                        ->first();

                    $row->used_points = $gamePoint ? $gamePoint->used_points : 0;
                    return $row;
                });



                if ($rows->isNotEmpty()) {
                    $chunks[] = [
                        'label' => Carbon::parse($d->d)->format('d M Y'),
                        'rows'  => $rows,
                    ];
                }
            }
        }

        /** MONTHLY */
        if ($this->activeTab === 'monthly') {
            $months = Transaction::selectRaw('YEAR(transaction_date) y, MONTH(transaction_date) m')
                ->distinct()
                ->orderByDesc('y')
                ->orderByDesc('m')
                ->get();

            foreach ($months as $m) {
                $start = Carbon::create($m->y, $m->m, 1)->startOfMonth();
                $end   = Carbon::create($m->y, $m->m, 1)->endOfMonth();

                $rows = $this->aggregatedQuery(
                    $this->baseQuery()->whereBetween('transactions.transaction_date', [$start, $end])
                )->map(function ($row) use ($start, $end) {
                    $row->top_player = $this->topPlayer($row->game_id, [$start, $end]);
                    // --- NEW: Fetch used points for this game on this date ---
                    $gamePoint = \App\Models\GamePoint::where('game_id', $row->game_id)
                        ->whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
                        ->sum('used_points');

                    $row->used_points = $gamePoint ?? 0;
                    return $row;
                });





                if ($rows->isNotEmpty()) {
                    $chunks[] = [
                        'label' => $start->format('F Y'),
                        'rows'  => $rows,
                    ];
                }
            }
        }

        /** ALL TIME */
        if ($this->activeTab === 'all') {
            // IGNORE date filter here
            $allTimeQuery = Transaction::query()
                ->join('games', 'transactions.game_id', '=', 'games.id')
                ->when($this->game_id, fn ($q) =>
                $q->where('games.id', $this->game_id)
                );

            $rows = $this->aggregatedQuery($allTimeQuery)
                ->map(function ($row) {
                    $row->top_player = $this->topPlayer($row->game_id);
                    $usedPoints = \App\Models\GamePoint::where('game_id', $row->game_id)
                        ->sum('used_points');

                    $row->used_points = $usedPoints ?? 0;
                    return $row;
                });

            $chunks[] = [
                'label' => 'All Time',
                'rows'  => $rows,
            ];
        }




        return view('livewire.game-performance', compact('chunks','games'));
    }
}
