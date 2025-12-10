<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Wallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;

class WalletsTable extends Component
{
    use WithPagination;

    // Input fields for typing before hitting Search
    public $searchAgentInput = '';
    public $searchWalletInput = '';
    public $searchRemarksInput = '';
    public $filterDateInput = null;

    // Applied search filters
    public $searchAgent = '';
    public $searchWallet = '';
    public $searchRemarks = '';
    public $filterDate = null;

    public $addModal = false;
    public $editModal = false;

    public $editingWalletId;
    public $agent;
    public $wallet_name;
    public $wallet_remarks;
    public $current_balance;
    public $date;

    public $confirmDeleteId = null;
    public $deleteModal = false;

    protected $listeners = [
        'walletCreated' => '$refresh',
        'walletUpdated' => '$refresh'
    ];

    protected function currentUser()
    {
        return Auth::guard('web')->user() ?? Auth::guard('staff')->user();
    }

    public function updatingSearchAgentInput() { $this->resetPage(); }
    public function updatingSearchWalletInput() { $this->resetPage(); }
    public function updatingSearchRemarksInput() { $this->resetPage(); }
    public function updatingFilterDateInput() { $this->resetPage(); }

    public function applySearch()
    {
        $this->searchAgent = $this->searchAgentInput;
        $this->searchWallet = $this->searchWalletInput;
        $this->searchRemarks = $this->searchRemarksInput;
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
        $this->reset(['editingWalletId', 'agent', 'wallet_name', 'wallet_remarks', 'current_balance', 'date']);
        $this->addModal = true;
    }

    public function openEditModal($id)
    {
        $wallet = Wallet::findOrFail($id);

        $this->editingWalletId = $id;
        $this->agent = $wallet->agent;
        $this->wallet_name = $wallet->wallet_name;
        $this->wallet_remarks = $wallet->wallet_remarks;
        $this->current_balance = $wallet->current_balance;
        $this->date = $wallet->date->format('Y-m-d');

        $this->editModal = true;
    }

    public function saveWallet()
    {
        $validated = $this->validate([
            'agent' => 'required|string|max:255',
            'wallet_name' => 'required|string|max:255',
            'wallet_remarks' => 'nullable|string|max:255',
            'current_balance' => 'required|numeric',
            'date' => 'required|date',
        ]);

        $user = $this->currentUser();
        $creatorType = $user instanceof \App\Models\User ? 'App\Models\User' : 'App\Models\Staff';

        if ($this->editingWalletId) {
            $wallet = Wallet::findOrFail($this->editingWalletId);
            $wallet->update(array_merge($validated, [
                'updated_by_id' => $user->id,
                'updated_by_type' => $creatorType,
            ]));
            $this->dispatch('walletUpdated');
            $this->editModal = false;
        } else {
            Wallet::create(array_merge($validated, [
                'created_by_id' => $user->id,
                'created_by_type' => $creatorType,
            ]));
            $this->dispatch('walletCreated');
            $this->addModal = false;
        }

        $this->reset(['editingWalletId','agent','wallet_name','wallet_remarks','current_balance','date']);
    }

    public function confirmDelete($id)
    {
        $this->confirmDeleteId = $id;
        $this->deleteModal = true;
    }

    public function deleteWallet()
    {
        Wallet::findOrFail($this->confirmDeleteId)->delete();
        $this->deleteModal = false;
        $this->confirmDeleteId = null;
        $this->resetPage();
    }

    public function render()
    {
        $user = $this->currentUser();

        // Filtered query
        $query = Wallet::query()
            ->when($this->searchAgent, fn($q) => $q->where('agent', 'like', '%'.$this->searchAgent.'%'))
            ->when($this->searchWallet, fn($q) => $q->where('wallet_name', 'like', '%'.$this->searchWallet.'%'))
            ->when($this->searchRemarks, fn($q) => $q->where('wallet_remarks', 'like', '%'.$this->searchRemarks.'%'))
            ->when($this->filterDate, fn($q) => $q->whereDate('date', $this->filterDate))
            ->orderBy('date', 'desc');

        // Get distinct dates
        $dates = $query->select('date')->distinct()->pluck('date')->sortDesc();

        // Pagination for dates (5 per page)
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 5;
        $currentDates = $dates->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $paginatedDates = new LengthAwarePaginator(
            $currentDates,
            $dates->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        // Fetch wallets for current 5 dates
        $walletsByDate = Wallet::whereIn('date', $currentDates)
            ->orderBy('date', 'desc')
            ->get()
            ->groupBy(fn($w) => $w->date->format('Y-m-d'));

        return view('livewire.wallets-table', [
            'walletsByDate' => $walletsByDate,
            'wallets' => $paginatedDates,
            'currentUser' => $user
        ]);
    }
}
