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
                    <td bgcolor="#FF6B6B" style="padding: 30px; text-align: center;">
                        <h1 style="color: #ffffff; margin: 0;">
                            {{ $isManager ? 'Reservation Cancellation Notice' : 'Reservation Cancelled' }}
                        </h1>
                    </td>
                </tr>

                <!-- Content -->
                <tr>
                    <td style="padding: 30px;">
                        <h2 style="color: #1a202c; margin-bottom: 20px;">
                            @if($isManager)
                                Dear Manager {{ $reservation->manager->name }},
                            @else
                                Dear {{ $reservation->user->name }},
                            @endif
                        </h2>

                        <p style="color: #4a5568; font-size: 15px; margin-bottom: 20px; font-style: italic;">
                            @if($isManager)
                                The following reservation has been cancelled:
                            @else
                                We regret to inform you that your reservation has been cancelled. Here are the details:
                            @endif
                        </p>

                        <h3 style="color: #FF6B6B; margin-top: 20px; margin-bottom: 15px; border-bottom: 2px solid #FF6B6B; padding-bottom: 10px;">
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
                                    Table Number:
                                </td>
                                <td style="color: #2d3748; padding-bottom: 20px;">
                                    {{ $reservation->table->table_number }}
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
                                <td width="150" style="color: #4a5568; font-weight: bold;">
                                    Status:
                                </td>
                                <td>
                                    <span style="background-color: #FF6B6B; color: #ffffff; padding: 5px 10px; border-radius: 4px; font-size: 14px;">
                                        Cancelled
                                    </span>
                                </td>
                            </tr>
                        </table>

                        <!-- Additional Information -->
                        <table width="100%" cellpadding="0" cellspacing="0" style="margin-top: 30px; border-top: 1px solid #e5e7eb; padding-top: 20px;">
                            <tr>
                                <td style="color: #4a5568; font-size: 14px; line-height: 24px;">
                                    @if($isManager)
                                        <p style="margin: 0;">
                                            Please ensure proper coordination with your team regarding this cancellation.
                                        </p>
                                    @else
                                        <p style="margin: 0;">
                                            If you have any questions or need assistance, please don't hesitate to contact us.
                                        </p>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td bgcolor="#1a202c" style="padding: 30px; text-align: center;">
                        <p style="color: #ffffff; font-size: 14px; margin: 0;">
                            Thank you for your understanding.
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
