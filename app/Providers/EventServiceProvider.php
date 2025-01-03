<?php

namespace App\Providers;


use App\Models\Reservation;
use Illuminate\Support\Facades\Event;

use App\Events\EmergencyOccurred;
use App\Listeners\SendEmergencyEmails;

use Illuminate\Auth\Events\Registered;
use App\Observers\ReservationLogObserver;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        Reservation::observe(ReservationLogObserver::class);

    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
