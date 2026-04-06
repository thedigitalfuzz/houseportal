<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\SubRechargeInformation;
use App\Models\Game;
use App\Models\Subdistributor;
use Illuminate\Support\Facades\Auth;

class SubRechargeInformationsTable extends Component
{
    use WithPagination;

    // Filters
    public $gameFilter = '';
    public $subFilter = '';
    public $dateFilter = '';

    public $subUsers = [];

    // Modal
    public $modalOpen = false;
    public $editingId;

    public $game_id;
    public $subdistributor_id;
    public $amount;
    public $date;

    public $deleteModal = false;
    public $confirmDeleteId;

    protected function currentUser()
    {
        return Auth::guard('web')->user() ?? Auth::guard('staff')->user();
    }

    public function canEdit(): bool
    {
        return $this->currentUser()?->role === 'admin';
    }

    public function updatingGameFilter()
    {
        $this->subFilter = '';
        $this->subUsers = [];

        if ($this->gameFilter) {
            $this->subUsers = Subdistributor::where('game_id', $this->gameFilter)
                ->where('status','active')
                ->orderBy('sub_username')
                ->get();
        }

        $this->resetPage();
    }

    public function updatingSubFilter() { $this->resetPage(); }
    public function updatingDateFilter() { $this->resetPage(); }

    // MODALS
    public function openAddModal()
    {
        $this->reset(['editingId','game_id','subdistributor_id','amount','date']);
        $this->subUsers = [];
        $this->modalOpen = true;
    }

    public function openEditModal($id)
    {
        $record = SubRechargeInformation::findOrFail($id);

        $this->editingId = $id;
        $this->game_id = $record->game_id;
        $this->subdistributor_id = $record->subdistributor_id;
        $this->amount = $record->amount;
        $this->date = $record->date->format('Y-m-d');

        $this->subUsers = Subdistributor::where('game_id', $this->game_id)
            ->where('status','active')
            ->orderBy('sub_username')
            ->get();

        $this->modalOpen = true;
    }

    public function save()
    {
        $validated = $this->validate([
            'game_id' => 'required|exists:games,id',
            'subdistributor_id' => 'required|exists:subdistributors,id',
            'amount' => 'required|numeric',
            'date' => 'required|date',
        ]);

        if ($this->editingId) {
            SubRechargeInformation::findOrFail($this->editingId)->update($validated);
        } else {
            SubRechargeInformation::create($validated);
        }

        $this->modalOpen = false;
        $this->reset(['editingId','game_id','subdistributor_id','amount','date']);
    }

    public function confirmDelete($id)
    {
        $this->confirmDeleteId = $id;
        $this->deleteModal = true;
    }

    public function delete()
    {
        SubRechargeInformation::findOrFail($this->confirmDeleteId)->delete();
        $this->deleteModal = false;
        $this->confirmDeleteId = null;
        $this->resetPage();
    }

    // dependent dropdown inside modal
    public function updatedGameId()
    {
        $this->subdistributor_id = null;

        if ($this->game_id) {
            $this->subUsers = Subdistributor::where('game_id', $this->game_id)
                ->where('status','active')
                ->orderBy('sub_username')
                ->get();
        } else {
            $this->subUsers = [];
        }
    }

    public function render()
    {
        $query = SubRechargeInformation::with(['game','subdistributor'])
            ->when($this->gameFilter, fn($q) => $q->where('game_id', $this->gameFilter))
            ->when($this->subFilter, fn($q) => $q->where('subdistributor_id', $this->subFilter))
            ->when($this->dateFilter, fn($q) => $q->whereDate('date',$this->dateFilter))
            ->orderBy('date','desc');

        $records = $query->paginate(15);

        $games = Game::orderBy('name')->get();

        return view('livewire.sub-recharge-informations-table', [
            'records' => $records,
            'games' => $games,
            'subUsers' => $this->subUsers,
        ]);
    }
}
