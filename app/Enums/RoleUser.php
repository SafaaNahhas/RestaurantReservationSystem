<?php

namespace App\Enums;

enum RoleUser: string
{
    case Customer           = 'Customer';
    case Admin              = 'Admin';

    case Manager            = 'Manager';
    case Waiter             = 'Waiter';

}
