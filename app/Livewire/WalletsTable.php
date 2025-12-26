<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Wallet;
use App\Models\WalletDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;

class WalletsTable extends Component
{
    use WithPagination;

    /* ---------------- SEARCH INPUTS ---------------- */
    public $searchAgentInput = '';
    public $searchWalletInput = '';
    public $searchRemarksInput = '';
    public $filterDateInput = null;

    public $searchAgent = '';
    public $searchWallet = '';
    public $searchRemarks = '';
    public $filterDate = null;

    /* ---------------- MODALS ---------------- */
    public $addModal = false;
    public $editModal = false;
    public $deleteModal = false;
    public $addWalletDetailModal = false;

    /* ---------------- WALLET FORM ---------------- */
    public $editingWalletId;
    public $agent;
    public $wallet_name;
    public $wallet_remarks;
    public $current_balance;
    public $date;

    /* ---------------- WALLET DETAILS (MASTER) ---------------- */
    public $agents = [];
    public $walletNames = [];
    public $walletRemarks = [];

    public $detail_agent;
    public $detail_wallet_name;
    public $detail_wallet_remarks;

    public $confirmDeleteId = null;

    protected $listeners = [
        'walletCreated' => '$refresh',
        'walletUpdated' => '$refresh'
    ];

    /* ---------------- AUTH ---------------- */
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

    /* ---------------- MOUNT ---------------- */
    public function mount()
    {
        $this->agents = WalletDetail::select('agent')
            ->distinct()
            ->orderBy('agent')
            ->pluck('agent')
            ->toArray();
    }

    /* ---------------- SEARCH ---------------- */
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

    /* ---------------- MODAL OPENERS ---------------- */
    public function openAddModal()
    {
        $this->reset([
            'editingWalletId',
            'agent',
            'wallet_name',
            'wallet_remarks',
            'current_balance',
            'date',
            'walletNames',
            'walletRemarks'
        ]);

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

        $this->walletNames = WalletDetail::where('agent', $this->agent)
            ->select('wallet_name')
            ->distinct()
            ->pluck('wallet_name')
            ->toArray();

        $this->walletRemarks = WalletDetail::where('agent', $this->agent)
            ->where('wallet_name', $this->wallet_name)
            ->pluck('wallet_remarks')
            ->toArray();

        $this->editModal = true;
    }

    public function openAddWalletDetailModal()
    {
        $this->reset([
            'detail_agent',
            'detail_wallet_name',
            'detail_wallet_remarks'
        ]);

        $this->addWalletDetailModal = true;
    }

    /* ---------------- SAVE WALLET ---------------- */
    public function saveWallet()
    {
        $validated = $this->validate([
            'agent' => 'required|string|max:255',
            'wallet_name' => 'required|string|max:255',
            'wallet_remarks' => 'nullable|string|max:255',
            'current_balance' => 'required|numeric',
            'date' => 'required|date',
        ]);

        $previousWalletQuery = Wallet::where('agent', $validated['agent'])
            ->where('wallet_name', $validated['wallet_name'])
            ->where('date', '<', $validated['date']);

        if (is_null($validated['wallet_remarks'])) {
            $previousWalletQuery->whereNull('wallet_remarks');
        } else {
            $previousWalletQuery->where('wallet_remarks', $validated['wallet_remarks']);
        }

        $previousWallet = $previousWalletQuery
            ->orderBy('date', 'desc')
            ->first();

        $validated['balance_difference'] = $previousWallet
            ? ($validated['current_balance'] - $previousWallet->current_balance)
            : 0;



        $user = $this->currentUser();
        $creatorType = $user instanceof \App\Models\User
            ? 'App\Models\User'
            : 'App\Models\Staff';

        if ($this->editingWalletId) {
            Wallet::findOrFail($this->editingWalletId)->update(
                array_merge($validated, [
                    'updated_by_id' => $user->id,
                    'updated_by_type' => $creatorType,
                ])
            );

            $this->dispatch('walletUpdated');
            $this->editModal = false;
        } else {
            Wallet::create(
                array_merge($validated, [
                    'created_by_id' => $user->id,
                    'created_by_type' => $creatorType,
                ])
            );

            $this->dispatch('walletCreated');
            $this->addModal = false;
        }

        $this->reset([
            'editingWalletId',
            'agent',
            'wallet_name',
            'wallet_remarks',
            'current_balance',
            'date'
        ]);
    }

    /* ---------------- SAVE WALLET DETAIL ---------------- */
    public function saveWalletDetail()
    {
        $this->validate([
            'detail_agent' => 'required|string|max:255',
            'detail_wallet_name' => 'required|string|max:255',
            'detail_wallet_remarks' => 'nullable|string|max:255',
        ]);

        WalletDetail::create([
            'agent' => $this->detail_agent,
            'wallet_name' => $this->detail_wallet_name,
            'wallet_remarks' => $this->detail_wallet_remarks,
        ]);

        $this->agents = WalletDetail::select('agent')
            ->distinct()
            ->orderBy('agent')
            ->pluck('agent')
            ->toArray();

        $this->addWalletDetailModal = false;
    }

    /* ---------------- DELETE ---------------- */
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

    /* ---------------- RENDER ---------------- */
    public function render()
    {
        $query = Wallet::query()
            ->when($this->searchAgent, fn($q) => $q->where('agent', 'like', "%{$this->searchAgent}%"))
            ->when($this->searchWallet, fn($q) => $q->where('wallet_name', 'like', "%{$this->searchWallet}%"))
            ->when($this->searchRemarks, fn($q) => $q->where('wallet_remarks', 'like', "%{$this->searchRemarks}%"))
            ->when($this->filterDate, fn($q) => $q->whereDate('date', $this->filterDate))
            ->orderBy('date', 'desc');

        $dates = $query->select('date')->distinct()->pluck('date')->sortDesc();

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

        $walletsByDate = Wallet::query()
            ->when($this->searchAgent, fn($q) => $q->where('agent', 'like', "%{$this->searchAgent}%"))
            ->when($this->searchWallet, fn($q) => $q->where('wallet_name', 'like', "%{$this->searchWallet}%"))
            ->when($this->searchRemarks, fn($q) => $q->where('wallet_remarks', 'like', "%{$this->searchRemarks}%"))
            ->when($this->filterDate, fn($q) => $q->whereDate('date', $this->filterDate))
            ->whereIn('date', $currentDates)
            ->orderBy('date', 'desc')
            ->get()
            ->groupBy(fn($w) => $w->date->format('Y-m-d'));

        return view('livewire.wallets-table', [
            'walletsByDate' => $walletsByDate,
            'wallets' => $paginatedDates,
            'currentUser' => $this->currentUser(),
        ]);
    }
}
