<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class GameCredit extends Model
{
    protected $fillable = [
        'game_id',
        'subdistributor_name',
        'subdistributor_balance',
        'store_name',
        'store_balance',
        'date',
        'created_by_id',
        'created_by_type',
        'updated_by_id',
        'updated_by_type',
    ];

    protected $casts = [
        'date' => 'date',
        'subdistributor_balance' => 'decimal:2',
        'store_balance' => 'decimal:2',
    ];

    /**
     * Link to the Game model
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class, 'game_id');
    }

    /**
     * Polymorphic relations for created_by and updated_by
     */
    public function createdBy(): MorphTo
    {
        return $this->morphTo();
    }

    public function updatedBy(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the name of the creator, Admin or Staff
     */
    public function getCreatedByNameAttribute(): string
    {
        if (!$this->createdBy) return '-';

        return $this->created_by_type === 'App\Models\Staff'
            ? ($this->createdBy->staff_name ?? '-')
            : ($this->createdBy->name ?? '-');
    }

    /**
     * Get the name of the last editor, Admin or Staff
     */
    public function getUpdatedByNameAttribute(): string
    {
        if (!$this->updatedBy) return '-';

        return $this->updated_by_type === 'App\Models\Staff'
            ? ($this->updatedBy->staff_name ?? '-')
            : ($this->updatedBy->name ?? '-');
    }
}
