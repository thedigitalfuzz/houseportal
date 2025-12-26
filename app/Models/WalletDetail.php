<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletDetail extends Model
{
    protected $table = 'wallet_details';

    protected $fillable = [
        'agent',
        'wallet_name',
        'wallet_remarks',
    ];
}
