<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $authUser, User $targetUser): bool
    {
        // Users can view their own profile
        // Admins can view any profile
        return $authUser->id === $targetUser->id || $authUser->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $authUser, User $targetUser): bool
    {
        // Users can update their own profile if they are active
        // Admins can update any profile
        return ($authUser->id === $targetUser->id && $authUser->is_active) || $authUser->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $authUser, User $targetUser): bool
    {
        // Users can delete their own profile if they are active
        // Admins can delete any profile
        return ($authUser->id === $targetUser->id && $authUser->is_active) || $authUser->isAdmin();
    }

    /**
     * Determine whether the user can view the host reservations calendar for the target user.
     */
    public function viewHostReservations(User $authUser, User $targetUser): bool
    {
        // Only approved hosts can view their own calendar. Admins may view any host calendar.
        if ($authUser->isAdmin()) return true;
        $isHost = ($authUser->application_status ?? null) === 'Approved';
        return $isHost && $authUser->id === $targetUser->id;
    }
}
