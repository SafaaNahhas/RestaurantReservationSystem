<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isUpdated ? 'Event Updated!' : 'New Event Created!' }}</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&family=Montserrat:wght@600&display=swap" rel="stylesheet">

    <style>
        /* Global styles */
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9fafb;
            color: #333;
        }
        .email-container {
            width: 100%;
            max-width: 700px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background-color: #3498db;
            color: white;
            padding: 30px 25px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .header h1 {
            margin: 0;
            font-family: 'Montserrat', sans-serif;
            font-size: 32px;
            font-weight: 600;
        }
        .content {
            padding: 30px 25px;
            color: #555;
            line-height: 1.6;
            font-size: 16px;
        }
        .content h2 {
            font-family: 'Montserrat', sans-serif;
            font-size: 24px;
            color: #333;
            margin-bottom: 15px;
            font-weight: 600;
        }
        .content p {
            font-size: 16px;
            margin-bottom: 20px;
        }
        .event-details {
            background-color: #ecf0f1;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .event-details .event-header {
            background-color: #2980b9;
            color: white;
            font-weight: 500;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 10px;
            text-align: center;
        }
        .content ul {
            list-style-type: none;
            padding-left: 0;
            margin-bottom: 20px;
        }
        .content ul li {
            padding: 10px 0;
            font-size: 16px;
            font-family: 'Roboto', sans-serif;
            border-bottom: 1px solid #ddd;
        }
        .content ul li:last-child {
            border-bottom: none;
        }
        .content ul li strong {
            font-weight: 500;
            color: #333;
        }
        .cta-container {
            text-align: center;
            margin-top: 20px;
        }
        .cta-button {
            display: inline-block;
            background-color: #3498db;
            color: white;
            padding: 12px 20px;
            text-decoration: none;
            font-size: 16px;
            font-weight: 600;
            border-radius: 6px;
            text-transform: uppercase;
            transition: background-color 0.3s ease;
        }
        .cta-button:hover {
            background-color: #2980b9;
        }
        .footer {
            background-color: #f7f9fc;
            text-align: center;
            padding: 20px;
            font-size: 14px;
            color: #777;
            border-top: 1px solid #ddd;
        }
        .footer a {
            color: #3498db;
            text-decoration: none;
        }

        /* Responsive Styles */
        @media only screen and (max-width: 600px) {
            .email-container {
                width: 100% !important;
                margin: 0;
            }
            .header h1 {
                font-size: 26px !important;
            }
            .content h2 {
                font-size: 20px !important;
            }
            .content ul li {
                font-size: 14px !important;
            }
            .cta-button {
                font-size: 14px !important;
                padding: 10px 16px !important;
            }
        }
    </style>
</head>
<body>
<div class="email-container">
    <!-- Header -->
    <div class="header">
        <h1>{{ $isUpdated ? 'Event Updated!' : 'New Event Created!' }}</h1>
    </div>

    <!-- Content -->
    <div class="content">
        <h2>{{ $event->event_name }}</h2>
        <p>{{ $isUpdated ? 'We’ve made updates to an upcoming event. Here are the updated details:' : 'We’re excited to announce a new event! Here are the details:' }}</p>

        <!-- Event Details -->
        <div class="event-details">
            <div class="event-header">Event Information</div>
            <ul>
                <li><strong>Name:</strong> {{ $event->event_name }}</li>
                <li><strong>Details:</strong> {{ $event->details }}</li>
                <li><strong>Start Date:</strong> {{ \Carbon\Carbon::parse($event->start_date)->format('l, F j, Y') }}</li>
                <li><strong>End Date:</strong> {{ \Carbon\Carbon::parse($event->end_date)->format('l, F j, Y') }}</li>
            </ul>
        </div>

        <div class="cta-container">
            <p><a href="https://your-website.com" class="cta-button">Learn More</a></p>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Thank you for staying connected with us!</p>
        <p>&copy; {{ date('Y') }} All Rights Reserved.</p>
        <p>For more information, visit our <a href="https://your-website.com">website</a>.</p>
    </div>
</div>
</body>
</html>
