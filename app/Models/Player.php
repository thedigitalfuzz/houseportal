<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    use HasFactory;

    protected $fillable = ['username','facebook_profile','balance'];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
