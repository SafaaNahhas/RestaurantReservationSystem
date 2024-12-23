<!DOCTYPE html>
<html>
<head>
    <title>Reservation Details</title>
</head>
<body>
    <h1>Reservation Details</h1>
    <p>Dear {{ $reservation->user->name }},</p>
    <p>Thank you for your reservation. Here are the details:</p>
    <ul>
        <li>Table Number: {{ $reservation->table->table_number }}</li>
        <li>Start Date: {{ $reservation->start_date }}</li>
        <li>End Date: {{ $reservation->end_date }}</li>
        <li>Guest Count: {{ $reservation->guest_count }}</li>
        <li>Status: {{ $reservation->status }}</li>
    </ul>
    <p>We look forward to serving you!</p>
</body>
</html>
