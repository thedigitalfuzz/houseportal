<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subdistributor extends Model
{
    protected $fillable = [
        'game_id',
        'sub_username',
        'status'
    ];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }
}
