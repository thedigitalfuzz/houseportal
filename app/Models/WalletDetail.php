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
        'status',
        'status_date',
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
}
