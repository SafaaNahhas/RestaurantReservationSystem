<?php

namespace App\Console\Commands;

 use App\Services\NotificationLogService;
 use Illuminate\Console\Command;
 use Illuminate\Support\Facades\Log;
 use App\Jobs\SendDailyReservationReportJob;

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
        $notificationLogService = new NotificationLogService();

        SendDailyReservationReportJob::dispatch($notificationLogService);
        $this->info('Daily reservations report job dispatched successfully!');
        Log::info('SendDailyReservationReport job successfully dispatched.');
    }
}
