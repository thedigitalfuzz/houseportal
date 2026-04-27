<?php

namespace App\Livewire;

use App\Models\GamePoint;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Services\GamePointService;
use App\Models\Game;
use Livewire\WithPagination;
use Illuminate\Pagination\LengthAwarePaginator;

class NewGamePointsTable extends Component
{
    use WithPagination;
    public $searchGame;
    public $dateFrom;
    public $dateTo;
    public $editModal = false;
    public $editingId;
    public $editPoints;
    public $editDate;
    public $editGameId;
    public $deleteModal = false;
    public $rechargeModal = false;
    public $rechargeGameId;
    public $rechargeDate;
    public $rechargeAmount;
    public $rechargeListModal = false;
    public $editRechargeId;
    public $editRechargeAmount;
    public $deleteRechargeId;

    protected GamePointService $service;

    public function boot(GamePointService $service)
    {
        $this->service = $service;
    }

    public function mount()
    {
        $this->resetInputs();
        $this->resetRecharge();
    }
    protected function currentUser()
    {
        return Auth::guard('web')->user() ?? Auth::guard('staff')->user();
    }
    public function resetInputs()
    {
        $this->editingId = null;
        $this->editDate = now()->format('Y-m-d');
        $this->editGameId = null;
        $this->editPoints = null;
    }
    public function canDelete(): bool
    {
        return $this->currentUser()?->role === 'admin';
    }

    // ----------------------
    // RECHARGE POINTS
    // ----------------------
    public function openRechargeModal()
    {
        $this->resetRecharge();
        $this->rechargeModal = true;
    }
    public function openAddModal()
    {
        $this->resetInputs();
        $this->editModal = true;
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

      //  $record->updated_by_id = $user->id;
       // $record->updated_by_type = $userType;
        $record->save();

        $this->rechargeModal = false;
        $this->resetRecharge();
       // $this->resetPage();
    }


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

       // $record->updated_by_id = $this->currentUser()->id;
      //  $record->updated_by_type = get_class($this->currentUser());
        $record->save();

        $this->deleteRechargeId = null;
    }
    public function resetRecharge()
    {
        $this->rechargeGameId = null;
        $this->rechargeDate = now()->format('Y-m-d');
        $this->rechargeAmount = null;
    }

    public function editRecord($id)
    {
        $record = \App\Models\GamePoint::findOrFail($id);

        $this->editingId = $id;
        $this->editDate = $record->date->format('Y-m-d');
        $this->editGameId = $record->game_id;
        $this->editPoints = $record->points;

        $this->editModal = true;
    }

    public function saveRecord()
    {
        $record = \App\Models\GamePoint::findOrFail($this->editingId);

        $user = auth()->user();

        $record->points = $this->editPoints;

// recalc used_points
        $record->used_points = $record->total_starting_points - $this->editPoints;

        $record->updated_by_id = $user->id;
        $record->updated_by_type = get_class($user);

        $record->save();

        $this->editModal = false;
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
     //   $this->resetPage();
    }


    public function render()
    {
        $data = $this->service->getData([
            'game_id' => $this->searchGame,
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
        ]);

        $dates = collect($data)->keys()->sortDesc()->values();

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;

        $currentDates = $dates->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $paginatedDates = new LengthAwarePaginator(
            $currentDates,
            $dates->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        $filteredData = collect($data)
            ->only($currentDates->toArray())
            ->sortKeysDesc()
            ->toArray();

        return view('livewire.new-game-points-table', [
            'recordsByDate' => $filteredData,
            'pagination' => $paginatedDates,
            'games' => Game::all(),
        ]);
    }
}
