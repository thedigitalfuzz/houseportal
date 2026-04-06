<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Subdistributor;
use App\Models\Game;
use Illuminate\Support\Facades\Auth;

class SubdistributorsTable extends Component
{
    use WithPagination;

    public $games;
    public $gameFilter = '';
    public $subFilter = '';
    public $subUsers = []; // <-- for dependent dropdown

    public $modalOpen = false;
    public $editingId;

    public $game_id;
    public $sub_username;
    public $status = 'active';

    protected $listeners = ['subAdded' => '$refresh', 'subUpdated' => '$refresh'];

    protected function currentUser()
    {
        return Auth::guard('web')->user() ?? Auth::guard('staff')->user();
    }

    public function canEdit(): bool
    {
        return $this->currentUser()?->role === 'admin';
    }

    // عندما يتغير فلتر اللعبة
    public function updatedGameFilter()
    {
        $this->subFilter = ''; // reset sub filter

        if ($this->gameFilter) {
            $this->subUsers = Subdistributor::where('game_id', $this->gameFilter)
                ->orderBy('sub_username')
                ->get();
        } else {
            $this->subUsers = [];
        }

        $this->resetPage();
    }

    public function updatingSubFilter()
    {
        $this->resetPage();
    }

    public function openAddModal()
    {
        $this->reset(['editingId','game_id','sub_username','status']);
        $this->status = 'active';
        $this->modalOpen = true;
    }

    public function openEditModal($id)
    {
        $sub = Subdistributor::findOrFail($id);
        $this->editingId = $id;
        $this->game_id = $sub->game_id;
        $this->sub_username = $sub->sub_username;
        $this->status = $sub->status;
        $this->modalOpen = true;
    }

    public function saveSub()
    {
        $validated = $this->validate([
            'game_id' => 'required|exists:games,id',
            'sub_username' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        if ($this->editingId) {
            Subdistributor::findOrFail($this->editingId)->update($validated);
            $this->dispatch('subUpdated');
        } else {
            Subdistributor::create($validated);
            $this->dispatch('subAdded');
        }

        $this->modalOpen = false;
        $this->reset(['editingId','game_id','sub_username','status']);
    }

    public function render()
    {
        $query = Subdistributor::with('game')
            ->when($this->gameFilter, fn($q) => $q->where('game_id', $this->gameFilter))
            ->when($this->subFilter, fn($q) => $q->where('id', $this->subFilter))
            ->join('games', 'subdistributors.game_id', '=', 'games.id')
            ->orderBy('games.name', 'asc')
            ->orderBy('subdistributors.sub_username', 'asc')
            ->select('subdistributors.*');

        $subs = $query->paginate(15);

        $this->games = Game::orderBy('name')->get();

        return view('livewire.subdistributors-table', [
            'subs' => $subs,
            'games' => $this->games,
            'subUsers' => $this->subUsers,
        ]);
    }
}
