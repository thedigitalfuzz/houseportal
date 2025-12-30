<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Wallet;
use App\Models\WalletDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Transaction;


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

    /* -------- WALLET SEARCH DROPDOWNS (LIKE TRANSACTIONS) -------- */
    public $wallet_agent = null;
    public $wallet_name_filter = null;
    public $wallet_remarks_filter = null;

    public $walletAgents = [];
    public $walletNamesFilter = [];
    public $walletRemarksFilter = [];


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

    public $cashin = 0;
    public $cashout = 0;
    public $bonus = 0;
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

        $this->walletAgents = WalletDetail::select('agent')
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

    /* ---------------- MODAL OPENERS ---------------- */
    public function openAddModal()
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->reset([
            'editingWalletId',
            'agent',
            'wallet_name',
            'wallet_remarks',
            'current_balance',
            'date',
            'walletNames',
            'walletRemarks',
            'cashin',
            'cashout',
            'bonus',
        ]);

        $this->addModal = true;
    }

    public function openEditModal($id)
    {
        $this->resetErrorBag();
        $this->resetValidation();
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
        $this->resetErrorBag();
        $this->resetValidation();
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
            'cashin' => 'nullable|numeric|min:0',
            'cashout' => 'nullable|numeric|min:0',
            'bonus' => 'nullable|numeric|min:0',
            'date' => 'required|date',
        ]);

        // ----- DUPLICATE WALLET RECORD CHECK (SAME DATE) -----
        $duplicateQuery = Wallet::where('agent', $validated['agent'])
            ->where('wallet_name', $validated['wallet_name'])
            ->whereDate('date', $validated['date']);

        if (is_null($validated['wallet_remarks'])) {
            $duplicateQuery->whereNull('wallet_remarks');
        } else {
            $duplicateQuery->where('wallet_remarks', $validated['wallet_remarks']);
        }

// Ignore current record when editing
        if ($this->editingWalletId) {
            $duplicateQuery->where('id', '!=', $this->editingWalletId);
        }

        if ($duplicateQuery->exists()) {
            $this->addError(
                'date',
                "The wallet record for '{$validated['date']}' for this wallet already exists."
            );
            return;
        }

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
            ->when($this->wallet_agent, fn($q) => $q->where('agent', $this->wallet_agent))
            ->when($this->wallet_name_filter, fn($q) => $q->where('wallet_name', $this->wallet_name_filter))
            ->when($this->wallet_remarks_filter, fn($q) => $q->where('wallet_remarks', $this->wallet_remarks_filter))
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

        // Fetch wallets and group by date
        $walletsByDate = Wallet::query()
            ->when($this->wallet_agent, fn($q) => $q->where('agent', $this->wallet_agent))
            ->when($this->wallet_name_filter, fn($q) => $q->where('wallet_name', $this->wallet_name_filter))
            ->when($this->wallet_remarks_filter, fn($q) => $q->where('wallet_remarks', $this->wallet_remarks_filter))
            ->when($this->filterDate, fn($q) => $q->whereDate('date', $this->filterDate))
            ->whereIn('date', $currentDates)
            ->orderBy('date', 'desc')
            ->get()
            ->map(function($wallet) {
                $totals = Transaction::where('agent', $wallet->agent)
                    ->where('wallet_name', $wallet->wallet_name)
                    ->when($wallet->wallet_remarks, fn($q) => $q->where('wallet_remarks', $wallet->wallet_remarks))
                    ->whereDate('transaction_date', $wallet->date)
                    ->selectRaw('SUM(cashin) as cashin, SUM(cashout) as cashout, SUM(bonus_added) as bonus')
                    ->first();

                $wallet->cashin = $totals->cashin ?? 0;
                $wallet->cashout = $totals->cashout ?? 0;
                $wallet->bonus = $totals->bonus ?? 0;

                return $wallet;
            })
            ->groupBy(fn($w) => $w->date->format('Y-m-d'));

        return view('livewire.wallets-table', [
            'walletsByDate' => $walletsByDate,
            'wallets' => $paginatedDates,
            'currentUser' => $this->currentUser(),
            'walletAgents' => $this->walletAgents,
            'walletNamesFilter' => $this->walletNamesFilter,
            'walletRemarksFilter' => $this->walletRemarksFilter,
        ]);
    }

}
