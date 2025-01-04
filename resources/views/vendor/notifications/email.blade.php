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
                    <td bgcolor="#B31312" style="padding: 30px; text-align: center;">
                        <h1 style="color: #ffffff; margin: 0;">{{ config('app.name') }}</h1>
                    </td>
                </tr>

                <!-- Content -->
                <tr>
                    <td style="padding: 30px;">
                        @if (! empty($greeting))
                            <h2 style="color: #1a202c; margin-bottom: 20px;">{{ $greeting }}</h2>
                        @endif

                        @foreach ($introLines as $line)
                            @if ($line === 'A new reservation requires your review:')
                                <p style="color: #4a5568; font-size: 15px; margin-bottom: 20px; font-style: italic;">
                                    {{ $line }}
                                </p>
                            @elseif ($line === 'Reservation Details:')
                                <h3 style="color: #B31312; margin-top: 20px; margin-bottom: 15px; border-bottom: 2px solid #B31312; padding-bottom: 10px;">
                                    {{ $line }}
                                </h3>
                            @elseif (str_starts_with($line, 'Date:') || str_starts_with($line, 'Time:') || str_starts_with($line, 'Number of Guests:') || str_starts_with($line, 'Table Number:') || str_starts_with($line, 'Additional Services:'))
                                <table width="100%" cellpadding="5" cellspacing="0" style="margin-bottom: 10px;">
                                    <tr>
                                        <td width="150" style="color: #4a5568; font-weight: bold;">
                                            {{ explode(':', $line)[0] }}:
                                        </td>
                                        <td style="color: #2d3748;">
                                            {{ trim(explode(':', $line, 2)[1]) }}
                                        </td>
                                    </tr>
                                </table>
                            @elseif (str_starts_with($line, 'Status:'))
                                <table width="100%" cellpadding="5" cellspacing="0" style="margin-bottom: 10px;">
                                    <tr>
                                        <td width="150" style="color: #4a5568; font-weight: bold;">
                                            Status:
                                        </td>
                                        <td>
                            <span style="background-color: #B31312; color: #ffffff; padding: 5px 10px; border-radius: 4px;">
                                Pending
                            </span>
                                        </td>
                                    </tr>
                                </table>
                            @elseif ($line === 'Please review this reservation request.')
                                <p style="color: #B31312; font-size: 15px; margin-top: 20px; margin-bottom: 15px; font-weight: 500; text-align: center;">
                                    {{ $line }}
                                </p>
                            @elseif ($line === 'Thank you.')
                                <p style="color: #1a202c; font-size: 14px; margin-top: 20px; text-align: left; font-style: italic;">
                                    {{ $line }}
                                </p>
                            @else
                                <p style="color: #4a5568; line-height: 1.6; margin-bottom: 15px;">
                                    {{ $line }}
                                </p>
                            @endif
                        @endforeach

                        @isset($actionText)
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $actionUrl }}"
                                           style="background-color: #B31312;
                                  color: #ffffff;
                                  display: inline-block;
                                  padding: 12px 24px;
                                  text-decoration: none;
                                  border-radius: 4px;
                                  font-weight: bold;">
                                            {{ $actionText }}
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        @endisset
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

            @isset($actionText)
                <table width="600" cellpadding="0" cellspacing="0" style="margin-top: 20px;">
                    <tr>
                        <td style="text-align: center;">
                            <p style="color: #718096; font-size: 13px; margin: 0;">
                                @lang("If you're having trouble clicking the \":actionText\" button, copy and paste the URL below into your web browser:", ['actionText' => $actionText])
                                <br>
                                <a href="{{ $actionUrl }}" style="color: #B31312; text-decoration: underline;">
                                    {{ $actionUrl }}
                                </a>
                            </p>
                        </td>
                    </tr>
                </table>
            @endisset
        </td>
    </tr>
</table>
</body>
</html>
