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

    // Casts for proper formatting
    protected $casts = [
        'current_balance' => 'decimal:2',
        'date' => 'date',
    ];

    // Polymorphic creator (Admin or Staff)
    public function createdBy(): MorphTo
    {
        return $this->morphTo();
    }

    // Polymorphic last editor (Admin or Staff)
    public function updatedBy(): MorphTo
    {
        return $this->morphTo();
    }
}
