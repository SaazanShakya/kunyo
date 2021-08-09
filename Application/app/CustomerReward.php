<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomerReward extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'points',
        'used_points',
        'remaining_points',
        'expiry_date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
