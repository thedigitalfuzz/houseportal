<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Player;
use App\Models\Transaction;

class StaffProfile extends Component
{
    use WithFileUploads;

    public $name;
    public $photo;
    public $existingPhoto;
    public $current_password;
    public $new_password;

    public $monthLabel;
    public $staffPlayersCount;
    public $staffTransactionsCount;
    public $staffTotalCashin;
    public $staffTotalCashout;

    public $highestCashinTxn;
    public $highestCashoutTxn;

    // Tables
    public $playersSearch = '';
    public $transactionsSearch = '';
    public $playersSortField = 'created_at';
    public $playersSortDirection = 'desc';
    public $transactionsSortField = 'transaction_date';
    public $transactionsSortDirection = 'desc';
    //public $perPage = 10;
    public $staffMonthlyPlayersCount;
    public $staffMonthlyTransactionsCount;
    public $staffMonthlyTotalCashin;
    public $staffMonthlyTotalCashout;
    public $highestMonthlyCashinTxn;
    public $highestMonthlyCashoutTxn;
    public $staffDailyPlayersCount;
    public $staffDailyTransactionsCount;
    public $staffDailyTotalCashin;
    public $staffDailyTotalCashout;

    public $highestDailyCashinTxn;
    public $highestDailyCashoutTxn;

    protected $updatesQueryString = ['playersSearch', 'transactionsSearch'];

    public function mount()
    {
        $staff = Auth::user();
        if (!$staff) abort(403, 'Unauthorized');

        $this->name = $staff->staff_name;
        $this->existingPhoto = $staff->photo;
        $this->monthLabel = now()->format('F Y');

        // All-time summary
        $this->updateSummary();
        $this->updateDailySummary();

        // Monthly summary
        $this->updateMonthlySummary();
    }

    public function updateSummary()
    {
        $staff = Auth::user();
        $staffId = $staff->id;

        $this->staffPlayersCount = Player::where('created_by_id', $staffId)->count();
        $this->staffTransactionsCount = Transaction::where('created_by_id', $staffId)->count();
        $this->staffTotalCashin = Transaction::where('created_by_id', $staffId)->sum('cashin');
        $this->staffTotalCashout = Transaction::where('created_by_id', $staffId)->sum('cashout');

        $this->highestCashinTxn = Transaction::where('created_by_id', $staffId)
            ->orderByDesc('cashin')
            ->with(['player','game'])
            ->first();

        $this->highestCashoutTxn = Transaction::where('created_by_id', $staffId)
            ->orderByDesc('cashout')
            ->with(['player','game'])
            ->first();
    }

    public function updateDailySummary()
    {
        $staff = Auth::user();
        $staffId = $staff->id;
        $today = now();

        // Players added today
        $this->staffDailyPlayersCount = Player::where('created_by_id', $staffId)
            ->whereDate('created_at', $today)
            ->count();

        // Transactions today
        $staffTxn = Transaction::where('created_by_id', $staffId)
            ->whereDate('transaction_date', $today);

        $this->staffDailyTransactionsCount = $staffTxn->count();
        $this->staffDailyTotalCashin = $staffTxn->sum('cashin');
        $this->staffDailyTotalCashout = $staffTxn->sum('cashout');

        $this->highestDailyCashinTxn = Transaction::where('created_by_id', $staffId)
            ->whereDate('transaction_date', $today)
            ->where('cashin', '>', 0)
            ->with(['player','game'])
            ->orderByDesc('cashin')
            ->first();

        $this->highestDailyCashoutTxn = Transaction::where('created_by_id', $staffId)
            ->whereDate('transaction_date', $today)
            ->where('cashout', '>', 0)
            ->with(['player','game'])
            ->orderByDesc('cashout')
            ->first();
    }
    public function updateMonthlySummary()
    {
        $staff = Auth::user();
        $staffId = $staff->id;
        $now = now();

        // Players added this month
        $this->staffMonthlyPlayersCount = Player::where('created_by_id', $staffId)
            ->whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->count();

        // Transactions this month
        $staffTxn = Transaction::where('created_by_id', $staffId)
            ->whereMonth('transaction_date', $now->month)
            ->whereYear('transaction_date', $now->year);

        $this->staffMonthlyTransactionsCount = $staffTxn->count();
        $this->staffMonthlyTotalCashin = $staffTxn->sum('cashin');
        $this->staffMonthlyTotalCashout = $staffTxn->sum('cashout');



        $this->highestMonthlyCashinTxn = Transaction::where('created_by_id', $staffId)
            ->whereMonth('transaction_date', $now->month)
            ->whereYear('transaction_date', $now->year)
            ->where('cashin', '>', 0) // IMPORTANT
            ->with(['player','game'])
            ->orderByDesc('cashin')
            ->first();

        $this->highestMonthlyCashoutTxn = Transaction::where('created_by_id', $staffId)
            ->whereMonth('transaction_date', $now->month)
            ->whereYear('transaction_date', $now->year)
            ->where('cashout', '>', 0) // IMPORTANT
            ->with(['player','game'])
            ->orderByDesc('cashout')
            ->first();
    }
    public function sortPlayers($field)
    {
        if ($this->playersSortField === $field) {
            $this->playersSortDirection = $this->playersSortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->playersSortField = $field;
            $this->playersSortDirection = 'asc';
        }
    }

    public function sortTransactions($field)
    {
        if ($this->transactionsSortField === $field) {
            $this->transactionsSortDirection = $this->transactionsSortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->transactionsSortField = $field;
            $this->transactionsSortDirection = 'asc';
        }
    }

    public function saveProfile()
    {
        $staff = Auth::user();

        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'photo' => 'nullable|image|max:2048',
            'current_password' => 'nullable|string',
            'new_password' => 'nullable|string|min:4',
        ]);

        if ($this->photo) {
            $path = $this->photo->store('staff_photos', 'public');
            $staff->photo = $path;
        }

        $staff->staff_name = $this->name;

        if ($this->new_password) {
            if (!Hash::check($this->current_password, $staff->password)) {
                $this->addError('current_password', 'Current password is incorrect.');
                return;
            }
            $staff->password = Hash::make($this->new_password);
            $staff->staff_plain_password = $this->new_password;
        }

        $staff->save();
        session()->flash('success', 'Profile updated successfully.');
        $this->updateSummary();
    }

    public function render()
    {
        $staff = Auth::user();

        $players = Player::where('created_by_id', $staff->id)
            ->when($this->playersSearch, fn($q) => $q->where('player_name', 'like', '%'.$this->playersSearch.'%'))
            ->orderBy($this->playersSortField, $this->playersSortDirection)
            ->get();

        $transactions = Transaction::with(['player', 'game'])
            ->where('created_by_id', $staff->id)
            ->when($this->transactionsSearch, fn($q) => $q->whereHas('player', fn($q2) => $q2->where('player_name', 'like', '%'.$this->transactionsSearch.'%')))
            ->orderBy($this->transactionsSortField, $this->transactionsSortDirection)
            ->get();

        return view('livewire.staff-profile', [
            'players' => $players,
            'transactions' => $transactions,
            'highestCashinTxn' => $this->highestCashinTxn,
            'highestCashoutTxn' => $this->highestCashoutTxn,
        ]);
    }
}
