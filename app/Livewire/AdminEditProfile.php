<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminEditProfile extends Component
{
    use WithFileUploads;

    public $name;
    public $photo = null;         // FIX: initialize properly
    public $existingPhoto = null; // FIX: same pattern as Staffs
    public $current_password;
    public $new_password;
    public $todaySummary = [];
    public $monthlySummary = [];
    public $allTimeSummary = [];

    public $last10DaysLabels = [];
    public $last10DaysData = [];

    public $allTimeLabels = [];
    public $allTimeCashin = [];
    public $allTimeCashout = [];
    public $allTimeNet = [];

    public $dailyTable = [];
    public $activeTableTab = 'daily';
    public $monthlyTable = [];
    public $allTimeTable = [];

    public function mount()
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'admin') {
            abort(403,'Unauthorized access');
        }

        $this->name = $user->name;
        $this->existingPhoto = $user->photo; // FIX: load existing photo

        // ==========================
// ✅ SYSTEM SUMMARIES
// ==========================

        $this->todaySummary = $this->calculateSummary(
            now()->startOfDay(),
            now()->endOfDay()
        );

        $this->monthlySummary = $this->calculateSummary(
            now()->startOfMonth(),
            now()->endOfMonth()
        );

        $this->allTimeSummary = $this->calculateSummary();

// ==========================
// ✅ LAST 10 DAYS GRAPH
// ==========================

        $allDates = \App\Models\Transaction::selectRaw('
    DATE(transaction_date) as day,
    SUM(cashin) as total
')
            ->groupBy('day')
            ->orderByDesc('day') // earliest first
            ->get();

        $this->last10DaysLabels = $allDates->pluck('day')
            ->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M'))
            ->toArray();

        $this->last10DaysData = $allDates->pluck('total')->toArray();

// ==========================
// ✅ ALL TIME GRAPH + TABLE
// ==========================

        $allTime = \App\Models\Transaction::selectRaw('
    DATE(transaction_date) as day,
    SUM(cashin) as cashin,
    SUM(cashout) as cashout
')
            ->groupBy('day')
            ->orderBy('day', 'asc') // latest first
            ->get();

        $this->allTimeLabels = $allTime->pluck('day')
            ->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M Y'))
            ->toArray();

        $this->allTimeCashin = $allTime->pluck('cashin')->toArray();
        $this->allTimeCashout = $allTime->pluck('cashout')->toArray();

        $this->allTimeNet = $allTime->map(fn($row) => $row->cashin - $row->cashout)->toArray();

        // DAILY TABLE
        $this->dailyTable = \App\Models\Transaction::selectRaw('
    DATE(transaction_date) as label,
    SUM(cashin) as cashin,
    SUM(cashout) as cashout
')
            ->groupBy('label')
            ->orderByDesc('label')
            ->get()
            ->map(fn($row) => [
                'label' => $row->label,
                'cashin' => $row->cashin,
                'cashout' => $row->cashout
            ])
            ->values()
            ->toArray();

// MONTHLY TABLE
        $this->monthlyTable = \App\Models\Transaction::selectRaw('
    YEAR(transaction_date) as y,
    MONTH(transaction_date) as m,
    SUM(cashin) as cashin,
    SUM(cashout) as cashout
')
            ->groupBy('y','m')
            ->orderByDesc('y')
            ->orderByDesc('m')
            ->get()
            ->map(function ($row) {
                return [
                    'label' => \Carbon\Carbon::create($row->y, $row->m)->format('F Y'),
                    'cashin' => $row->cashin,
                    'cashout' => $row->cashout
                ];
            })
            ->values()
            ->toArray();

// ALL TIME TABLE (single row)
        $this->allTimeTable = [
            [
                'label' => 'All Time',
                'cashin' => $this->allTimeSummary['cashin'],
                'cashout' => $this->allTimeSummary['cashout']
            ]
        ];
    }

    public function saveProfile()
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'photo' => 'nullable|image|max:2048',
            'current_password' => 'nullable|string',
            'new_password' => 'nullable|string|min:4',
        ]);

        if ($this->photo) {
            $path = $this->photo->store('admin_photos', 'public');
            $user->photo = $path;
        }

        $user->name = $this->name;

        if ($this->new_password) {
            if (!Hash::check($this->current_password, $user->password)) {
                $this->addError('current_password', 'Current password is incorrect.');
                return;
            }

            $user->password = Hash::make($this->new_password);
        }

        $user->save();

        session()->flash('success', 'Profile updated successfully.');
    }
    public function setTableTab($tab)
    {
        $this->activeTableTab = $tab;
    }
    private function calculateSummary($start = null, $end = null)
    {
        $base = \App\Models\Transaction::query();

        if ($start && $end) {
            $base->whereBetween('transaction_date', [$start, $end]);
        }

        $transactions = (clone $base)->count();
        $players = (clone $base)->distinct('player_id')->count('player_id');
        $cashin = (clone $base)->sum('cashin');
        $cashout = (clone $base)->sum('cashout');

        return [
            'transactions' => $transactions,
            'players' => $players,
            'cashin' => $cashin,
            'cashout' => $cashout,
            'net' => $cashin - $cashout,
        ];
    }
    public function render()
    {
        return view('livewire.admin-edit-profile');
    }
}
