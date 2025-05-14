<?php

namespace App\Gateway;

enum CheckoutStatus: string
{
    case InPending = 'in_pending';

    case Charged = 'charged';
}
