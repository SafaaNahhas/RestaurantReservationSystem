<?php

namespace App\Policies;

use App\Enums\RoleUser;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PgSql\Lob;

class ReservationPolicy
{
    /**
     * Check if the user can store a reservation.
     *
     * @param User $user
     * @return bool
     */
    public function store(User $user)
    {
        return $user->hasPermissionTo('store reservation');
    }
    // public function update(User $user, Reservation $reservation)
    // {
    //     return $user->hasPermissionTo('update reservation');
    // }
    public function update(User $user, Reservation $reservation)
    {
        // Check if the user has permission and is the owner of the reservation
        return $user->hasPermissionTo('update reservation') && $reservation->user_id === $user->id;
    }


        public function confirm(User $user, Reservation $reservation)
    {
        // Check if the user has permission and if the user is a manager of the department
        return $user->hasPermissionTo('confirm reservation') ||
            ($reservation->table && $reservation->table->department->manager_id == $user->id);
    }

    public function reject(User $user, Reservation $reservation)
    {
        // Check if the user has permission and if the user is a manager of the department
        return $user->hasPermissionTo('reject reservation') ||
            ($reservation->table && $reservation->table->department->manager_id == $user->id);
    }


    public function cancel(User $user, Reservation $reservation)
    {
        return $user->hasPermissionTo('cancle reservation') && $reservation->user_id === $user->id;
    }

    public function startService(User $user)
    {
        return $user->hasPermissionTo('start service');
    }

    public function completeService(User $user)
    {
        return $user->hasPermissionTo('complete service');
    }
    /**
     * Determine if the user can soft delete a reservation.
     *
     * @param User $user
     * @return bool
     */
    public function softDeleteReservation(User $user)
    {
        return $user->hasPermissionTo('soft delete reservation');
    }
    /**
     * Determine if the user can force delete a reservation.
     *
     * @param User $user
     * @return bool
     */
    public function forceDeleteReservation(User $user)
    {
        return $user->hasPermissionTo('hard delete reservation');
    }

    /**
     * Determine if the user can restore a soft-deleted reservation.
     *
     * @param User $user
     * @return bool
     */
    public function restoreReservation(User $user)
    {
        return $user->hasPermissionTo('restorereservation');
    }

    /**
     * Determine if the user can view soft-deleted reservations.
     *
     * @param User $user
     * @return bool
     */
    public function viewSoftDeletedReservations(User $user)
    {
        return $user->hasPermissionTo('view soft delete reservation');
}
    /**
     * Check if the user can view reservations by manager.
     *
     * @param User $user
     * @param Reservation $reservation
     * @return bool
     */
    public function viewReservationsByManager(User $user, Reservation $reservation)
    {
        // Allow access if the user is an admin or the manager of the department
        return $user->hasRole(RoleUser::Admin->value) ||
            ($reservation->table && $reservation->table->department->manager_id == $user->id);
    }

    /**
     * Check if the user can view the most frequent user.
     *
     * @param  User  $user
     * @return bool
     */
    public function viewMostFrequentUser(User $user)
    {
        // Allow access only if the user is an admin
        return $user->hasRole(RoleUser::Admin->value);
    }
    /**
     * Determine if the given user can create a rating for the reservation.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Reservation  $reservation
     * @return bool
     */
    public function storeRating(User $user, Reservation $reservation)
    {
        // Check if the reservation belongs to the user
        return Reservation::where('id', $reservation->id)
            ->where('user_id', $user->id)
            ->exists();
    }
}
