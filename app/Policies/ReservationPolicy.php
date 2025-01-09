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
    public function store(User $user)
    {
        return $user->hasPermissionTo('store reservation');
    }
    // public function update(User $user)
    // {
    //     return $user->hasPermissionTo('update reservation');
    // }
    public function update(User $user, Reservation $reservation)
    {
        // السماح بالتعديل فقط للكستمر الذي أنشأ الحجز
        return $reservation->user_id == $user->id;
    }
    // public function confirm(User $user)
    // {
    //     return $user->hasPermissionTo('confirm reservation');
    // }
    public function confirm(User $user, Reservation $reservation)
    {
        // السماح بتأكيد الحجز فقط لمدير القسم أو الأدمن
        return $user->hasRole(RoleUser::Admin->value) ||
            ($reservation->table && $reservation->table->department->manager_id == $user->id);
    }
    // public function reject(User $user)
    // {
    //     return $user->hasPermissionTo('reject reservation');
    // }
    public function reject(User $user, Reservation $reservation)
    {
        // السماح برفض الحجز فقط لمدير القسم أو الأدمن
        return $user->hasRole(RoleUser::Admin->value) ||
            ($reservation->table && $reservation->table->department->manager_id == $user->id);
    }
    // public function cancel(User $user, Reservation $reservation)
    // {
    //     return $user->hasRole(RoleUser::Admin->value) ||
    //         $reservation->manager_id == $user->id    ||
    //         $reservation->user_id == $user->id;
    // }
    public function cancel(User $user, Reservation $reservation)
    {
        // السماح بالإلغاء فقط للكستمر الذي أنشأ الحجز
        return $reservation->user_id == $user->id;
    }

    public function startService(User $user)
    {
        return $user->hasPermissionTo('start service');
    }

    public function completeService(User $user)
    {
        return $user->hasPermissionTo('complete service');
    }

    public function delete(User $user)
    {
        return $user->hasPermissionTo('hard delete reservation');
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
