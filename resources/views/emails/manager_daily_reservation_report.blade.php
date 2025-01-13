<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
<body>
<table cellpadding="0" cellspacing="0" width="100%" style="background-color: #edf2f7; padding: 20px;">
    <tr>
        <td align="center">
            <table cellpadding="0" cellspacing="0" width="800" style="background-color: #ffffff; border-radius: 8px; overflow: hidden;">
                <!-- Header -->
                <tr>
                    <td bgcolor="#50C878" style="padding: 30px; text-align: center;">
                        <h1 style="color: #ffffff; margin: 0;">Daily Reservations Report for Your Department</h1>
                    </td>
                </tr>

                <!-- Content -->
                <tr>
                    <td style="padding: 30px;">
                        <p style="color: #4a5568; font-size: 15px; margin-bottom: 20px;">
                            Date: {{ $date }}
                        </p>

                        <h3 style="color: #50C878; margin-top: 20px; margin-bottom: 15px; border-bottom: 2px solid #50C878; padding-bottom: 10px;">
                            Summary
                        </h3>

                        <table width="100%" cellpadding="5" cellspacing="0" style="margin-bottom: 30px;">
                            <tr>
                                <td width="200" style="color: #4a5568; font-weight: bold; padding-bottom: 10px;">
                                    Pending Reservations:
                                </td>
                                <td style="color: #FFA500; padding-bottom: 10px;">
                                    {{ $pendingCount }}
                                </td>
                            </tr>
                            <tr>
                                <td width="200" style="color: #4a5568; font-weight: bold; padding-bottom: 10px;">
                                    Confirmed Reservations:
                                </td>
                                <td style="color: #2B7A0B; padding-bottom: 10px;">
                                    {{ $confirmedCount }}
                                </td>
                            </tr>
                            <tr>
                                <td width="200" style="color: #4a5568; font-weight: bold; padding-bottom: 10px;">
                                    In Service Reservations:
                                </td>
                                <td style="color: #3B82F6; padding-bottom: 10px;">
                                    {{ $inServiceCount }}
                                </td>
                            </tr>
                            <tr>
                                <td width="200" style="color: #4a5568; font-weight: bold; padding-bottom: 10px;">
                                    Completed Reservations:
                                </td>
                                <td style="color: #059669; padding-bottom: 10px;">
                                    {{ $completedCount }}
                                </td>
                            </tr>
                            <tr>
                                <td width="200" style="color: #4a5568; font-weight: bold; padding-bottom: 10px;">
                                    Cancelled Reservations:
                                </td>
                                <td style="color: #B31312; padding-bottom: 10px;">
                                    {{ $cancelledCount }}
                                </td>
                            </tr>
                            <tr>
                                <td width="200" style="color: #4a5568; font-weight: bold; padding-bottom: 10px;">
                                    Rejected Reservations:
                                </td>
                                <td style="color: #7A1212; padding-bottom: 10px;">
                                    {{ $rejectedCount }}
                                </td>
                            </tr>
                        </table>

                        <h3 style="color: #50C878; margin-top: 30px; margin-bottom: 15px; border-bottom: 2px solid #50C878; padding-bottom: 10px;">
                            Reservation Details
                        </h3>

                        <table width="100%" cellpadding="10" cellspacing="0" style="border-collapse: collapse; margin-top: 10px;">
                            <thead>
                            <tr>
                                <th style="background-color: #f8f9fa; border: 1px solid #e2e8f0; color: #4a5568; text-align: left;">ID</th>
                                <th style="background-color: #f8f9fa; border: 1px solid #e2e8f0; color: #4a5568; text-align: left;">Table ID</th>
                                <th style="background-color: #f8f9fa; border: 1px solid #e2e8f0; color: #4a5568; text-align: left;">Guest Count</th>
                                <th style="background-color: #f8f9fa; border: 1px solid #e2e8f0; color: #4a5568; text-align: left;">Status</th>
                                <th style="background-color: #f8f9fa; border: 1px solid #e2e8f0; color: #4a5568; text-align: left;">Start Date</th>
                                <th style="background-color: #f8f9fa; border: 1px solid #e2e8f0; color: #4a5568; text-align: left;">End Date</th>
                                <th style="background-color: #f8f9fa; border: 1px solid #e2e8f0; color: #4a5568; text-align: left;">Created At</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($reservations as $reservation)
                                <tr>
                                    <td style="border: 1px solid #e2e8f0; color: #2d3748;">{{ $reservation->id }}</td>
                                    <td style="border: 1px solid #e2e8f0; color: #2d3748;">{{ $reservation->table_id }}</td>
                                    <td style="border: 1px solid #e2e8f0; color: #2d3748;">{{ $reservation->guest_count }}</td>
                                    <td style="border: 1px solid #e2e8f0;">
                                            <span :style="{ color: '{{ $statusColors[$reservation->status] }}'}">
                                                {{ $reservation->status }}
                                            </span>
                                    </td>
                                    <td style="border: 1px solid #e2e8f0; color: #2d3748;">{{ $reservation->start_date }}</td>
                                    <td style="border: 1px solid #e2e8f0; color: #2d3748;">{{ $reservation->end_date }}</td>
                                    <td style="border: 1px solid #e2e8f0; color: #2d3748;">{{ $reservation->created_at }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
