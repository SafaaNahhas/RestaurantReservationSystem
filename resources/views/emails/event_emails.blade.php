<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Notification</title>
</head>
<body>
<h1>{{ $isUpdated ? 'Event Updated!' : 'New Event Created!' }}</h1>
<p>Hello,</p>
    <p>We wanted to let you know about an event:</p>
    <ul>
        <li><strong>Name:</strong> {{ $event->event_name }}</li>
        <p>Details: {{ $event->details }}</p>
<p>Start Date: {{ $event->start_date }}</p>
<p>End Date: {{ $event->end_date }}</p>
    </ul>
    <p>Thank you for staying connected with us!</p>
</body>
</html>
