<?php

namespace App\Gateway;

enum CheckoutStatus: string
{
    case Pending = 'pending';

    case Charged = 'charged';

    case ToRefund = 'to_refund';
}
