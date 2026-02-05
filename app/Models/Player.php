<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Player extends Model
{
    use HasFactory;

    protected $fillable = [
        'username',
        'facebook_profile',
        'player_name',
        'staff_id',
        'phone',
        'created_by_id',
        'created_by_type',
        'updated_by_id',
        'updated_by_type',
        ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
    public function assignedStaff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }
    public function createdBy(): MorphTo
    {
        return $this->morphTo(
            name: 'createdBy',
            type: 'created_by_type',
            id: 'created_by_id'
        );
    }

    public function updatedBy(): MorphTo
    {
        return $this->morphTo(
            name: 'updatedBy',
            type: 'updated_by_type',
            id: 'updated_by_id'
        );
    }

    public function getCreatedByNameAttribute(): string
    {
        if (!$this->createdBy) return '-';

        return $this->createdBy instanceof \App\Models\Staff
            ? ($this->createdBy->staff_name ?? '-')
            : ($this->createdBy->name ?? '-');
    }

    public function getUpdatedByNameAttribute(): string
    {
        if (is_null($this->updated_by_id)) {
            return '-';
        }
        if (!$this->updatedBy) return '-';

        return $this->updatedBy instanceof \App\Models\Staff
            ? ($this->updatedBy->staff_name ?? '-')
            : ($this->updatedBy->name ?? '-');
    }
}
