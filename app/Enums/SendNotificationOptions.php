<?php

namespace App\Enums;

enum  SendNotificationOptions: string
{
    case Events = 'events';
    case Rating = 'rating';
}
