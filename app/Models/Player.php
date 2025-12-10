<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    use HasFactory;

    protected $fillable = ['username','facebook_profile','player_name', 'staff_id' ,'phone'];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
    public function assignedStaff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }
}
