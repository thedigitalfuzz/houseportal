<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Player;
use App\Models\PlayerAgent;
use Illuminate\Support\Facades\Auth;
use App\Helpers\NotificationHelper;

class PlayersTable extends Component
{
    use WithPagination;

    public $searchInput = '';
    public $search = '';
    public $perPage = 15;

    public $editModal = false;
    public $addModal = false;

    public $editingPlayerId;
    public $player_name;
    public $username;
    public $facebook_profile;
    public $phone;

    public $agent_id; // for add/edit
    public $filter_agent_id; // for admin filter dropdown
    public $assigned_staff;
    public $allAgents = [];

    public $confirmDeleteId = null;
    public $deleteModal = false;
    public $duplicateUsernameError = null;

    protected $listeners = [
        'playerCreated' => '$refresh',
        'playerUpdated' => '$refresh'
    ];

    public function updatingSearchInput()
    {
        $this->resetPage();
    }

    public function applySearch()
    {
        $this->search = $this->searchInput;
        $this->resetPage();
    }

    protected function currentUser()
    {
        return Auth::guard('web')->user() ?? Auth::guard('staff')->user();
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
        $this->reset(['editingPlayerId','username','player_name','facebook_profile','phone','agent_id']);
        $this->duplicateUsernameError = null;

        // Only admin can assign agents
        $this->allAgents = PlayerAgent::all(); // Get all player agents
        $this->agent_id = null;

        $this->addModal = true;
    }

    public function openEditModal($id)
    {
        $this->duplicateUsernameError = null;
        $player = Player::with('assignedAgent')->findOrFail($id);

        $this->editingPlayerId = $id;
        $this->player_name = $player->player_name ?? '';
        $this->username = $player->username;
        $this->facebook_profile = $player->facebook_profile ?? '';
        $this->phone = $player->phone ?? '';
        $this->agent_id = $player->agent_id ? (int) $player->agent_id : null;

        $this->allAgents = PlayerAgent::all();

        $this->editModal = true;
    }

    public function savePlayer()
    {
        $user = $this->currentUser();
        if (Auth::guard('staff')->check()) {
            $userType = \App\Models\Staff::class;
        } else {
            $userType = \App\Models\User::class;
        }

        $this->duplicateUsernameError = null;

        $rules = [
            'username' => 'required|string|max:255|unique:players,username,' . $this->editingPlayerId,
            'player_name' => 'required|string|max:255',
            'facebook_profile' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'agent_id' => 'nullable|exists:player_agents,id', // agent_id will reference the `id` in `player_agents` table
            'assigned_staff' => 'nullable|string|max:255', // Assigned staff will store the player_agent_name from PlayerAgent
        ];

        try {
            $validated = $this->validate($rules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($e->validator->errors()->has('username')) {
                $this->duplicateUsernameError = 'The player "' . $this->username . '" already exists.';
                return;
            }
            throw $e;
        }

        // Retrieve player agent name
        $playerAgentName = PlayerAgent::find($this->agent_id)?->player_agent_name;

        // Update or create player based on editingPlayerId
        if ($this->editingPlayerId) {
            $player = Player::findOrFail($this->editingPlayerId);
                $player->update(array_merge($validated, [
                    'staff_id' => $this->agent_id, // Store agent_id in `staff_id` column
                    'assigned_staff' => $playerAgentName, // Store player_agent_name in `assigned_staff`
                    'username' => $this->username,
                    'updated_by_id' => $user->id,
                    'updated_by_type' => $userType,
                ]));

            // 🔔 Send notification for admins & wallet managers
            $staffName = $user instanceof \App\Models\Staff ? $user->staff_name : $user->name;
            $this->sendPlayerUpdateNotification(
                "{$player->username}, {$player->player_name} has been updated by {$staffName}"
            );

            $this->dispatch('playerUpdated');
            $this->editModal = false;
        } else {
            $player= Player::create(array_merge($validated, [
                'staff_id' => $this->agent_id, // Store agent_id in `staff_id` column
                'assigned_staff' => $playerAgentName, // Store player_agent_name in `assigned_staff`
                'username' => $this->username,
                'created_by_id' => $user->id,
                'created_by_type' => $userType,
            ]));

            $this->dispatch('playerCreated');
            $this->addModal = false;

            // 🔔 Send notification for admins & wallet managers
            $staffName = $user instanceof \App\Models\Staff ? $user->staff_name : $user->name;
            $this->sendPlayerNotification(
                "{$player->username}, {$player->player_name} has been added by {$staffName}"
            );
        }

        // Reset values after save
        $this->reset([
            'editingPlayerId',
            'username',
            'player_name',
            'facebook_profile',
            'phone',
            'agent_id',
            'duplicateUsernameError'
        ]);
    }

    public function confirmDelete($id)
    {
        $this->confirmDeleteId = $id;
        $this->deleteModal = true;
    }

    public function deletePlayer()
    {
        Player::findOrFail($this->confirmDeleteId)->delete();
        $this->deleteModal = false;
        $this->confirmDeleteId = null;
        $this->resetPage();
    }

    protected function sendPlayerNotification($message)
    {
        // Only admin + wallet managers
        $users = \App\Models\User::whereIn('role', ['admin', 'wallet_manager'])->get();
        $staff = \App\Models\Staff::where('role', 'wallet_manager')->get();
        if ($users->isEmpty()) return;
        $allRecipients = $users->merge($staff);

        NotificationHelper::send($allRecipients, 'New Player Added', $message, '/players');



        // Refresh notification bell
        $this->dispatch('refreshNotifications');
    }

    protected function sendPlayerUpdateNotification($message)
    {
        // Get all admins and wallet managers
        $users = \App\Models\User::whereIn('role', ['admin','wallet_manager'])->get();
        $staff = \App\Models\Staff::where('role', 'wallet_manager')->get();
        $allRecipients = $users->merge($staff);

        if ($allRecipients->isEmpty()) return;

        NotificationHelper::send($allRecipients, 'Player Updated', $message, '/players');

        // Refresh notification bell
        $this->dispatch('refreshNotifications');
    }
    public function render()
    {
        $user = $this->currentUser();

        $query = Player::with('assignedAgent', 'createdBy', 'updatedBy')
            ->when($this->filter_agent_id, fn($q) => $q->where('staff_id', $this->filter_agent_id)) // Filter by `staff_id`
            ->when($this->search, fn($q) => $q->where(function($p) {
                $p->where('username', 'like', '%'.$this->search.'%')
                    ->orWhere('player_name', 'like', '%'.$this->search.'%');
            }));

        $players = $query->orderBy('id','asc')->paginate($this->perPage);

        return view('livewire.players-table', [
            'players' => $players,
            'currentUser' => $user,
        ]);
    }
}
