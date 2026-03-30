<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayerAgent extends Model
{
use HasFactory;

protected $fillable = [
'player_agent_name',
'facebook_profile',
'facebook_password',
    'email_id',
'two_way_verification_code',
'photo',

];

// Optional: relation to players
public function players()
{
return $this->hasMany(Player::class, 'agent_id');
}
}
