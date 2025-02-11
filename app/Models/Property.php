<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'address',
        'latitude',
        'longitude',
        'price_per_night',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
