<?php

namespace App\Enums;

enum ReservationSendNotificationOptions : string
{
     case Reject = 'reject';
     case Confirm = 'confirm';
     case Cancel  = 'cancel';
}
