<?php
namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\PlayerAgent;

class PlayerAgentsTable extends Component
{
use WithPagination, WithFileUploads;

public $searchInput = '';
public $search = '';
public $perPage = 15;

public $modalOpen = false;
public $editingAgentId;
public $player_agent_name;
public $facebook_profile;
public $facebook_password;
public $email_id;
public $two_way_verification_code;
public $photo;
public $existingPhoto;

public $confirmDeleteId = null;
public $deleteModalOpen = false;

public function mount()
{
if (auth()->user()?->role !== 'admin') {
abort(403, 'Unauthorized access');
}
}

public function updatingSearchInput()
{
$this->resetPage();
}

public function applySearch()
{
$this->search = $this->searchInput;
$this->resetPage();
}

public function openAddModal()
{
$this->reset([
'editingAgentId', 'player_agent_name', 'facebook_profile', 'facebook_password', 'email_id', 'two_way_verification_code', 'photo', 'existingPhoto'
]);

$this->modalOpen = true;
}

public function openEditModal($id)
{
$agent = PlayerAgent::findOrFail($id);

$this->editingAgentId = $id;
$this->player_agent_name = $agent->player_agent_name;
$this->facebook_profile = $agent->facebook_profile;
$this->facebook_password = $agent->facebook_password;
$this->email_id = $agent->email_id;
$this->two_way_verification_code = $agent->two_way_verification_code;
$this->existingPhoto = $agent->photo;

$this->modalOpen = true;
}

public function savePlayerAgent()
{
$validated = $this->validate([
'player_agent_name' => 'required|string|max:255',
'facebook_profile' => 'nullable|string|max:255',
'facebook_password' => 'nullable|string|max:255',
    'email_id' => 'nullable|string|max:255',
'two_way_verification_code' => 'nullable|string|max:255',
'photo' => 'nullable|image|max:2048',
]);

if ($this->photo) {
$path = $this->photo->store('agent_photos', 'public');
$validated['photo'] = $path;
}

if ($this->editingAgentId) {
$agent = PlayerAgent::findOrFail($this->editingAgentId);
$agent->update(array_merge($validated, [
'photo' => $validated['photo'] ?? $agent->photo
]));
} else {
PlayerAgent::create($validated);
}

$this->modalOpen = false;
$this->reset([
'editingAgentId', 'player_agent_name', 'facebook_profile', 'facebook_password', 'email_id', 'two_way_verification_code', 'photo', 'existingPhoto'
]);
}

public function confirmDelete($id)
{
$this->confirmDeleteId = $id;
$this->deleteModalOpen = true;
}

public function deletePlayerAgent()
{
PlayerAgent::findOrFail($this->confirmDeleteId)->delete();
$this->deleteModalOpen = false;
$this->confirmDeleteId = null;
$this->resetPage();
}

public function render()
{
$query = PlayerAgent::query()
->when($this->search, fn($q) =>
$q->where('player_agent_name', 'like', '%'.$this->search.'%')
->orWhere('facebook_profile', 'like', '%'.$this->search.'%')
);

$agents = $query->orderBy('id','asc')->paginate($this->perPage);

return view('livewire.player-agents-table', [
'agents' => $agents,
]);
}
}
