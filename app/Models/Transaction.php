<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
        'bonus_added',
        'deposit',
        'transaction_time',
        'notes'
    ];

    protected $dates = ['transaction_time'];

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
}
