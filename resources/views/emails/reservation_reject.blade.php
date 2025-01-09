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
                    <td bgcolor="#DC2626" style="padding: 30px; text-align: center;">
                        <h1 style="color: #ffffff; margin: 0;">Reservation Rejected</h1>
                    </td>
                </tr>

                <!-- Content -->
                <tr>
                    <td style="padding: 30px;">
                        <h2 style="color: #1a202c; margin-bottom: 20px;">Dear {{ $reservation->user->name }},</h2>

                        <p style="color: #4a5568; font-size: 15px; margin-bottom: 20px; font-style: italic;">
                            We regret to inform you that your reservation request could not be confirmed at this time.
                        </p>

                        <div style="background-color: #FEE2E2; border-left: 4px solid #4a5568; padding: 20px; margin-bottom: 20px; border-radius: 4px;">
                            <p style="color: #4a5568; margin: 0; font-size: 15px;">
                                <strong>Reason for Rejection:</strong><br>
                                {{ $rejectionReason }}
                            </p>
                        </div>

                        <h3 style="color: #DC2626; margin-top: 20px; margin-bottom: 15px; border-bottom: 2px solid #DC2626; padding-bottom: 10px;">
                            Reservation Details:
                        </h3>

                        <table width="100%" cellpadding="5" cellspacing="0" style="margin-bottom: 30px;">
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
                        </table>

                        <!-- Alternative Options -->
                        <table width="100%" cellpadding="0" cellspacing="0" style="margin: 20px 0;">
                            <tr>
                                <td style="padding: 15px; border: 1px solid #e5e7eb; border-radius: 4px;">
                                    <p style="color: #2d3748; font-size: 15px; margin: 0 0 10px 0; font-weight: bold;">
                                        Available Options:
                                    </p>
                                    <p style="color: #4a5568; margin: 0; line-height: 1.6;">
                                        • Try booking for a different date or time<br>
                                        • Contact us directly for assistance
                                    </p>
                                </td>
                            </tr>
                        </table>

                        <!-- Contact Information -->
                        <table width="100%" cellpadding="0" cellspacing="0" style="margin-top: 30px; border-top: 1px solid #e5e7eb; padding-top: 20px;">
                            <tr>
                                <td style="color: #4a5568; font-size: 14px; line-height: 24px;">
                                    <p style="margin: 0 0 10px 0;">
                                        If you have any questions or need assistance, please don't hesitate to contact us:
                                    </p>
                                    <p style="margin: 0;">
                                        Phone: (555) 123-4567<br>
                                        Email: reservations@restaurant.com
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td bgcolor="#1a202c" style="padding: 30px; text-align: center;">
                        <p style="color: #ffffff; font-size: 14px; margin: 0;">
                            Thank you for your understanding.<br>
                            We hope to serve you in the future.
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
