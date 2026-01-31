<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\WalletDetail;
use App\Models\Staff;
use Illuminate\Support\Facades\Auth;

class WalletDetails extends Component
{
    public $walletDetails;

    public $deleteModal = false;


    public $editId;
    public $agent;
    public $wallet_name;
    public $wallet_remarks;

    public $deleteId = null;
    public $status;
    public $disabled = false;
    public $status_date;
public $editModal = false;

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

        $this->loadData();
    }

    public function canDelete(): bool
    {
        return $this->currentUser()?->role === 'admin';
    }

    public function loadData()
    {
        $this->walletDetails = WalletDetail::orderBy('agent')
            ->orderBy('wallet_name')
            ->get();
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

        $wd->save();

        $this->editModal = false;
        $this->loadData();
    }


    public function render()
    {
        return view('livewire.wallet-details');
    }
}
