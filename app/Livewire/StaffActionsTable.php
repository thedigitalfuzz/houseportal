<?php

namespace App\Livewire;

use Livewire\Component;

class StaffActionsTable extends Component
{
    public function canEdit(): bool
    {
        return auth()->user()->role === 'admin';
    }

    public function canDelete(): bool
    {
        return auth()->user()->role === 'admin';
    }

    public function render()
    {
        return view('livewire.staff-actions-table');
    }
}
