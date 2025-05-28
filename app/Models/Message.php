<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Message extends Model
{
    protected $fillable = [
        'from_user_id',
        'to_user_id',
        'content',
        'booking_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who sent the message.
     */
    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    /**
     * Get the user who received the message.
     */
    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    /**
     * Get the booking associated with the message.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get formatted created date.
     */
    public function getFormattedCreatedAtAttribute(): string
    {
        return $this->created_at->format('H:i d.m.Y');
    }

    /**
     * Get formatted updated date.
     */
    public function getFormattedUpdatedAtAttribute(): string
    {
        return $this->updated_at->format('H:i d.m.Y');
    }
}
