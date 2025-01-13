<?php

namespace App\Console;

use App\Console\Commands\SendDailyReservationReport;
use App\Models\Reservation;
use App\Jobs\SendRatingRequestJob;
use Illuminate\Support\Facades\Log;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {

        $schedule->command('report:daily-reservations')->daily();

        $schedule->command('report:manager-daily-reservations')->daily();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
