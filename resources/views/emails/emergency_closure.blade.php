<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Important: Reservation Cancellation</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f3f4f6;
            font-family: 'Arial', sans-serif;
            color: #333333;
            line-height: 1.6;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(90deg, #ff6b6b, #fa5252);
            color: #ffffff;
            text-align: center;
            padding: 20px 30px;
        }
        .header img {
            max-width: 50px;
            margin-bottom: 10px;
        }
        .header h1 {
            font-size: 24px;
            margin: 0;
        }
        .content {
            padding: 20px 30px;
            text-align: center;
        }
        .content p {
            font-size: 16px;
            margin: 15px 0;
        }
        .icon {
            font-size: 48px;
            color: #fa5252;
            margin-bottom: 20px;
        }
        .action-btn {
            display: inline-block;
            background: linear-gradient(90deg, #ff6b6b, #fa5252);
            color: #ffffff;
            padding: 12px 25px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 5px;
            text-decoration: none;
            box-shadow: 0 5px 10px rgba(250, 82, 82, 0.3);
            margin-top: 20px;
        }
        .action-btn:hover {
            background: #ff4c4c;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 15px;
            text-align: center;
            font-size: 12px;
            color: #7a7a7a;
        }
    </style>
</head>
<body>
<div class="email-container">
    <!-- Header -->
    <div class="header">
        <h1>Important Notice</h1>
    </div>

    <!-- Content -->
    <div class="content">
        <div class="icon">📩</div>
        <p>Dear {{ $customer_name }},</p>
        <p>
            We regret to inform you that due to unforeseen circumstances,
            the restaurant {{ $restaurantName }} will be closed on {{ $reservationDate }}.
        </p>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Thank you for your patience. We look forward to serving you soon!</p>
        <p>&copy; {{ date('Y') }} {{ $restaurantName }}. All rights reserved.</p>
    </div>
</div>
</body>
</html>
