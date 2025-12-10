<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Wallet extends Model
{
    protected $fillable = [
        'agent',
        'wallet_name',
        'wallet_remarks',
        'current_balance',
        'date',
        'created_by_id',
        'created_by_type',
        'updated_by_id',
        'updated_by_type',
    ];

    // Casts for proper data types
    protected $casts = [
        'current_balance' => 'decimal:2',  // ensures two decimal places
        'date' => 'date',                  // casts to Carbon instance
    ];

    /**
     * Polymorphic relation for creator (Admin or Staff)
     */
    public function createdBy(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Polymorphic relation for last editor (Admin or Staff)
     */
    public function updatedBy(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the name of the creator, whether Admin or Staff
     */
    public function getCreatedByNameAttribute(): string
    {
        if (!$this->createdBy) return '-';

        // If Staff model, use staff_name, else default name
        return $this->created_by_type === 'App\Models\Staff'
            ? ($this->createdBy->staff_name ?? '-')
            : ($this->createdBy->name ?? '-');
    }

    /**
     * Get the name of the last editor, whether Admin or Staff
     */
    public function getUpdatedByNameAttribute(): string
    {
        if (!$this->updatedBy) return '-';

        // If Staff model, use staff_name, else default name
        return $this->updated_by_type === 'App\Models\Staff'
            ? ($this->updatedBy->staff_name ?? '-')
            : ($this->updatedBy->name ?? '-');
    }
}
