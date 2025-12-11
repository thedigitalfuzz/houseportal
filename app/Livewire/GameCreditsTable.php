<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\GameCredit;
use App\Models\Game;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;

class GameCreditsTable extends Component
{
    use WithPagination;

    public $searchStoreInput = '';
    public $filterDateInput = null;

    public $game_id = null;
    public $searchStore = '';
    public $filterDate = null;

    public $addModal = false;
    public $editModal = false;

    public $editingId;
    public $store_name;
    public $store_balance;
    public $subdistributor_balance;
    public $date;

    public $confirmDeleteId = null;
    public $deleteModal = false;

    protected $listeners = [
        'gameCreditCreated' => '$refresh',
        'gameCreditUpdated' => '$refresh'
    ];

    protected function currentUser()
    {
        return Auth::guard('web')->user() ?? Auth::guard('staff')->user();
    }

    public function updatingSearchStoreInput() { $this->resetPage(); }
    public function updatingFilterDateInput() { $this->resetPage(); }
    public function updatingGameId() { $this->resetPage(); }

    public function applySearch()
    {
        $this->searchStore = $this->searchStoreInput;
        $this->filterDate = $this->filterDateInput;
        $this->resetPage();
    }
    public function canEdit(): bool
    {
        return $this->currentUser()?->role === 'admin';
    }

    public function canDelete(): bool
    {
        return $this->currentUser()?->role === 'admin';
    }
    public function openAddModal()
    {
        $this->reset(['editingId','game_id','subdistributor_balance','store_name','store_balance','date']);
        $this->addModal = true;
    }

    public function openEditModal($id)
    {
        $record = GameCredit::findOrFail($id);
        $this->editingId = $id;
        $this->game_id = $record->game_id;
        $this->subdistributor_balance = $record->subdistributor_balance;
        $this->store_name = $record->store_name;
        $this->store_balance = $record->store_balance;
        $this->date = $record->date->format('Y-m-d');
        $this->editModal = true;
    }

    public function saveRecord()
    {
        $validated = $this->validate([
            'game_id' => 'required|exists:games,id',
            'subdistributor_balance' => 'required|numeric',
            'store_name' => 'required|string|max:255',
            'store_balance' => 'required|numeric',
            'date' => 'required|date',
        ]);

        $user = $this->currentUser();
        $creatorType = $user instanceof \App\Models\User ? 'App\Models\User' : 'App\Models\Staff';

        if ($this->editingId) {
            $record = GameCredit::findOrFail($this->editingId);
            $record->update(array_merge($validated, [
                'updated_by_id' => $user->id,
                'updated_by_type' => $creatorType,
            ]));
            $this->dispatch('gameCreditUpdated');
            $this->editModal = false;
        } else {
            GameCredit::create(array_merge($validated, [
                'created_by_id' => $user->id,
                'created_by_type' => $creatorType,
            ]));
            $this->dispatch('gameCreditCreated');
            $this->addModal = false;
        }

        $this->reset(['editingId','game_id','subdistributor_balance','store_name','store_balance','date']);
    }

    public function confirmDelete($id)
    {
        $this->confirmDeleteId = $id;
        $this->deleteModal = true;
    }

    public function deleteRecord()
    {
        GameCredit::findOrFail($this->confirmDeleteId)->delete();
        $this->deleteModal = false;
        $this->confirmDeleteId = null;
        $this->resetPage();
    }

    public function render()
    {
        $user = $this->currentUser();

        $query = GameCredit::with(['game', 'createdBy', 'updatedBy'])
            ->when($this->game_id, fn($q) => $q->where('game_id', $this->game_id))
            ->when($this->searchStore, fn($q) => $q->where('store_name','like','%'.$this->searchStore.'%'))
            ->when($this->filterDate, fn($q) => $q->whereDate('date',$this->filterDate))
            ->orderBy('date','desc');

        $dates = $query->select('date')->distinct()->pluck('date')->sortDesc();

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 2;
        $currentDates = $dates->slice(($currentPage-1)*$perPage,$perPage)->values();
        $paginatedDates = new LengthAwarePaginator(
            $currentDates,
            $dates->count(),
            $perPage,
            $currentPage,
            ['path'=>request()->url(),'query'=>request()->query()]
        );

        // --- IMPORTANT FIX ---
        // Fetch credits for current dates but APPLY THE SAME FILTERS so only matching rows show.
        $creditsByDate = GameCredit::with(['game', 'createdBy', 'updatedBy'])
            ->when($this->game_id, fn($q) => $q->where('game_id', $this->game_id))
            ->when($this->searchStore, fn($q) => $q->where('store_name','like','%'.$this->searchStore.'%'))
            ->when($this->filterDate, fn($q) => $q->whereDate('date',$this->filterDate))
            ->whereIn('date', $currentDates)
            ->orderBy('date','desc')
            ->get()
            ->groupBy(fn($g) => $g->date->format('Y-m-d'));

        $games = Game::orderBy('name')->get();

        return view('livewire.game-credits-table', [
            'creditsByDate' => $creditsByDate,
            'credits' => $paginatedDates,
            'currentUser' => $user,
            'games' => $games,
        ]);
    }

}
