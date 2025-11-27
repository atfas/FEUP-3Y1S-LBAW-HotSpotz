<?php

namespace App\Policies;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\DB;

class ListingPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can create listings.
     */
    public function create(User $user)
    {
        return $user && ($user->application_status === 'Approved')
            ? Response::allow()
            : Response::deny('Only approved hosts can create listings.');
    }

    /**
     * Determine whether the user can update the listing.
     */
    public function update(User $user, Listing $listing)
    {
        return $user && ($user->id === $listing->host_id)
            ? Response::allow()
            : Response::deny('Only the listing owner can update the listing.');
    }

    /**
     * Determine whether the user can delete the listing.
     * Also blocks deletion when there are confirmed future bookings.
     */
    public function delete(User $user, Listing $listing)
    {
        if (!$user || $user->id !== $listing->host_id) {
            return Response::deny('Only the listing owner can delete the listing.');
        }

        $today = date('Y-m-d');
        $hasFutureConfirmed = DB::table('bookings')
            ->join('availability', 'bookings.availability_id', '=', 'availability.id')
            ->where('availability.listing_id', $listing->id)
            ->where('bookings.status', 'Confirmed')
            ->where('bookings.check_in_date', '>=', $today)
            ->exists();

        if ($hasFutureConfirmed) {
            return Response::deny('Cannot delete listing: there are confirmed future bookings scheduled for this listing.');
        }

        return Response::allow();
    }
}
