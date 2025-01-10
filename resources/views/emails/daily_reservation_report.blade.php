<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Reservations Report</title>
</head>
<body>
    <h1>Daily Reservations Report</h1>
    <p>Date: {{ $date }}</p>

    <h2>Summary</h2>
    <ul>
        <li>Confirmed Reservations: {{ $confirmedCount }}</li>
        <li>Cancelled Reservations: {{ $cancelledCount }}</li>
        <li>Pending Reservations: {{ $pendingCount }}</li>
    </ul>

    <h2>Reservation Details</h2>
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Table ID</th>
                <th>Guest Count</th>
                <th>Status</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Created At</th>
                <th>Manager</th>
                <th>Payment_value</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($reservations as $reservation)
                <tr>
                    <td>{{ $reservation->id }}</td>
                    <td>{{ $reservation->table_id }}</td>
                    <td>{{ $reservation->guest_count }}</td>
                    <td>{{ $reservation->status }}</td>
                    <td>{{ $reservation->start_date }}</td>
                    <td>{{ $reservation->end_date }}</td>
                    <td>{{ $reservation->created_at }}</td>
                    <td>{{ $reservation->manager ? $reservation->manager->name : 'N/A' }}</td>
                    <td>{{ $reservation->payment_value }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>