<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Rejected</title>
</head>
<body>
    <h1>Your Reservation Has Been Rejected</h1>

    <p>Dear {{ $reservation->user->name }},</p>

    <p>We regret to inform you that your reservation for table number {{ $reservation->table->table_number }} on {{ $reservation->start_date }} has been rejected.</p>

    <p><strong>Rejection Reason:</strong> {{ $rejectionReason }}</p>

    <p>If you have any questions or need further assistance, please contact us.</p>

    <p>Best regards,<br>Your Restaurant Team</p>
</body>
</html>
