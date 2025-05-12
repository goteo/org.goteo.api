<?php

namespace App\Gateway;

enum ChargeStatus: string
{
    case InPending = 'in_pending';

    case Charged = 'charged';

    case ToRefund = 'to_refund';

    case Refunded = 'refunded';
}
