<?php

namespace App\Enums;

enum RoleUser: string
{
    case Customer           = 'customer';
    case Admin              = 'admin';
    case ReservationManager = 'reservation manager';
    case Waiter             = 'waiter';
}
