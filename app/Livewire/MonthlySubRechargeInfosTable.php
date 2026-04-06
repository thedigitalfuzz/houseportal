<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\SubRechargeInformation;
use Carbon\Carbon;

class MonthlySubRechargeInfosTable extends Component
{
    public $month;
    public $year;
    public $searchMode = false;

    public function mount()
    {
        $this->month = '';
        $this->year = '';
    }

    public function search()
    {
        $this->searchMode = true;
    }

    public function render()
    {
        $query = SubRechargeInformation::with(['game','subdistributor']);

        if ($this->searchMode) {
            if ($this->year) {
                $query->whereYear('date', $this->year);
            }
            if ($this->month) {
                $query->whereMonth('date', $this->month);
            }
        }

        // Group by month
        $grouped = $query->get()
            ->groupBy(function ($item) {
                return Carbon::parse($item->date)->format('Y-m');
            })
            ->sortKeysDesc();

        $monthlyData = [];

        foreach ($grouped as $monthKey => $records) {

            $rows = $records
                ->groupBy(function ($item) {
                    return $item->game_id . '-' . $item->subdistributor_id;
                })
                ->map(function ($group) {
                    return [
                        'game_name' => $group->first()->game->name,
                        'sub_name' => $group->first()->subdistributor->sub_username,
                        'total_recharge' => $group->sum('amount'),
                        'last_recharge_date' => $group->max('date'),
                    ];
                })
                ->sortByDesc('total_recharge')
                ->values();

            $monthlyData[] = [
                'month' => $monthKey,
                'rows' => $rows,
            ];
        }

        return view('livewire.monthly-sub-recharge-infos-table', [
            'monthlyData' => $monthlyData,
        ]);
    }
}
