<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Rate Your Reservation Experience</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f4f4f7;
            font-family: 'Arial', sans-serif;
        }
        a {
            text-decoration: none;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(90deg, #6c63ff, #8a77ff);
            color: #ffffff;
            padding: 40px 20px;
            text-align: center;
        }
        .header h1 {
            font-size: 28px;
            margin: 0;
        }
        .main-content {
            padding: 30px 20px;
            text-align: center;
        }
        .main-content p {
            color: #4a4a4a;
            font-size: 16px;
            line-height: 1.6;
            margin: 15px 0;
        }
        .rating-stars {
            font-size: 40px;
            color: #FFD700;
            margin: 20px 0;
        }
        .btn-primary {
            display: inline-block;
            background: linear-gradient(90deg, #6c63ff, #8a77ff);
            color: #ffffff;
            font-size: 16px;
            font-weight: bold;
            padding: 14px 30px;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(108, 99, 255, 0.4);
            margin-top: 20px;
        }
        .btn-secondary {
            display: inline-block;
            color: #6c63ff;
            font-size: 14px;
            margin-top: 10px;
            text-decoration: underline;
        }
        .footer {
            background: #f4f4f7;
            padding: 20px;
            text-align: center;
            color: #7a7a7a;
            font-size: 12px;
        }
        .footer p {
            margin: 5px 0;
        }
        .footer a {
            color: #6c63ff;
        }
    </style>
</head>
<body>
<table class="email-container" align="center" cellpadding="0" cellspacing="0">
    <!-- Header -->
    <tr>
        <td class="header">
            <h1>Your Opinion Matters!</h1>
        </td>
    </tr>

    <!-- Main Content -->
    <tr>
        <td class="main-content">
            <p>We hope you enjoyed your visit!</p>
            <p>How was your dining experience? Your feedback helps us serve you better.</p>
            <div class="rating-stars">★★★★★</div>
            <a href="{{ $createLink }}" class="btn-primary">Rate Your Experience</a>
        </td>
    </tr>

    <!-- Footer -->
    <tr>
        <td class="footer">
            <p>Thank you for choosing our restaurant!</p>
            <p>&copy; {{ date('Y') }} Restaurant Reservation System. All rights reserved.</p>
            <p>This is an automated message. Please do not reply.</p>
        </td>
    </tr>
</table>
</body>
</html>
