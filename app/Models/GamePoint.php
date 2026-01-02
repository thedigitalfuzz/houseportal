<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class GamePoint extends Model
{
    protected $fillable = [
        'game_id',
        'points',
        'date',
        'created_by_id',
        'created_by_type',
        'updated_by_id',
        'updated_by_type'
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

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

        return $this->updated_by_type === 'App\Models\Staff'
            ? ($this->updatedBy->staff_name ?? '-')
            : ($this->updatedBy->name ?? '-');
    }
}
