<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class WalletDetail extends Model
{

    protected $table = 'wallet_details';

    protected $fillable = [
        'agent',
        'wallet_name',
        'wallet_remarks',
        'status',
        'status_date',
        'created_by_id',
        'created_by_type',
        'updated_by_id',
        'updated_by_type',
    ];

    protected $casts = [
        'status_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /* UI helper */
    public function getEffectiveStatusDateAttribute()
    {
        return $this->status === 'disabled'
            ? $this->status_date
            : $this->created_at;
    }
    public function createdBy(): MorphTo
    {
        return $this->morphTo();
    }

    public function updatedBy(): MorphTo
    {
        return $this->morphTo();
    }
    public function getCreatedByNameAttribute(): string
    {
        if (!$this->createdBy) return '-';

        return $this->created_by_type === 'App\Models\Staff'
            ? ($this->createdBy->staff_name ?? '-')
            : ($this->createdBy->name ?? '-');
    }

    public function getUpdatedByNameAttribute(): string
    {
        if (!$this->updatedBy) return '-';

        return $this->updated_by_type === 'App\Models\Staff'
            ? ($this->updatedBy->staff_name ?? '-')
            : ($this->updatedBy->name ?? '-');
    }
}
