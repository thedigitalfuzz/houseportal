<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Staff extends Authenticatable
{
    use Notifiable;

    protected $table = 'staffs';

    protected $fillable = [
        'staff_name',
        'staff_username',
        'email',
        'password',
        'staff_plain_password',
        'photo',
    ];

    protected $hidden = [
        'password',
        'remember_token', // still hide hashed password
    ];

    // Remove getAuthIdentifierName to fix session user_id issue
    public function getAuthPassword()
    {
        return $this->password;
    }
}
