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

    protected $listeners = ['refreshGamePoints' => '$refresh'];

    public function mount()
    {
        $this->resetInputs();
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
            // UPDATE
            $record = GamePoint::findOrFail($this->editingId);

            $record->update([
                'game_id' => $this->editGameId,
                'points' => $this->editPoints,
                'date' => $this->editDate,
                'updated_by_id' => $user->id,
                'updated_by_type' => $userType,
            ]);
        } else {
            // CREATE
            GamePoint::create([
                'game_id' => $this->editGameId,
                'points' => $this->editPoints,
                'date' => $this->editDate,
                'created_by_id' => $user->id,
                'created_by_type' => get_class($user)
            ]);
        }

        $this->editModal = false;
        $this->resetInputs();
        $this->resetPage();
    }

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

        // Group records by date string
        $grouped = $records->groupBy(fn ($r) =>
        Carbon::parse($r->date)->format('Y-m-d')
        );

        // Paginate DATE KEYS (not models, not paginator object)
        $dateKeys = $grouped->keys()->values();

        $page = LengthAwarePaginator::resolveCurrentPage();
        $pagedDates = $dateKeys
            ->slice(($page - 1) * $this->perPage, $this->perPage)
            ->values()
            ->all(); // ← ARRAY (IMPORTANT)

        $paginator = new LengthAwarePaginator(
            $pagedDates,
            $dateKeys->count(),
            $this->perPage,
            $page,
            ['path' => request()->url()]
        );

        return view('livewire.game-points-table', [
            'dates' => $pagedDates,          // ← ARRAY ONLY
            'recordsByDate' => $grouped,     // ← COLLECTION
            'paginator' => $paginator,
            'games' => Game::orderBy('name')->get(),
            'hasAnyData' => $records->count() > 0,
        ]);
    }
}
