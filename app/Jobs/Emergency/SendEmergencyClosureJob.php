<?php

namespace App\Jobs\Emergency;

use App\Models\Restaurant;
use Illuminate\Bus\Queueable;
use App\Services\EmailLogService;
use App\Mail\EmergencyClosureMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class SendEmergencyClosureJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $affectedReservations;
    protected EmailLogService $emailLogService;

    /**
     * Create a new job instance.
     */
    public function __construct($affectedReservations, EmailLogService $emailLogService)
    {
        $this->emailLogService = $emailLogService;
        $this->affectedReservations = $affectedReservations;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        // Get the first restaurant record from the database
        $restaurant = Restaurant::first();

        // Iterate over the collection of affected reservations
        foreach ($this->affectedReservations as $reservation) {
            // Send an email notification to the user associated with the reservation
            try {
                Mail::to($reservation->user->email)
                    ->send(new EmergencyClosureMail($restaurant, $reservation));

                // Log the email success
                $emailLog = $this->emailLogService->createEmailLog(
                    $reservation->user->id,
                    'Emergency Closure Mail',
                    'Reservation in ' . $reservation->start_date
                );
            } catch (\Exception $e) {
                // Log the email failure
                Log::error('Error sending email to user ' . $reservation->user->id . ': ' . $e->getMessage());
                $this->emailLogService->updateEmailLog(
                    $emailLog,
                    'Reservation in ' . $reservation->start_date
                );
            }
        }
    }
}
