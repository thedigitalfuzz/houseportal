<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Models\Player;
use App\Models\Game;
use App\Models\User;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'player_id',
        'game_id',
        'staff_user_id',
        'cashin',
        'cashout',
        'total_transaction',
        'bonus_added',
        'deposit',
        'cash_tag',
        'agent',
        'wallet_name',
        'wallet_remarks',
        'transaction_time',
        'transaction_date',
        'notes',
        'created_by_id',
        'created_by_type',
        'updated_by_id',
        'updated_by_type',
    ];

    protected $dates = ['transaction_time', 'transaction_date'];

    protected $casts = [
        'transaction_date' => 'date',
    ];



    public function player()
    {
        return $this->belongsTo(Player::class);
    }

    public function game()
    {
        return $this->belongsTo(Game::class);
    }


    public function staffUser()
    {
        return $this->belongsTo(User::class, 'staff_user_id');
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
        // ❌ Only show editor if it was actually edited
        if (is_null($this->updated_by_id)) return '-';
        if (!$this->updatedBy) return '-';

        return $this->updated_by_type === 'App\Models\Staff'
            ? ($this->updatedBy->staff_name ?? '-')
            : ($this->updatedBy->name ?? '-');
    }
}
