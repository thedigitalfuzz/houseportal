<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    protected $fillable = ['name','game_code', 'backend_link'];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
