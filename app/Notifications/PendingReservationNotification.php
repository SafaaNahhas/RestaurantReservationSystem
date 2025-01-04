<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class PendingReservationNotification extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected Reservation $reservation;

    /**
     * Create a new notification instance.
     *
     * @param Reservation $reservation The reservation that needs review
     */
    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation;
    }

    /**
     * Get the notification's delivery channels.
     *
     * Currently only using email channel, but can be extended to include
     * other channels like SMS, Slack, etc.
     *
     * @param mixed $notifiable The entity receiving the notification
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Build the mail representation of the notification.
     *
     * Constructs an email with all relevant reservation details including:
     * - Reservation ID
     * - Date and time
     * - Guest count
     * - Table information
     * - Additional services (if any)
     *
     * @param mixed $notifiable The entity receiving the notification
     * @return MailMessage
     */
    public function toMail(object $notifiable): MailMessage
    {
        // Split the datetime strings
        $startParts = explode(' ', $this->reservation->start_date);
        $endParts = explode(' ', $this->reservation->end_date);

        $date = $startParts[0];  // Gets YYYY-MM-DD
        $startTime = $startParts[1];  // Gets HH:mm
        $endTime = $endParts[1];  // Gets HH:mm

        return (new MailMessage)
            ->subject('New Reservation Request #' . $this->reservation->id)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('A new reservation requires your review:')
            ->line('Reservation Details:')
            ->line('Date: ' . $date)
            ->line('Time: ' . $startTime . ' - ' . $endTime)
            ->line('Number of Guests: ' . $this->reservation->guest_count)
            ->line('Table Number: ' . ($this->reservation->table ? $this->reservation->table->table_number : 'Not assigned'))
            ->when($this->reservation->services, function ($message) {
                return $message->line('Additional Services: ' . $this->reservation->services);
            })
            ->line('Status: Pending approval')
            ->line('Please review this reservation request.')
            ->line('Thank you.');
    }

    /**
     * Get the array representation of the notification.
     *
     * Note: This method is currently not in use as we're only using mail notifications.
     * Keep this method if you plan to:
     * - Store notifications in the database (add 'database' to via() method)
     * - Use broadcast channels
     * - Implement custom notification channels
     *
     * To enable database notifications:
     * 1. Run: php artisan notifications:table
     * 2. Run: php artisan migrate
     * 3. Add 'database' to via() method
     *
     * @param mixed $notifiable The entity receiving the notification
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'reservation_id' => $this->reservation->id,
            'start_date' => $this->reservation->start_date,
            'end_date' => $this->reservation->end_date,
            'guest_count' => $this->reservation->guest_count,
            'status' => $this->reservation->status,
            'table_number' => $this->reservation->table?->table_number,
            'services' => $this->reservation->services,
            'notification_type' => 'pending_reservation'
        ];
    }

    /**
     * Handle notification failure.
     *
     * @param Throwable $exception
     * @return void
     */
    public function failed(Throwable $exception): void
    {
        Log::error('Failed to send pending reservation notification', [
            'reservation_id' => $this->reservation->id,
            'error' => $exception->getMessage()
        ]);
    }
}
