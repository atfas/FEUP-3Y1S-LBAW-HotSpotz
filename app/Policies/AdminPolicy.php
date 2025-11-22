<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AdminPolicy
{
    /**
     * Determine whether the user can access the admin panel.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function viewAdminPanel(User $user): bool
    {
        // Only active admin users can access the admin panel
        return Auth::check() && $user->is_admin && $user->is_active;
    }

    /**
     * Determine whether the user can manage other users.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function manageUsers(User $user): bool
    {
        return Auth::check() && $user->is_admin && $user->is_active;
    }

    /**
     * Determine whether the user can promote/demote admins.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $targetUser
     * @return bool
     */
    public function changeAdminStatus(User $user, User $targetUser): bool
    {
        // Admin cannot change their own admin status
        // Only active admins can change other users' admin status
        return Auth::check() 
            && $user->is_admin 
            && $user->is_active 
            && $user->id !== $targetUser->id;
    }

    /**
     * Determine whether the user can enable/disable accounts.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $targetUser
     * @return bool
     */
    public function toggleUserStatus(User $user, User $targetUser): bool
    {
        // Admin cannot disable their own account
        return Auth::check() 
            && $user->is_admin 
            && $user->is_active 
            && $user->id !== $targetUser->id;
    }

    /**
     * Determine whether the user can delete other users.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $targetUser
     * @return bool
     */
    public function deleteUser(User $user, User $targetUser): bool
    {
        // Admin cannot delete themselves
        return Auth::check() 
            && $user->is_admin 
            && $user->is_active 
            && $user->id !== $targetUser->id;
    }
}