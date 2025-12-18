<?php

namespace App\Policies;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class NotificationPolicy
{
    /**
     * Determine whether the user can view any notifications.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the notification.
     */
    public function view(User $user, Notification $notification): Response
    {
        return $user->id === $notification->user_id
            ? Response::allow()
            : Response::deny('You can only view your own notifications.');
    }

    /**
     * Determine whether the user can update the notification (mark as read).
     */
    public function update(User $user, Notification $notification): Response
    {
        return $user->id === $notification->user_id
            ? Response::allow()
            : Response::deny('You can only update your own notifications.');
    }

    /**
     * Determine whether the user can delete the notification.
     */
    public function delete(User $user, Notification $notification): Response
    {
        return $user->id === $notification->user_id
            ? Response::allow()
            : Response::deny('You can only delete your own notifications.');
    }
}
