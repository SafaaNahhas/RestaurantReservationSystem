<?php

namespace App\Console\Commands;

use App\Jobs\SendDailyReservationReportJob;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendDailyReservationReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:daily-reservations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a daily report of the reservation system to the admin';

    public function __construct()
     {
         parent::__construct();
     }
    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info('Starting scheduled command to dispatch SendDailyReservationReport job.');
        $this->info('Dispatching daily reservations report job...'); 
        SendDailyReservationReportJob::dispatch(); 
        $this->info('Daily reservations report job dispatched successfully!');
        Log::info('SendDailyReservationReport job successfully dispatched.');
    }
}
