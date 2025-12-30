<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\WalletDetail;

class WalletDetails extends Component
{
    public $walletDetails;

    public $deleteModal = false;

    public $editId;
    public $agent;
    public $wallet_name;
    public $wallet_remarks;

    public $deleteId = null;



    protected $rules = [
        'agent' => 'required|string|max:255',
        'wallet_name' => 'required|string|max:255',
        'wallet_remarks' => 'nullable|string|max:255',
    ];

    public function mount()
    {
        // Hard stop if not admin (extra safety)
        abort_unless(auth()->user()?->role === 'admin', 403);

        $this->loadData();
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



    public function render()
    {
        return view('livewire.wallet-details');
    }
}
