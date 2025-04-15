<?php

namespace App\Gateway;

enum ChargeStatus: string
{
    case Pending = 'pending';

    case Charged = 'charged';

    case ToRefund = 'to_refund';

    case Refunded = 'refunded';
}
