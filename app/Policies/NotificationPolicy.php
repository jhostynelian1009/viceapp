<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;

class NotificationPolicy
{
    /**
     * Determine whether the user can mark a notification as read.
     */
    public function markAsRead(User $user, DatabaseNotification $notification): bool
    {
        return (int) $notification->notifiable_id === (int) $user->id
            && $notification->notifiable_type === User::class;
    }
}
