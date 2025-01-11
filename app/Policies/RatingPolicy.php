<?php

namespace App\Policies;

use App\Enums\RoleUser;
use App\Models\User;
use App\Models\Rating;
use App\Models\Reservation;
use Illuminate\Support\Facades\Log;

class RatingPolicy
{
    /*
     * Verify that the user is the same as the user in the link and that he has the same booking number as the link
     */
    public function show(User $user)
    {
        // Allow only Admin and Reservation Manager to view all ratings
        return $user->hasRole(RoleUser::Admin->value) ||
            $user->hasRole(RoleUser::Manager->value);
    }


    public function create(User $user, $userId, $reservationId)
    {
        if ($user->id != $userId) {
            return false;
        }

        return Reservation::where('id', $reservationId)
            ->where('user_id', $userId)
            ->exists();
    }



    /**
     * Determine whether the user can show the rating.
     */
    // public function show(User $user, Rating $rating)
    // {
    //     // تحقق من الصلاحيات هنا
    //     return $user->id == $rating->user_id; // مثال: السماح للمستخدم بقراءة التقييم الخاص به
    // }

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
