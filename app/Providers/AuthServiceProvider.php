<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use App\Models\Rating;
use App\Models\Reservation;
use App\Policies\RatingPolicy;
use App\Policies\ReservationPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
        Reservation::class => ReservationPolicy::class,
        Rating::class => RatingPolicy::class


    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        //
    }
}
