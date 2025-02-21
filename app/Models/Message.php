<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Message extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'from_user_id',
        'to_user_id',
        'content',
    ];

    protected $dates = ['deleted_at'];

    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser()
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

}
