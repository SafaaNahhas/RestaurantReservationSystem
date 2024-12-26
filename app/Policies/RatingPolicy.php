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
    public function index(User $user)
    {
        return $user->hasRole(RoleUser::Admin->value);
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
}
