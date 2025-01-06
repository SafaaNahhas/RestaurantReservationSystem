<?php

namespace App\Policies;

use App\Enums\RoleUser;
use App\Models\User;
use App\Models\Rating;
use App\Models\Reservation;
use Illuminate\Support\Facades\Log;

class RatingPolicy
{
    /**
     * Determine whether the user can show all ratings.
     */
    public function show(User $user)
    {
        // Allow only Admin and Reservation Manager to view all ratings
        return $user->hasRole(RoleUser::Admin->value) ||
            $user->hasRole(RoleUser::ReservationManager->value);
    }



    public function create(User $user, $userId, $reservationId)
    {

    /**  Verify that the logged-in user is the same as the id in the link
     *   And check that the reservation belongs to this user
     */
        return $user->id == $userId &&
            $user->reservations->contains('id', $reservationId);
    }


    /**
     * Determine whether the user can update the rating.
     */
    public function update(User $user, Rating $rating)
    {
        return $rating->user_id === $user->id;
    }

    /**
     * Determine whether the user can permanently delete a rating.
     */
    public function delete(User $user, Rating $rating)
    {
        return $rating->user_id === $user->id ||
            $user->hasPermissionTo('delete rating');
    }

    public function forceDelete(User $user)
    {
        // Allow only Admin can do the force delete
        return $user->hasRole(RoleUser::Admin->value);
    }
    public function restore(User $user)
    {
        // Allow only Admin can do the restore the deleting rating

        return $user->hasRole(RoleUser::Admin->value);
    }
    public function get_deleting(User $user)
    {
        // Allow only Admin can do the restore the deleting rating

        return $user->hasRole(RoleUser::Admin->value);
    }
}
