<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'type',
        'message',
        'notifiable_id',
        'notifiable_type',
        'is_read',
        'redirect_to',
    ];

    public function notifiable()
    {
        return $this->morphTo();
    }
}
