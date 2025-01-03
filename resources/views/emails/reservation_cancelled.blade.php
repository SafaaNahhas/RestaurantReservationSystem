<!DOCTYPE html>
<html>
<head>
    <title>Reservation Cancelled</title>
</head>
<body>
    <h1>{{ $isManager ? 'Reservation Cancellation Notification for Manager' : 'Reservation Cancelled' }}</h1>

    @if($isManager)
        <p>Dear Manager,</p>
        <p>The following reservation in your department has been cancelled:</p>
    @else
        <p>Dear {{ $reservation->user->name }},</p>
        <p>We regret to inform you that your reservation has been cancelled. Here are the details:</p>
    @endif

    <ul>
        <li>Table Number: {{ $reservation->table->table_number }}</li>
        <li>Start Date: {{ $reservation->start_date }}</li>
        <li>End Date: {{ $reservation->end_date }}</li>
        <li>Guest Count: {{ $reservation->guest_count }}</li>
        <li>Status: Cancelled</li>
    </ul>

    @if($isManager)
        <p>Please ensure proper coordination with your team regarding this cancellation.</p>
    @else
        <p>If you have any questions, feel free to contact us.</p>
    @endif
</body>
</html>
