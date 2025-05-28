<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Message;
use Illuminate\Auth\Access\HandlesAuthorization;

class MessagePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the chat with another user.
     */
    public function chat(User $user, User $otherUser): bool
    {
        // Проверяем, что пользователи не одинаковые
        if ($user->id === $otherUser->id) {
            return false;
        }

        // Проверяем, что оба пользователя активны
        if (!$user->is_active || !$otherUser->is_active) {
            return false;
        }

        // Проверяем, что у пользователей есть общее бронирование
        $hasCommonBooking = $user->bookings()
            ->whereHas('property', function ($query) use ($otherUser) {
                $query->where('user_id', $otherUser->id);
            })
            ->orWhereHas('property', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->exists();

        return $hasCommonBooking;
    }

    /**
     * Determine whether the user can view the message.
     */
    public function view(User $user, Message $message): bool
    {
        return $user->id === $message->from_user_id || $user->id === $message->to_user_id;
    }

    /**
     * Determine whether the user can create messages.
     */
    public function create(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Determine whether the user can delete the message.
     */
    public function delete(User $user, Message $message): bool
    {
        return $user->id === $message->from_user_id;
    }
} 