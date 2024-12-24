<?php

namespace App\Enums;

enum RoleUser: string
{
    case Customer           = 'Customer';
    case Admin              = 'Admin';
    case ReservationManager = 'Reservation Manager';
    case Captin             = 'Captin';
}
