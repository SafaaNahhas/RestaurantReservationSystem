<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Event</title>
</head>
<body>
<h1>New Event: {{ $event->event_name }}</h1>
<p>Details: {{ $event->details }}</p>
<p>Start Date: {{ $event->start_date }}</p>
<p>End Date: {{ $event->end_date }}</p>
<p>Thank you for being a valued customer!</p>

</body>
</html>