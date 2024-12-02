<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Rating;
use App\Models\Reservation;

class RatingPolicy
{
    /**
     * Determine whether the user can create a new rating.
     */
    public function create(User $user, $reservationId)
    {
        // التحقق من أن المستخدم لديه حجز مرتبط
        return Reservation::where('id', $reservationId)
            ->where('user_id', $user->id)
            ->exists();
    }

    /**
     * Determine whether the user can update the rating.
     */
    public function update(User $user, Rating $rating)
    {
        return $rating->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the rating.
     */
    public function delete(User $user, Rating $rating)
    {
        return $rating->user_id === $user->id;
    }

    /**
     * Determine whether the user can view soft-deleted ratings.
     */
    public function viewDeleted(User $user)
    {
        return $user->hasRole(['admin', 'manager']);
    }

    /**
     * Determine whether the user can restore soft-deleted ratings.
     */
    public function restore(User $user, Rating $rating)
    {
        return $user->hasRole(['admin', 'manager']);
    }

    /**
     * Determine whether the user can permanently delete a rating.
     */
    public function forceDelete(User $user, Rating $rating)
    {
        return $user->hasRole(['admin', 'manager']);
    }
}
