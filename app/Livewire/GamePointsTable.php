<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\GamePoint;
use App\Models\Game;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

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

    public function openAddModal()
    {
        $this->resetInputs();
        $this->editModal = true;
    }

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
            $record = GamePoint::firstOrCreate(
                [
                    'game_id' => $this->editGameId,
                    'date' => $this->editDate,
                ],
                [
                    'points' => 0,
                    'recharge_points' => 0,
                    'total_starting_points' => 0,
                    'created_by_id' => $user->id,
                    'created_by_type' => $userType,
                ]
            );

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
        GamePoint::findOrFail($this->editingId)->delete();
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

        // Get existing record for this game + date (if any)
        $record = GamePoint::where('game_id', $this->rechargeGameId)
            ->where('date', $this->rechargeDate)
            ->first();

        if (!$record) {
            // CREATE new row if it doesn't exist
            $record = GamePoint::create([
                'game_id' => $this->rechargeGameId,
                'date' => $this->rechargeDate,
                'points' => 0,
                'recharge_points' => 0,
                'total_starting_points' => 0,
                'created_by_id' => $user->id,
                'created_by_type' => $userType,
            ]);
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
        $query = GamePoint::with(['game', 'createdBy', 'updatedBy'])
            ->orderBy('date', 'desc');

        if ($this->searchGame) {
            $query->where('game_id', $this->searchGame);
        }

        if ($this->dateFrom) {
            $query->whereDate('date', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('date', '<=', $this->dateTo);
        }

        $records = $query->get();

        // --- FILTER: show only rows where main points are entered ---
        $recordsWithPoints = $records->filter(fn($r) => $r->points > 0);

        // Group records by date
        $grouped = $recordsWithPoints->groupBy(fn($r) =>
        Carbon::parse($r->date)->format('Y-m-d')
        );

        // Paginate DATE KEYS
        $dateKeys = $grouped->keys()->values();
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
            'recordsByDate' => $grouped,
            'paginator' => $paginator,
            'games' => Game::orderBy('name')->get(),
            'hasAnyData' => $recordsWithPoints->count() > 0,
        ]);
    }
}
