<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\WalletDetail;
use App\Models\Staff;
use Illuminate\Support\Facades\Auth;
use App\Helpers\NotificationHelper;

class WalletDetails extends Component
{
    public $walletDetails;

    public $deleteModal = false;


    public $editId;
    public $agent;
    public $wallet_name;
    public $wallet_remarks;
    public $agents = [];
    public $walletNames = [];
    public $walletRemarks = [];
    public $walletAgents = [];
    public $detail_agent;
    public $detail_wallet_name;
    public $detail_wallet_remarks;

    public $deleteId = null;
    public $status;
    public $disabled = false;
    public $status_date;
    public $addWalletDetailModal = false;
public $editModal = false;
    public $searchTerm = ''; // For search

    protected $rules = [
        'agent' => 'required|string|max:255',
        'wallet_name' => 'required|string|max:255',
        'wallet_remarks' => 'nullable|string|max:255',
    ];

    protected function currentUser()
    {
        return Auth::guard('web')->user() ?? Auth::guard('staff')->user();
    }
    public function mount()
    {
        // Hard stop if not admin (extra safety)
       // abort_unless(auth()->user()?->role === 'admin', 403);
        $this->agents = WalletDetail::select('agent')
            ->distinct()
            ->orderBy('agent')
            ->pluck('agent')
            ->toArray();

        $this->walletAgents = WalletDetail::select('agent')
            ->distinct()
            ->orderBy('agent')
            ->pluck('agent')
            ->toArray();
        $this->loadData();
    }

    public function canDelete(): bool
    {
        return $this->currentUser()?->role === 'admin';
    }

    public function loadData()
    {
        $query = WalletDetail::query();

        // Search filter
        if ($this->searchTerm) {
            $term = '%' . $this->searchTerm . '%';
            $query->where(function ($q) use ($term) {
                $q->where('agent', 'like', $term)
                    ->orWhere('wallet_name', 'like', $term)
                    ->orWhere('wallet_remarks', 'like', $term);
            });
        }

        $this->walletDetails = $query->orderBy('agent')
            ->orderBy('wallet_name')
            ->get();
    }
    public function saveWalletDetail()
    {
        $this->resetErrorBag();

        $this->validate([
            'detail_agent' => 'required|string|max:255',
            'detail_wallet_name' => 'required|string|max:255',
            'detail_wallet_remarks' => 'nullable|string|max:255',
        ]);

        $exists = WalletDetail::where('agent', $this->detail_agent)
            ->where('wallet_name', $this->detail_wallet_name)
            ->where(function ($q) {
                if ($this->detail_wallet_remarks === null || $this->detail_wallet_remarks === '') {
                    $q->whereNull('wallet_remarks');
                } else {
                    $q->where('wallet_remarks', $this->detail_wallet_remarks);
                }
            })
            ->exists();

        if ($exists) {
            $this->addError(
                'detail_wallet_name',
                'The wallet you are trying to add already exists.'
            );
            return;
        }

        $user = $this->currentUser();

        WalletDetail::create([
            'agent' => $this->detail_agent,
            'wallet_name' => $this->detail_wallet_name,
            'wallet_remarks' => $this->detail_wallet_remarks,

            'created_by_id' => $user->id,
            'created_by_type' => $user instanceof \App\Models\User
                ? 'App\Models\User'
                : 'App\Models\Staff',
        ]);

        $this->agents = WalletDetail::select('agent')
            ->distinct()
            ->orderBy('agent')
            ->pluck('agent')
            ->toArray();

// After creating the wallet
        $this->createNotification(
            'New Wallet Alert',
            "{$this->detail_wallet_name} - {$this->detail_wallet_remarks} has been added on " . now()->format('Y-m-d'),
            $this->getRedirectPath()
        );
        // Reload table immediately
        $this->loadData();

        // Close modal
        $this->addWalletDetailModal = false;



    }

    public function openAddWalletDetailModal()
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->reset([
            'detail_agent',
            'detail_wallet_name',
            'detail_wallet_remarks'
        ]);

        $this->addWalletDetailModal = true;
    }

    /* ---------------- DEPENDENT DROPDOWNS ---------------- */
    public function updatedAgent()
    {
        $this->wallet_name = null;
        $this->wallet_remarks = null;

        if (!$this->agent) {
            $this->walletNames = [];
            $this->walletRemarks = [];
            return;
        }

        $this->walletNames = WalletDetail::where('agent', $this->agent)
            ->select('wallet_name')
            ->distinct()
            ->orderBy('wallet_name')
            ->pluck('wallet_name')
            ->toArray();

        $this->walletRemarks = [];
    }

    public function updatedWalletName()
    {
        $this->wallet_remarks = null;

        if (!$this->agent || !$this->wallet_name) {
            $this->walletRemarks = [];
            return;
        }

        $this->walletRemarks = WalletDetail::where('agent', $this->agent)
            ->where('wallet_name', $this->wallet_name)
            ->orderBy('wallet_remarks')
            ->pluck('wallet_remarks')
            ->toArray();
    }

    public function updatedWalletAgent()
    {
        $this->wallet_name_filter = null;
        $this->wallet_remarks_filter = null;

        if (!$this->wallet_agent) {
            $this->walletNamesFilter = [];
            $this->walletRemarksFilter = [];
            return;
        }

        $this->walletNamesFilter = WalletDetail::where('agent', $this->wallet_agent)
            ->select('wallet_name')
            ->distinct()
            ->orderBy('wallet_name')
            ->pluck('wallet_name')
            ->toArray();
    }

    public function updatedWalletNameFilter()
    {
        $this->wallet_remarks_filter = null;

        if (!$this->wallet_agent || !$this->wallet_name_filter) {
            $this->walletRemarksFilter = [];
            return;
        }

        $this->walletRemarksFilter = WalletDetail::where('agent', $this->wallet_agent)
            ->where('wallet_name', $this->wallet_name_filter)
            ->orderBy('wallet_remarks')
            ->pluck('wallet_remarks')
            ->toArray();
    }
    public function confirmDelete($id)
    {
        $this->deleteId = $id;
        $this->deleteModal = true;
    }

    public function closeDeleteModal()
    {
        $this->deleteModal = false;
        $this->deleteId = null;
    }

    public function deleteConfirmed()
    {
        WalletDetail::findOrFail($this->deleteId)->delete();

        $this->closeDeleteModal();
        $this->loadData();
    }

    public function openEditModal($id)
    {
        $wd = WalletDetail::findOrFail($id);

        $this->editId = $wd->id;
        $this->agent = $wd->agent;
        $this->wallet_name = $wd->wallet_name;
        $this->wallet_remarks = $wd->wallet_remarks;

        // Status radio
        $this->status = $wd->status;

        // Disabled date input: if disabled, show actual date; else null
        $this->status_date = $wd->status === 'disabled'
            ? $wd->status_date->format('Y-m-d')
            : now()->format('Y-m-d');

        $this->editModal = true;
    }

// Trigger immediately when status changes
    public function updatedStatus($value)
    {
        if ($value === 'disabled' && !$this->status_date) {
            $this->status_date = now()->format('Y-m-d');
        }
        // If switched back to active, clear date
        if ($value === 'active') {
            $this->status_date = null;
        }
    }

    public function updateStatus()
    {
        $wd = WalletDetail::findOrFail($this->editId);

        $wd->status = $this->status;

        // Save date only if disabled, else null
        $wd->status_date = $this->status === 'disabled'
            ? $this->status_date
            : null;

        $user = $this->currentUser();

        $wd->updated_by_id = $user->id;
        $wd->updated_by_type = $user instanceof \App\Models\User
            ? 'App\Models\User'
            : 'App\Models\Staff';

        $wd->save();

        // ✅ CREATE NOTIFICATION HERE
        if ($this->status === 'active') {
            $this->createNotification(
                'Wallet Active',
                "{$wd->wallet_name}-{$wd->wallet_remarks} is active from " . now()->format('Y-m-d'),
                $this->getRedirectPath()
            );
        } else {
            $remarks = $wd->wallet_remarks ? " ({$wd->wallet_remarks})" : '';

            $this->createNotification(
                'Wallet Disabled',
                "{$wd->wallet_name}-{$wd->wallet_remarks} has been disabled on " . now()->format('Y-m-d'),
                $this->getRedirectPath()
            );
        }

        $this->editModal = false;
        $this->loadData();
    }

    protected function createNotification($type, $message , $redirect = null)
    {
        $users = $this->allUsers();

        NotificationHelper::send($users, $type, $message, $redirect);

        // 🔥 refresh notification bell for all live components
        $this->dispatch('refreshNotifications');
    }
    protected function allUsers()
    {
        $admins = \App\Models\User::all();
        $staffs = \App\Models\Staff::all();

        return $admins->concat($staffs);
    }
    protected function getRedirectPath()
    {
        $user = $this->currentUser();

        if ($user instanceof \App\Models\Staff) {
            if ($user->role === 'support_agent') {
                return '/transactions';
            }
        }

        // Admin + Wallet Manager
        return '/wallet-details';
    }
    public function render()
    {
        return view('livewire.wallet-details');
    }
}
