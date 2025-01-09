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
            <table cellpadding="0" cellspacing="0" width="600" style="background-color: #ffffff; border-radius: 8px; overflow: hidden;">
                <!-- Header -->
                <tr>
                    <td bgcolor="#50C878" style="padding: 30px; text-align: center;">
                        <h1 style="color: #ffffff; margin: 0;">Confirm Reservation</h1>
                    </td>
                </tr>

                <!-- Content -->
                <tr>
                    <td style="padding: 30px;">
                        <h2 style="color: #1a202c; margin-bottom: 20px;">Dear {{ $reservation->user->name }},</h2>

                        <p style="color: #4a5568; font-size: 15px; margin-bottom: 20px; font-style: italic;">
                            Thank you for your reservation. Here are the details:
                        </p>

                        <h3 style="color: #50C878; margin-top: 20px; margin-bottom: 15px; border-bottom: 2px solid #50C878; padding-bottom: 10px;">
                            Reservation Details:
                        </h3>

                        <table width="100%" cellpadding="5" cellspacing="0" style="margin-bottom: 10px;">
                            <tr>
                                <td width="150" style="color: #4a5568; font-weight: bold; padding-bottom: 20px;">
                                    Date:
                                </td>
                                <td style="color: #2d3748; padding-bottom: 20px; white-space: nowrap;">
                                    {{ \Carbon\Carbon::parse($reservation->start_date)->format('F j, Y') }}
                                </td>
                            </tr>
                            <tr>
                                <td width="150" style="color: #4a5568; font-weight: bold; padding-bottom: 20px;">
                                    Time:
                                </td>
                                <td style="color: #2d3748; padding-bottom: 20px; white-space: nowrap;">
                                    {{ \Carbon\Carbon::parse($reservation->start_date)->format('g:i A') }} -
                                    {{ \Carbon\Carbon::parse($reservation->end_date)->format('g:i A') }}
                                </td>
                            </tr>
                            <tr>
                                <td width="150" style="color: #4a5568; font-weight: bold; padding-bottom: 20px;">
                                    Number of Guests:
                                </td>
                                <td style="color: #2d3748; padding-bottom: 20px;">
                                    {{ $reservation->guest_count }}
                                </td>
                            </tr>
                            <tr>
                                <td width="150" style="color: #4a5568; font-weight: bold; padding-bottom: 20px;">
                                    Table Number:
                                </td>
                                <td style="color: #2d3748; padding-bottom: 20px;">
                                    {{ $reservation->table->table_number }}
                                </td>
                            </tr>
                            <tr>
                                <td width="150" style="color: #4a5568; font-weight: bold;">
                                    Status:
                                </td>
                                <td>
                                    <span style="background-color: #50C878; color: #ffffff; padding: 5px 10px; border-radius: 4px;">
                                        {{ $reservation->status }}
                                    </span>
                                </td>
                            </tr>
                        </table>

                        <p style="color: #1a202c; font-size: 14px; margin-top: 20px; text-align: left; font-style: italic;">
                            We look forward to serving you!
                        </p>
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="background-color: #f7fafc; padding: 20px; text-align: center; border-top: 1px solid #edf2f7;">
                        <p style="color: #718096; margin: 0; font-size: 14px;">
                            © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
