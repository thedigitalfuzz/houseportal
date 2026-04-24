<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\GamePoint;
use App\Models\Game;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;
use App\Models\Transaction;

class GamePointsTable extends Component
{
    use WithPagination;

    public $searchGame;
    public $dateFrom;
    public $dateTo;
    public $perPage = 5;

    public $editModal = false;
    public $deleteModal = false;
    public $editingId = null;

    public $editDate;
    public $editGameId;
    public $editPoints;

    public $rechargeModal = false;
    public $rechargeGameId;
    public $rechargeDate;
    public $rechargeAmount;

    // Recharge list modal
    public $rechargeListModal = false;
    public $editRechargeId = null;
    public $editRechargeAmount = null;
    public $deleteRechargeId = null; // for confirmation

    protected $listeners = ['refreshGamePoints' => '$refresh'];

    public function mount()
    {
        $this->resetInputs();
        $this->resetRecharge();
    }

    protected function currentUser()
    {
        return Auth::guard('web')->user() ?? Auth::guard('staff')->user();
    }

    // ----------------------
    // ADD / EDIT MAIN POINTS
    // ----------------------
    public function resetInputs()
    {
        $this->editingId = null;
        $this->editDate = now()->format('Y-m-d');
        $this->editGameId = null;
        $this->editPoints = null;
    }

    //public function openAddModal()
   // {
     //   $this->resetInputs();
       // $this->editModal = true;
    //}

    public function editRecord($id)
    {
        $record = GamePoint::findOrFail($id);

        $this->editingId = $id;
        $this->editDate = Carbon::parse($record->date)->format('Y-m-d');
        $this->editGameId = $record->game_id;
        $this->editPoints = $record->points;

        $this->editModal = true;
    }

    public function saveRecord()
    {
        $this->validate([
            'editDate' => 'required|date',
            'editGameId' => 'required|exists:games,id',
            'editPoints' => 'required|numeric|min:0',
        ]);

        $user = $this->currentUser();
        $userType = get_class($user);
        if (!$this->editingId) return;
        if ($this->editingId) {
            // UPDATE existing main points
            $record = GamePoint::findOrFail($this->editingId);
            $record->update([
                'game_id' => $this->editGameId,
                'points' => $this->editPoints,
                'date' => $this->editDate,
                'updated_by_id' => $user->id,
                'updated_by_type' => $userType,
            ]);
        } else {
            // CREATE or fetch row if recharge exists
            $record = GamePoint::firstOrNew([
                'game_id' => $this->editGameId,
                'date' => $this->editDate,
            ]);

            if (!$record->exists) {
                $record->recharge_points = 0;
                $record->total_starting_points = 0;
            }
            $record->points = $this->editPoints;
            $record->updated_by_id = $user->id;
            $record->updated_by_type = $userType;
        }

        // --- FIX: recalc total starting points for THIS row only ---
        $previous = GamePoint::where('game_id', $this->editGameId)
            ->where('date', '<', $this->editDate)
            ->orderBy('date', 'desc')
            ->first();

        $previousPoints = $previous ? $previous->points : 0;
        $record->total_starting_points = $previousPoints + ($record->recharge_points ?? 0);

        // Calculate used points
        $record->used_points = $record->total_starting_points - $record->points;

        $record->save();

        // --- FIX: update total_starting_points of all FUTURE rows for this game ---
        $nextRecords = GamePoint::where('game_id', $this->editGameId)
            ->where('date', '>', $this->editDate)
            ->orderBy('date', 'asc')
            ->get();

        $prevPoints = $record->points; // this row's main points
        foreach ($nextRecords as $next) {
            $next->total_starting_points = $prevPoints + ($next->recharge_points ?? 0);
            if ($next->points !== null && $next->points > 0) {
                $next->used_points = $next->total_starting_points - $next->points;
            }
            $next->save();
            $prevPoints = $next->points; // for next iteration
        }

        $this->editModal = false;
        $this->resetInputs();
        $this->resetPage();
    }

    public function deleteRecord($id)
    {
        $this->editingId = $id;
        $this->deleteModal = true;
    }

    public function confirmDelete()
    {
        $record = GamePoint::findOrFail($this->editingId);

        // 🔥 PRESERVE recharge info BEFORE delete
        $gameId = $record->game_id;
        $date = $record->date;
        $recharge = $record->recharge_points ?? 0;

        $record->delete();

        // 🔥 RE-ATTACH recharge into a "shadow record"
        GamePoint::updateOrCreate(
            [
                'game_id' => $gameId,
                'date' => $date,
            ],
            [
                'recharge_points' => $recharge,
            ]
        );

        $this->deleteModal = false;
        $this->resetInputs();
        $this->resetPage();
    }

    public function canDelete(): bool
    {
        return $this->currentUser()?->role === 'admin';
    }

    // ----------------------
    // RECHARGE POINTS
    // ----------------------

    // Open the recharge list modal
    public function openRechargeListModal()
    {
        $this->rechargeListModal = true;
        $this->editRechargeId = null;
        $this->editRechargeAmount = null;
    }

// Open edit recharge modal for a specific recharge
    public function editRecharge($id)
    {
        $recharge = GamePoint::findOrFail($id);

        $this->editRechargeId = $id;
        $this->editRechargeAmount = $recharge->recharge_points;

        $this->rechargeListModal = true; // Keep same modal
    }

// Save edited recharge
    public function saveRechargeEdit()
    {
        $this->validate([
            'editRechargeAmount' => 'required|numeric|min:0.01',
        ]);

        $user = $this->currentUser();
        $userType = get_class($user);

        $record = GamePoint::findOrFail($this->editRechargeId);

        // Update only recharge_points
        $record->recharge_points = $this->editRechargeAmount;

        // Recalculate total starting points
        $previous = GamePoint::where('game_id', $record->game_id)
            ->where('date', '<', $record->date)
            ->orderBy('date', 'desc')
            ->first();

        $previousPoints = $previous ? $previous->points : 0;
        $record->total_starting_points = $previousPoints + $record->recharge_points;

        // Recalculate used points if main points exist
        if ($record->points !== null && $record->points > 0) {
            $record->used_points = $record->total_starting_points - $record->points;
        }

        $record->updated_by_id = $user->id;
        $record->updated_by_type = $userType;
        $record->save();

        // Close only the edit form, keep modal open
        $this->editRechargeId = null;
        $this->editRechargeAmount = null;
    }

// Delete recharge


    public function confirmDeleteRecharge($id)
    {
        $this->deleteRechargeId = $id;
    }

    public function deleteRecharge()
    {
        if (!$this->deleteRechargeId) return;

        $record = GamePoint::findOrFail($this->deleteRechargeId);

        // Subtract the recharge amount
        $record->recharge_points = 0;

        // Recalculate total starting points
        $previous = GamePoint::where('game_id', $record->game_id)
            ->where('date', '<', $record->date)
            ->orderBy('date', 'desc')
            ->first();

        $previousPoints = $previous ? $previous->points : 0;
        $record->total_starting_points = $previousPoints + $record->recharge_points;

        // Recalculate used points if main points exist
        if ($record->points !== null && $record->points > 0) {
            $record->used_points = $record->total_starting_points - $record->points;
        }

        $record->updated_by_id = $this->currentUser()->id;
        $record->updated_by_type = get_class($this->currentUser());
        $record->save();

        $this->deleteRechargeId = null;
    }


    public function resetRecharge()
    {
        $this->rechargeGameId = null;
        $this->rechargeDate = now()->format('Y-m-d');
        $this->rechargeAmount = null;
    }

    public function openRechargeModal()
    {
        $this->resetRecharge();
        $this->rechargeModal = true;
    }

    public function saveRecharge()
    {
        $this->validate([
            'rechargeGameId' => 'required|exists:games,id',
            'rechargeDate' => 'required|date',
            'rechargeAmount' => 'required|numeric|min:0.01',
        ]);

        $user = $this->currentUser();
        $userType = get_class($user);

        $record = GamePoint::firstOrNew([
            'game_id' => $this->rechargeGameId,
            'date' => $this->rechargeDate,
        ]);

        if (!$record->exists) {
            $record->recharge_points = 0;
        }

        // Add recharge
        $record->recharge_points += $this->rechargeAmount;

        // Calculate total starting points (previous points + recharge)
        $previous = GamePoint::where('game_id', $this->rechargeGameId)
            ->where('date', '<', $this->rechargeDate)
            ->orderBy('date', 'desc')
            ->first();

        $previousPoints = $previous ? $previous->points : 0;
        $record->total_starting_points = $previousPoints + $record->recharge_points;

        // If main points already entered, update used points
        if ($record->points !== null && $record->points > 0) {
            $record->used_points = $record->total_starting_points - $record->points;
        }

        $record->updated_by_id = $user->id;
        $record->updated_by_type = $userType;
        $record->save();

        $this->rechargeModal = false;
        $this->resetRecharge();
        $this->resetPage();
    }




    // ----------------------
    // RENDER TABLE
    // ----------------------
    public function render()
    {
        $transactions = \App\Models\Transaction::with('game')
            ->when($this->searchGame, fn($q) => $q->where('game_id', $this->searchGame))
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->orderBy('created_at', 'asc')
            ->get();

        $grouped = $transactions->groupBy(function ($t) {
            return Carbon::parse(
                $t->transaction_date ?? $t->created_at
            )->format('Y-m-d');
        });

        $recordsByDate = [];
        $previousClosing = [];

        foreach ($grouped as $date => $txns) {

            $games = $txns->groupBy('game_id');

            $allGames = Game::all()->keyBy('id');
            $groupedGames = $txns->groupBy('game_id');

            foreach ($allGames as $gameId => $gameModel) {

                $gTxns = $groupedGames[$gameId] ?? collect();

                $cashinCredits = $gTxns
                    ->where('transaction_type', 'cashin')
                    ->sum('credits_used');

                $cashoutCredits = $gTxns
                    ->where('transaction_type', 'cashout')
                    ->sum('credits_used');

                $totalBonus = $gTxns->sum('bonus_added');

                // ⚠️ IMPORTANT: DO NOT CREATE ROW IF NO DATA
                $existingRow = GamePoint::where('game_id', $gameId)
                    ->where('date', $date)
                    ->first();

                $rechargePoints = $existingRow->recharge_points ?? 0;

                $starting = ($previousClosing[$gameId] ?? 0) + $rechargePoints;

                $netUsed = $cashinCredits - $cashoutCredits;

                // 🔥 FIX: if NO transaction AND NO manual points → DO NOTHING
                if (!$existingRow && $cashinCredits == 0 && $cashoutCredits == 0) {
                    $closing = $starting;
                    $displayUsed = 0;
                } else {

                    $calculatedClosing = $starting - $netUsed;

                    $closing = $existingRow && $existingRow->points !== null
                        ? $existingRow->points
                        : $calculatedClosing;

                    if ($existingRow && $existingRow->points !== null) {
                        $displayUsed = $starting - $existingRow->points;
                    } elseif ($cashinCredits > 0 || $cashoutCredits > 0) {
                        $displayUsed = $netUsed;
                    } else {
                        $displayUsed = 0;
                    }

                    // ✅ ONLY update if row exists (NO SPAM CREATION)
                    if ($existingRow) {
                        $existingRow->update([
                            'total_starting_points' => $starting,
                            'used_points' => ($cashinCredits > 0 || $cashoutCredits > 0)
                                ? $netUsed
                                : 0,
                        ]);
                    }
                }

                $recordsByDate[$date][] = (object)[
                    'id' => $existingRow->id ?? null,
                    'game_id' => $gameId,
                    'game' => $gameModel, // ✅ important fix
                    'points' => $closing,
                    'recharge_points' => $rechargePoints,
                    'total_starting_points' => $starting,
                    'used_points' => $displayUsed,
                    'bonus_added_points' => $totalBonus,
                    'created_by_name' => $existingRow->created_by_name ?? '-',
                    'updated_by_name' => $existingRow?->updated_by_name ?? '-',
                ];

                $previousClosing[$gameId] = $closing;
            }
        }

        $dateKeys = collect(array_keys($recordsByDate))
            ->sortDesc()
            ->values();
        $page = LengthAwarePaginator::resolveCurrentPage();

        $pagedDates = $dateKeys
            ->slice(($page - 1) * $this->perPage, $this->perPage)
            ->values()
            ->all();

        $paginator = new LengthAwarePaginator(
            $pagedDates,
            $dateKeys->count(),
            $this->perPage,
            $page,
            ['path' => request()->url()]
        );
        return view('livewire.game-points-table', [
            'dates' => $pagedDates,
            'recordsByDate' => $recordsByDate,
            'paginator' => $paginator,
            'games' => Game::orderBy('name')->get(),
            'hasAnyData' => count($recordsByDate) > 0,
        ]);
    }
}
