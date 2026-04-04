<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class GameCreditCredential extends Model
{
    protected $fillable = [
        'game_id',
        'type',
        'username',
        'password',
    ];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    // 🔐 Encrypt before saving
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Crypt::encryptString($value);
    }

    // 🔓 Decrypt when accessing
    public function getPasswordAttribute($value)
    {
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return $value; // fallback if old data exists
        }
    }
}
