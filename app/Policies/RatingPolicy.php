<?php

namespace App\Policies;

use App\Enums\RoleUser;
use App\Models\User;
use App\Models\Rating;
use App\Models\Reservation;

class RatingPolicy
{
    /**
     * Determine whether the user can show all ratings.
     */
    public function index(User $user)
    {
        return $user->hasRole(RoleUser::Admin->value);
    }
    /**
     * Determine if the given user can create a rating for the reservation.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Reservation  $reservation
     * @return bool
     */
    public function store(User $user, Reservation $reservation)
    {
        // Check if the reservation belongs to the user
        return $reservation->user_id === $user->id;
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
    public function forceDelete(User $user, Rating $rating)
    {
        return $rating->user_id === $user->id ||
            $user->hasPermissionTo('hard delete reservation');
    }
}
