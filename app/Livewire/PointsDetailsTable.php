<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Transaction;
use App\Models\Game;
use App\Models\GamePoint;
use Carbon\Carbon;

class PointsDetailsTable extends Component
{
    public $tab = 'daily';
    public $selectedGame = null;
    public $walletPage = 0;

    public function setTab($tab)
    {
        $this->tab = $tab;
        $this->walletPage = 0;
    }
    public function nextWalletReport()
    {
        $this->walletPage++;
    }

    public function prevWalletReport()
    {
        if ($this->walletPage > 0) {
            $this->walletPage--;
        }
    }

    public function setGame($gameId)
    {
        $this->selectedGame = $gameId;
    }
    private function resolveUsedPoints($gpKey, $gamePoints, $transactionUsed)
    {
        if (isset($gamePoints[$gpKey])) {

            $gp = $gamePoints[$gpKey];

            // MUST ALWAYS prioritize computed value from authoritative fields
            if ($gp->total_starting_points !== null && $gp->points !== null) {
                return $gp->total_starting_points - $gp->points;
            }

            // fallback ONLY if structure broken
            if (!is_null($gp->used_points)) {
                return $gp->used_points;
            }
        }

        // last fallback
        return $transactionUsed[$gpKey] ?? 0;
    }
    protected $listeners = [
        'refreshPoints' => 'forceReload'
    ];
    public function forceReload()
    {
        // forces Livewire to fully re-run render cycle cleanly
        $this->resetPage();
        $this->render();
    }

    private function periodKey($date)
    {
        $dt = Carbon::parse($date);

        return match ($this->tab) {
            'monthly' => [
                'label' => $dt->format('Y-F'),
                'sort'  => $dt->format('Y-m'),
            ],
            'yearly' => [
                'label' => $dt->format('Y'),
                'sort'  => $dt->format('Y'),
            ],
            'all' => [
                'label' => 'Overall',
                'sort'  => 'Overall',
            ],
            default => [
                'label' => $dt->format('Y-F-d'),
                'sort'  => $dt->format('Y-m-d'),
            ],
        };
    }

    public function render()
    {
        $usedTracker = [
            'total' => [],
            'game' => [],
            'wallet' => [],
        ];
        $txns = Transaction::with(['game', 'player'])->get();

        $gamePoints = GamePoint::query()
            ->select([
                'game_id',
                'date',
                'used_points',
                'total_starting_points',
                'points'
            ])
            ->get()
            ->fresh()
            ->keyBy(fn($g) =>
                $g->game_id . '-' . Carbon::parse($g->date)->format('Y-m-d')
            );
        $transactionUsed = [];

        foreach ($txns as $t) {

            $date = Carbon::parse($t->transaction_date ?? $t->created_at)->format('Y-m-d');
            $gpKey = $t->game_id . '-' . $date;

            if (!isset($transactionUsed[$gpKey])) {
                $transactionUsed[$gpKey] = 0;
            }

       //     $cashin = $t->transaction_type === 'cashin' ? ($t->credits_used ?? 0) : 0;
         //   $cashout = $t->transaction_type === 'cashout' ? ($t->credits_used ?? 0) : 0;

         //   $transactionUsed[$gpKey] += ($t->credits_used ?? 0);
            $transactionUsed[$gpKey] += (float) ($t->credits_used ?? 0);
        }
        // =========================
        // TOTAL SUMMARY
        // =========================
        $totalSummary = [];

        foreach ($txns as $t) {



            $periodData = $this->periodKey($t->transaction_date ?? $t->created_at);
            $period = $periodData['label'];
            $sortKey = $periodData['sort'];

            if ($this->selectedGame && $t->game_id != $this->selectedGame) continue;

            $cashin = $t->cashin ?? 0;
            $cashout = $t->cashout ?? 0;
            $net = $cashin - $cashout;
            $bonus = max(0, $t->bonus_added ?? 0);

           // $gpKey = $t->game_id . '-' . Carbon::parse($t->transaction_date)->format('Y-m-d');
            $date = Carbon::parse($t->transaction_date ?? $t->created_at)->format('Y-m-d');
            $gpKey = $t->game_id . '-' . $date;



            if (!isset($totalSummary[$sortKey])) {
                $totalSummary[$sortKey] = [
                    'label' => $period,
                    'cashin' => 0,
                    'cashout' => 0,
                    'net' => 0,
                    'bonus' => 0,
                    'used' => 0,
                ];
            }

            $uniqueKey = $sortKey . '-' . $gpKey;

            if (!isset($usedTracker['total'][$uniqueKey])) {

                $used = $this->resolveUsedPoints($gpKey, $gamePoints, $transactionUsed);

                $totalSummary[$sortKey]['used'] =
                    ($totalSummary[$sortKey]['used'] ?? 0) + $used;

                $usedTracker['total'][$uniqueKey] = true;
            }

            $totalSummary[$sortKey]['cashin'] += $cashin;
            $totalSummary[$sortKey]['cashout'] += $cashout;
            $totalSummary[$sortKey]['net'] += $net;
            $totalSummary[$sortKey]['bonus'] += $bonus;
           // $totalSummary[$sortKey]['used'] += $used;
        }

        krsort($totalSummary);

        // =========================
        // GAME SUMMARY
        // =========================
        $gameSummary = [];

        foreach ($txns as $t) {

            if ($this->selectedGame && $t->game_id != $this->selectedGame) continue;

            $periodData = $this->periodKey($t->transaction_date ?? $t->created_at);
            $period = $periodData['label'];
            $sortKey = $periodData['sort'];

            $game = $t->game->name ?? 'Unknown';

            $key = $sortKey . '-' . $game;

         //   $gpKey = $t->game_id . '-' . Carbon::parse($t->transaction_date)->format('Y-m-d');
            $date = Carbon::parse($t->transaction_date ?? $t->created_at)->format('Y-m-d');
            $gpKey = $t->game_id . '-' . $date;

            if (!isset($gameSummary[$key])) {
                $gameSummary[$key] = [
                    'period' => $period,
                    'sort' => $sortKey,
                    'game' => $game,
                    'cashin' => 0,
                    'cashout' => 0,
                    'bonus' => 0,
                    'used' => 0,
                ];
            }

            $uniqueKey = $key . '-' . $gpKey;

            if (!isset($usedTracker['game'][$uniqueKey])) {

                $used = $this->resolveUsedPoints($gpKey, $gamePoints, $transactionUsed);

                $gameSummary[$key]['used'] =
                    ($gameSummary[$key]['used'] ?? 0) + $used;

                $usedTracker['game'][$uniqueKey] = true;
            }

            $gameSummary[$key]['cashin'] += $t->cashin;
            $gameSummary[$key]['cashout'] += $t->cashout;
            $gameSummary[$key]['bonus'] += $t->bonus_added;
           // $gameSummary[$key]['used'] += $used;
        }
        uksort($gameSummary, fn($a, $b) => strcmp($b, $a));
        // =========================
        // PLAYER SUMMARY
        // =========================
        $playerSummary = [];

        foreach ($txns as $t) {

            if ($this->selectedGame && $t->game_id != $this->selectedGame) continue;

            // ✅ FIX: group by PLAYER NAME (same as PlayerRankings page)
            $playerName = $t->player->player_name ?? $t->player->name ?? $t->player->username ?? 'Unknown';

            if (!isset($playerSummary[$playerName])) {
                $playerSummary[$playerName] = [
                    'player' => $playerName,
                    'cashin' => 0,
                    'cashout' => 0,
                    'net' => 0,
                    'bonus' => 0,
                    'used' => 0,
                ];
            }

            $cashin = $t->cashin ?? 0;
            $cashout = $t->cashout ?? 0;
            $net = $cashin - $cashout;

           // $gpKey = $t->game_id . '-' . Carbon::parse($t->transaction_date)->format('Y-m-d');
            $date = Carbon::parse($t->transaction_date ?? $t->created_at)->format('Y-m-d');
            $gpKey = $t->game_id . '-' . $date;

            // Player used = THEIR OWN credits_used (no grouping logic needed)
            $playerSummary[$playerName]['used'] += ($t->credits_used ?? 0);

            $playerSummary[$playerName]['cashin'] += $cashin;
            $playerSummary[$playerName]['cashout'] += $cashout;
            $playerSummary[$playerName]['net'] += $net;
            $playerSummary[$playerName]['bonus'] += $t->bonus_added;
          //  $playerSummary[$playerName]['used'] += $used;
        }

        uasort($playerSummary, fn($a, $b) => $b['used'] <=> $a['used']);

        // =========================
        // WALLET SUMMARY (FIXED)
        // =========================
        $walletPeriods = [];

        foreach ($txns as $t) {

            if ($this->selectedGame && $t->game_id != $this->selectedGame) continue;

            $periodData = $this->periodKey($t->transaction_date ?? $t->created_at);
            $period = $periodData['label'];
            $sortKey = $periodData['sort'];

            $walletName = $t->wallet_name ?? 'Unknown';
            $remarks = $t->wallet_remarks ?? '';

          //  $key = $sortKey . '-' . $walletName . '-' . $remarks;
            $key = $sortKey . '-' . $t->game_id . '-' . $walletName . '-' . $remarks;

            $cashin = $t->cashin ?? 0;
            $cashout = $t->cashout ?? 0;
            $net = $cashin - $cashout;

          //  $gpKey = $t->game_id . '-' . Carbon::parse($t->transaction_date)->format('Y-m-d');
            $date = Carbon::parse($t->transaction_date ?? $t->created_at)->format('Y-m-d');
            $gpKey = $t->game_id . '-' . $date;

            if (!isset($walletPeriods[$sortKey])) {
                $walletPeriods[$sortKey] = [
                    'label' => $period,
                    'items' => []
                ];
            }

            if (!isset($walletPeriods[$sortKey]['items'][$key])) {
                $walletPeriods[$sortKey]['items'][$key] = [
                    'wallet' => $walletName,
                    'remarks' => $remarks,
                    'cashin' => 0,
                    'cashout' => 0,
                    'net' => 0,
                    'used' => 0,
                ];
            }

            $uniqueKey = $sortKey . '-' . $gpKey;

            $used = (float) ($t->credits_used ?? 0);

            $walletPeriods[$sortKey]['items'][$key]['used'] += $used;

            $walletPeriods[$sortKey]['items'][$key]['cashin'] += $cashin;
            $walletPeriods[$sortKey]['items'][$key]['cashout'] += $cashout;
            $walletPeriods[$sortKey]['items'][$key]['net'] += $net;
           // $walletPeriods[$sortKey]['items'][$key]['used'] += $used;
        }

        krsort($walletPeriods);

        $gameLabel = 'All Games';

        if ($this->selectedGame) {
            $gameLabel = Game::find($this->selectedGame)?->name ?? 'All Games';
        }

        return view('livewire.points-details-table', [
            'tab' => $this->tab,
            'selectedGame' => $this->selectedGame,
            'totalSummary' => $totalSummary,
            'gameSummary' => $gameSummary,
            'playerSummary' => $playerSummary,
            'walletPeriods' => $walletPeriods,
            'walletPage' => $this->walletPage,
            'games' => Game::all(),
            'gameLabel' => $gameLabel,
        ]);
    }
}
