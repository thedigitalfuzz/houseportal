<?php

namespace App\Services;

use App\Models\Game;
use App\Models\GamePoint;
use App\Models\Transaction;
use Carbon\Carbon;


class GamePointService
{

    public function getData($filters = [])
    {
        $searchGame = $filters['game_id'] ?? null;
        $dateFrom = $filters['date_from'] ?? null;
        $dateTo = $filters['date_to'] ?? null;

        // ✅ FETCH TRANSACTIONS (ONLY transaction_date)
        $transactions = Transaction::query()
            ->when($searchGame, fn($q) => $q->where('game_id', $searchGame))
            ->when($dateFrom, fn($q) => $q->whereDate('transaction_date', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('transaction_date', '<=', $dateTo))
            ->get();

        if ($transactions->isEmpty()) {
            return [];
        }

        // ✅ GROUP
        $grouped = $transactions->groupBy(fn($t) =>
        Carbon::parse($t->transaction_date)->format('Y-m-d')
        );

        // ✅ DATE RANGE
        $start = Carbon::parse($dateFrom ?? $transactions->min('transaction_date'));
        $end = Carbon::parse($dateTo ?? $transactions->max('transaction_date'));

        $dates = collect($grouped)->keys()->sort()->values()->all();

        $games = Game::when($searchGame, fn($q) => $q->where('id', $searchGame))->get();
        $previousClosing = [];
        $result = [];

        foreach ($dates as $date) {

            foreach ($games as $game) {

                $txns = $grouped[$date] ?? collect();
                $gTxns = $txns->where('game_id', $game->id);

                $cashin = $gTxns->where('transaction_type', 'cashin')->sum('credits_used');
                $cashout = $gTxns->where('transaction_type', 'cashout')->sum('credits_used');

                $netUsed = $gTxns->sum('credits_used');

                $totalBonus = max(0, $gTxns->sum('bonus_added'));

                $row = GamePoint::where('game_id', $game->id)
                    ->where('date', $date)
                    ->first();

                $recharge = $row->recharge_points ?? 0;

                $starting = ($previousClosing[$game->id] ?? 0) + $recharge;

                // 🔥 PRIORITY LOGIC

                $isOverride = $row && $row->updated_by_id;
                $isManual = $row && $row->created_by_id && !$row->updated_by_id && $row->points !== null;
                if ($isOverride) {
                    $closing = $row->points;
                    $used = $starting - $closing;
                }
                elseif ($isManual) {
                    $closing = $row->points;
                    $used = $starting - $closing;
                }
                else {
                    $used = $netUsed;
                    $closing = $starting - $used;
                }

                $row = GamePoint::updateOrCreate(
                    [
                        'game_id' => $game->id,
                        'date' => $date,
                    ],
                    [
                        'total_starting_points' => $starting,
                        'used_points' => $used,
                        'points' => $closing,
                        'bonus_added' => $totalBonus,
                    ]
                );

                $result[$date][] = [
                    'game' => $game,
                    'starting' => $starting,
                    'used' => $used,
                    'closing' => $closing,
                    'recharge' => $recharge,
                    'row' => $row,
                    'bonus_added_points' => $totalBonus,
                ];

                $previousClosing[$game->id] = $closing;
            }
        }

       // return array_reverse($result, true);
        return $result;

    }
}
