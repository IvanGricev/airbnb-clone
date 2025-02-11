<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandlordApplication extends Model
{
    protected $fillable = [
        'user_id',
        'message',
        'status', // pending, approved, rejected
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
