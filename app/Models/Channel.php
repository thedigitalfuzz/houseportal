<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Models\Staff;
use App\Models\Message;

class Channel extends Model
{
    use HasFactory;

    protected $fillable = ['name','type','created_by'];

    public function users()
    {
        return $this->belongsToMany(Staff::class, 'channel_user', 'channel_id', 'user_id')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
    // Creator of the channel
    public function creator()
    {
        return $this->belongsTo(Staff::class, 'created_by');
    }
}
