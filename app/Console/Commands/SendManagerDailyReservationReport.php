<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EmailLogService;
use App\Jobs\SendManagerDailyReservationReportJob;

class SendManagerDailyReservationReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:manager-daily-reservations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a daily reservation report to each manager for their respective departments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Dispatching daily reservation report jobs for managers...');
        $emailLogService = new EmailLogService();

        SendManagerDailyReservationReportJob::dispatch($emailLogService);

        $this->info('Daily reservation report jobs for managers dispatched successfully!');
    }
}
