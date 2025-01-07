<?php

namespace App\Policies;

use App\Models\User;
use App\Enums\RoleUser;
use App\Models\Favorite;
use Illuminate\Auth\Access\Response;

class FavoritePolicy
{


    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user): bool
    {
        return $user->hasRole(RoleUser::Admin->value);
    }
    /**
     * Determine whether the user can show all favorite.
     */
    public function showAllFavorite(User $user): bool
    {
        return $user->hasRole(RoleUser::Admin->value);
    }
    /**
     * Determine whether the user can show the deleting favorite 
     */
    public function getDeleting(User $user): bool
    {
        return $user->hasRole(RoleUser::Admin->value);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user): bool
    {
        return $user->hasRole(RoleUser::Admin->value);
    }
}
