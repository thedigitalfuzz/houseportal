<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubRechargeInformation extends Model
{
    protected $table = 'sub_recharge_informations';
    protected $fillable = [
        'game_id',
        'subdistributor_id',
        'amount',
        'date',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function subdistributor()
    {
        return $this->belongsTo(Subdistributor::class);
    }
}
