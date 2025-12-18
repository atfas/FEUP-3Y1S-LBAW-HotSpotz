<?php

use Illuminate\Support\Facades\Broadcast;

/**
 * Register broadcast authorization channels.
 */

// Private channel for user-specific notifications
Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
