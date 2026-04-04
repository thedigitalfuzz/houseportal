<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'facebook_profile',
        'photo',
        'role',
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

    /**
     * Players created by this staff
     */
    public function players(): HasMany
    {
        return $this->hasMany(Player::class, 'created_by_id'); // adjust 'created_by_id' if your column is different
    }

    /**
     * Transactions created by this staff
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'created_by_id'); // adjust if your column differs
    }
    public function isOnline(): bool
    {
        return cache()->has('user-is-online-' . ($this->staff_id ?? $this->id));
    }
}
