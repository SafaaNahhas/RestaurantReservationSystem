<!DOCTYPE html>
<html>
<head>
    <title>Reservation Cancelled</title>
</head>
<body>
    <h1>Reservation Cancelled</h1>
    <p>Dear {{ $reservation->user->name }},</p>
    <p>We regret to inform you that your reservation has been cancelled. Here are the details:</p>
    <ul>
        <li>Table Number: {{ $reservation->table->table_number }}</li>
        <li>Start Date: {{ $reservation->start_date }}</li>
        <li>End Date: {{ $reservation->end_date }}</li>
        <li>Guest Count: {{ $reservation->guest_count }}</li>
        <li>Status: Cancelled</li>
    </ul>
    <p>If you have any questions, feel free to contact us.</p>
</body>
</html>
